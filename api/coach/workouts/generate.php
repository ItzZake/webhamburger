<?php
// Increase execution time for longer workout plans (4+ weeks)
set_time_limit(180); // 3 minutes
ini_set('max_execution_time', 180);

require_once __DIR__ . '/../../../DB.php';
require_once __DIR__ . '/../../helpers/auth.php';
header('Content-Type: application/json');

require_role(['coach','admin']);

$raw = file_get_contents('php://input');
$data = json_decode($raw, true) ?: $_POST;

if (empty($data['member_id'])) {
    echo json_encode(['error' => 'member_id required']);
    exit;
}

$memberId = (int)$data['member_id'];

// Get member details
$memberSql = "SELECT m.Member_Id, u.First_Name, u.Last_Name, m.Experience_Level, m.Training_Goals, m.Injuries, m.Medical_Condition, m.Height, m.Weight, m.BMI
              FROM memberprofile m
              JOIN userprofile u ON m.Member_Id = u.User_ID
              WHERE m.Member_Id = ? AND m.is_deleted = 0 AND u.is_deleted = 0";
$stmt = $conn->prepare($memberSql);
$stmt->bind_param('i', $memberId);
$stmt->execute();
$member = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$member) {
    echo json_encode(['error' => 'Member not found']);
    exit;
}

// Get detailed medical history from MedicalRecord table
$medicalSql = "SELECT Has_Diabetes, Has_Heart_Condition, Has_Asthma, Has_Thyroid_Disorder, 
                      Has_High_Cholesterol, Has_Back_Injury, Has_Neck_Injury, 
                      Has_lactose_intolerance, Has_gluten_intolerance, Has_nut_Allergy, 
                      Has_egg_allergy, has_recent_surgery
               FROM medicalrecord 
               WHERE Member_Id = ?";
$medicalStmt = $conn->prepare($medicalSql);
$medicalConditions = [];
if ($medicalStmt) {
    $medicalStmt->bind_param('i', $memberId);
    $medicalStmt->execute();
    $medicalResult = $medicalStmt->get_result();
    if ($medicalRow = $medicalResult->fetch_assoc()) {
        // Build list of active medical conditions
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
            if (!empty($medicalRow[$field]) && $medicalRow[$field] == 1) {
                $medicalConditions[] = $label;
            }
        }
    }
    $medicalStmt->close();
}

// Load environment variables
function loadEnv($path = '.env') {
    if (!file_exists($path)) {
        return false;
    }
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') === false) continue;
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        putenv("$name=$value");
        $_ENV[$name] = $value;
    }
    return true;
}

loadEnv(__DIR__ . '/../../../.env');
loadEnv(__DIR__ . '/../../../Admin/.env');

// Function to call Gemini API
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
    // Increase timeout for longer workout plans (4+ weeks)
    curl_setopt($ch, CURLOPT_TIMEOUT, 180); // 3 minutes
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);

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

// Build prompt with member information
$weeks = isset($data['weeks']) ? (int)$data['weeks'] : 4;
$goal = isset($data['goal']) ? $data['goal'] : ($member['Training_Goals'] ?: 'General fitness');
$experience = $member['Experience_Level'] ?: 'Beginner';
$injuries = $member['Injuries'] ?: 'None';
$medical = $member['Medical_Condition'] ?: 'None';

// Combine medical conditions from both sources
$detailedMedical = '';
if (!empty($medicalConditions)) {
    $detailedMedical = 'Detailed Medical Conditions: ' . implode(', ', $medicalConditions);
}
if ($medical && $medical !== 'None') {
    $detailedMedical .= ($detailedMedical ? '. ' : '') . 'Additional Notes: ' . $medical;
}
if (empty($detailedMedical)) {
    $detailedMedical = 'None';
}

$heightWeight = "";
if ($member['Height'] && $member['Weight']) {
    $heightWeight = "H:{$member['Height']}cm W:{$member['Weight']}kg. ";
}

$prompt = "Create {$weeks}-week workout plan for {$member['First_Name']} {$member['Last_Name']} ({$experience}). Goal: {$goal}. {$heightWeight}Injuries: {$injuries}. Medical: {$detailedMedical}.

SAFETY: Avoid exercises that worsen medical conditions. Heart: low intensity, rest. Diabetes: safe for blood sugar. Asthma: proper warm-up, rest. Back/neck: no strain. Surgery: light only. Prioritize safety.

JSON format:
{
  \"program\": {\"title\": \"Title\", \"description\": \"Brief\", \"goal\": \"{$goal}\", \"weeks\": {$weeks}},
  \"days\": {
    \"day1\": [{\"name\": \"Exercise\", \"description\": \"Brief\", \"sets\": 3, \"reps\": 10, \"rest\": \"60s\", \"difficulty\": \"Beginner\", \"targetMuscleGroup\": \"Chest\", \"secondaryMuscles\": \"Triceps\", \"instructions\": \"Steps\", \"equipment\": \"Bodyweight\", \"notes\": \"Notes\"}],
    \"day2\": [], ...day" . ($weeks * 7) . "
  }
}

Rules: Rest days=[]. Vary muscle groups. Match {$experience} level. Progressive but safe. Medical conditions: use safe alternatives/modifications.";

$response = callGemini($prompt, 'gemini-2.5-flash', true);

if (isset($response['error'])) {
    echo json_encode($response);
    exit;
}

// Return the generated workout for preview
echo json_encode([
    'success' => true,
    'workout' => $response,
    'member' => [
        'id' => $member['Member_Id'],
        'name' => $member['First_Name'] . ' ' . $member['Last_Name']
    ]
]);
?>

