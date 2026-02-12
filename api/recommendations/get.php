<?php
// Start output buffering and suppress error display
ob_start();
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Register shutdown function to catch fatal errors
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== NULL && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        ob_clean();
        if (!headers_sent()) {
            header('Content-Type: application/json');
        }
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'PHP Fatal Error: ' . $error['message'] . ' in ' . basename($error['file']) . ' on line ' . $error['line']
        ]);
        exit;
    }
});

try {
    require_once __DIR__ . '/../../DB.php';
    require_once __DIR__ . '/../helpers/auth.php';
} catch (Exception $e) {
    ob_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to load required files: ' . $e->getMessage()
    ]);
    exit;
} catch (Error $e) {
    ob_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Fatal error loading files: ' . $e->getMessage()
    ]);
    exit;
}

header('Content-Type: application/json');

// Check database connection
if (!isset($conn) || !$conn) {
    ob_clean();
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit;
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$user_id = $_SESSION['user_id'] ?? null;
if(!$user_id) {
    ob_clean();
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'User not logged in']);
    exit;
}
function loadEnv($path = '.env') {
    if (!file_exists($path)) return false;
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') === false) continue;
        list($name, $value) = explode('=', $line, 2);
        putenv(trim($name) . "=" . trim($value));
        $_ENV[trim($name)] = trim($value);
    }
    return true;
}

try {
    loadEnv(__DIR__ . '/../../.env');
    loadEnv(__DIR__ . '/../../Admin/.env');
} catch (Exception $e) {
    ob_clean();
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Error loading .env files: ' . $e->getMessage()]);
    exit;
}

try {
$medicalSql = "SELECT * From medicalrecord Where Member_Id = ?";
$stmt = $conn->prepare($medicalSql);
if (!$stmt) {
    ob_clean();
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $conn->error]);
    exit;
}
$stmt->bind_param('i', $user_id);
if (!$stmt->execute()) {
    ob_clean();
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database execute error: ' . $stmt->error]);
    $stmt->close();
    exit;
}
$medicalRecord = $stmt->get_result()->fetch_assoc();
$stmt->close();


$medicalConditions = [];
if ($medicalRecord) {
    $conditionMap = [
        'Has_Diabetes' => 'Diabetes',
        'Has_Heart_Condition' => 'Heart Condition',
        'Has_Asthma' => 'Asthma',
        'Has_Thyroid_Disorder' => 'Thyroid Disorder',
        'Has_High_Cholesterol' => 'High Cholesterol',
        'Has_Back_Injury' => 'Back Injury',
        'Has_Neck_Injury' => 'Neck Injury',
        'Has_lactose_intolerance' => 'Lactose Intolerance',
        'Has_gluten_intolerance' => 'Gluten Intolerance',
        'Has_nut_Allergy' => 'Nut Allergy',
        'Has_egg_allergy' => 'Egg Allergy',
        'has_recent_surgery' => 'Recent Surgery'
    ];
    
    foreach ($conditionMap as $field => $label) {
        if (!empty($medicalRecord[$field]) && $medicalRecord[$field] == 1) {
            $medicalConditions[] = $label;
        }
    }
}

$memberSql = "SELECT Training_Goals, Medical_Condition, Injuries, Experience_Level
              From memberprofile Where Member_Id = ?";
$stmt = $conn->prepare($memberSql);
if (!$stmt) {
    ob_clean();
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $conn->error]);
    exit;
}
$stmt->bind_param('i', $user_id);
if (!$stmt->execute()) {
    ob_clean();
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database execute error: ' . $stmt->error]);
    $stmt->close();
    exit;
}
$memberProfile = $stmt->get_result()->fetch_assoc();
$stmt->close();

$coachesSql = "SELECT cp.Coach_ID, cp.Specialization_Main, cp.Specialization_Other, 
               cp.Bio AS bio, cp.rating_count, cp.Avg_rating, cp.Is_Accepting_new, 
               u.First_Name, u.Last_Name, u.Profile_pic_url
               FROM coachprofile cp
               JOIN userprofile u ON cp.Coach_ID = u.User_ID
               WHERE (cp.is_deleted = 0 OR cp.is_deleted IS NULL) 
               AND (u.is_deleted = 0 OR u.is_deleted IS NULL) 
               AND (cp.Is_Accepting_new = 1 OR cp.Is_Accepting_new IS NULL)";
$coachesResult = $conn->query($coachesSql);
if (!$coachesResult) {
    ob_clean();
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $conn->error]);
    exit;
}
$coaches = [];
while ($row = $coachesResult->fetch_assoc()) {
    $coaches[] = [
        'id' => $row['Coach_ID'],
        'name' => trim($row['First_Name'] . ' ' . $row['Last_Name']),
        'specialization_main' => $row['Specialization_Main'] ?? '',
        'specialization_other' => $row['Specialization_Other'] ?? '',
        'bio' => $row['bio'] ?? '',
        'rating' => $row['Avg_rating'] ?? 0,
        'rating_count' => $row['rating_count'] ?? 0
    ];
}
$nutritionistsSql = "SELECT np.Nutritionist_ID, np.Specialization_Main, 
                            np.Bio AS bio, np.Certifications, np.Years_Experience,
                            np.Avg_rating, np.rating_count, np.Is_Accepting_new,
                            u.First_Name, u.Last_Name, u.Profile_pic_url
                     FROM nutritionistprofile np
                     JOIN userprofile u ON np.Nutritionist_ID = u.User_ID
                     WHERE (np.is_deleted = 0 OR np.is_deleted IS NULL) 
                     AND (u.is_deleted = 0 OR u.is_deleted IS NULL) 
                     AND (np.Is_Accepting_new = 1 OR np.Is_Accepting_new IS NULL)";
$nutritionistsResult = $conn->query($nutritionistsSql);
if (!$nutritionistsResult) {
    ob_clean();
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $conn->error]);
    exit;
}
$nutritionists = [];
while ($row = $nutritionistsResult->fetch_assoc()) {
    $nutritionists[] = [
        'id' => $row['Nutritionist_ID'],
        'name' => trim($row['First_Name'] . ' ' . $row['Last_Name']),
        'specialization_main' => $row['Specialization_Main'] ?? '',
        'bio' => $row['bio'] ?? '',
        'certifications' => $row['Certifications'] ?? '',
        'years_experience' => $row['Years_Experience'] ?? 0,
        'rating' => $row['Avg_rating'] ?? 0,
        'rating_count' => $row['rating_count'] ?? 0
    ];
}


// Build the prompt for Gemini
$prompt = "You are a medical and fitness recommendation system. Analyze the following member profile and recommend the most suitable coaches and nutritionists.

MEMBER PROFILE:
- Training Goals: " . ($memberProfile['Training_Goals'] ?? 'Not specified') . "
- Experience Level: " . ($memberProfile['Experience_Level'] ?? 'Not specified') . "
- Injuries: " . ($memberProfile['Injuries'] ?? 'None') . "
- Medical Conditions: " . (empty($medicalConditions) ? 'None' : implode(', ', $medicalConditions)) . "
- Additional Medical Notes: " . ($memberProfile['Medical_Condition'] ?? 'None') . "

AVAILABLE COACHES:
" . json_encode($coaches, JSON_PRETTY_PRINT) . "

AVAILABLE NUTRITIONISTS:
" . json_encode($nutritionists, JSON_PRETTY_PRINT) . "

Based on the member's medical conditions, injuries, training goals, and experience level, recommend which coaches and nutritionists are best suited.

Respond ONLY with a valid JSON object in this exact format:
{
  \"recommended_coaches\": [1, 5, 12],
  \"recommended_nutritionists\": [3, 7],
  \"reasoning\": {
    \"coaches\": {
      \"1\": \"Brief explanation why this coach is recommended\",
      \"5\": \"Brief explanation why this coach is recommended\"
    },
    \"nutritionists\": {
      \"3\": \"Brief explanation why this nutritionist is recommended\",
      \"7\": \"Brief explanation why this nutritionist is recommended\"
    }
  }
}

Important:
- Only recommend coaches/nutritionists whose specializations align with the member's needs
- Prioritize those who can safely work with the member's medical conditions and injuries
- Consider experience level and training goals
- Return empty arrays [] if no good matches are found
- Only include IDs that exist in the provided lists";


function callGemini($prompt, $model = 'gemini-2.5-flash', $jsonResponse = false) {
    $apiKey = getenv('GEMINI_API_KEY');
    if (!$apiKey) {
        return ['error' => 'GEMINI_API_KEY not set in .env file'];
    }

    $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

    $requestData = [
        'contents' => [
            [
                'parts' => [
                    ['text' => $prompt]
                ]
            ]
        ]
    ];

    if ($jsonResponse) {
        $requestData['generationConfig'] = [
            'responseMimeType' => 'application/json'
        ];
    }

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json'
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        return ['error' => "API request failed with status {$httpCode}"];
    }

    $result = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        return ['error' => 'Invalid JSON response from API'];
    }

    $text = $result['candidates'][0]['content']['parts'][0]['text'] ?? null;
    if (!$text) {
        return ['error' => 'No response text found'];
    }

    if ($jsonResponse) {
        $parsed = json_decode($text, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return ['error' => 'Invalid JSON from LLM'];
        }
        return $parsed;
    }

    return $text;
}

$recommendations = callGemini($prompt, 'gemini-2.5-flash', true);

if (isset($recommendations['error'])) {
    ob_clean();
    http_response_code(500);
    echo json_encode($recommendations);
    exit;
}

// Clear any buffered output before sending JSON
ob_clean();
// Return the recommendations
echo json_encode([
    'success' => true,
    'recommended_coaches' => $recommendations['recommended_coaches'] ?? [],
    'recommended_nutritionists' => $recommendations['recommended_nutritionists'] ?? [],
    'reasoning' => $recommendations['reasoning'] ?? []
]);
ob_end_flush();

} catch (Exception $e) {
    ob_clean();
    error_log("Recommendations API error: " . $e->getMessage() . "\n" . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Server error: ' . $e->getMessage(),
        'file' => basename($e->getFile()),
        'line' => $e->getLine()
    ]);
    exit;
} catch (Error $e) {
    ob_clean();
    error_log("Recommendations API fatal error: " . $e->getMessage() . "\n" . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Fatal error: ' . $e->getMessage(),
        'file' => basename($e->getFile()),
        'line' => $e->getLine()
    ]);
    exit;
}
?>
