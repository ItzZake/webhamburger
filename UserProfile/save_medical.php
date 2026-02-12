<?php
// Start output buffering to catch any unexpected output
ob_start();
ini_set('display_errors', 0);
error_reporting(E_ALL);

include("../DB.php");

// Function to update memberprofile with height, weight, and experience level
function updateMemberProfile($user_id, $height, $weight, $experience, $conn) {
    if (empty($height) && empty($weight) && empty($experience)) {
        return; // Nothing to update
    }
    
    $updates = [];
    $params = [];
    $types = '';
    
    if (!empty($height) && is_numeric($height) && $height > 0) {
        $updates[] = "Height = ?";
        $params[] = (int)$height;
        $types .= 'i';
    }
    
    if (!empty($weight) && is_numeric($weight) && $weight > 0) {
        $updates[] = "Weight = ?";
        $params[] = (float)$weight;
        $types .= 'd';
    }
    
    // Calculate BMI if both height and weight are provided
    if (!empty($height) && !empty($weight) && is_numeric($height) && is_numeric($weight) && $height > 0 && $weight > 0) {
        $heightInMeters = (float)$height / 100; // Convert cm to meters
        $bmi = (float)$weight / ($heightInMeters * $heightInMeters);
        $updates[] = "BMI = ?";
        $params[] = $bmi;
        $types .= 'd';
    }
    
    if (!empty($experience)) {
        $updates[] = "Experience_Level = ?";
        $params[] = $experience;
        $types .= 's';
    }
    
    if (empty($updates)) {
        return; // Nothing to update
    }
    
    $updates[] = "Updated_at = ?";
    $params[] = date('Y-m-d H:i:s');
    $types .= 's';
    
    $params[] = $user_id;
    $types .= 'i';
    
    $sql = "UPDATE memberprofile SET " . implode(', ', $updates) . " WHERE Member_Id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param($types, ...$params);
        if (!$stmt->execute()) {
            error_log('updateMemberProfile error: ' . $stmt->error);
        }
        $stmt->close();
    }
}

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Clear any output that might have been generated
ob_clean();
header('Content-Type: application/json');

try {
    $user_id = $_SESSION['user_id'] ?? null;
    if (!$user_id) {
        ob_clean();
        echo json_encode(['success' => false, 'message' => 'Not logged in']);
        exit;
    }

    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);
    if (!is_array($data)) {
        error_log('save_medical: invalid JSON payload: ' . $raw);
        ob_clean();
        echo json_encode(['success' => false, 'error' => 'Invalid JSON payload']);
        exit;
    }
    $conditions = $data['conditions'] ?? [];
    $age = $data['age'] ?? '';
    $weight = $data['weight'] ?? '';
    $height = $data['height'] ?? '';
    $experience = $data['experience'] ?? '';
    $notes = $data['notes'] ?? '';

// Map conditions to database fields
$fields = [
    'Has_Diabetes' => 0,
    'Has_Heart_Condition' => 0,
    'Has_Asthma' => 0,
    'Has_Thyroid_Disorder' => 0,
    'Has_High_Cholesterol' => 0,
    'Has_Back_Injury' => 0,
    'Has_Neck_Injury' => 0,
    'Has_lactose_intolerance' => 0,
    'Has_gluten_intolerance' => 0,
    'Has_nut_Allergy' => 0,
    'Has_egg_allergy' => 0,
    'has_recent_surgery' => 0
];

foreach ($conditions as $cond) {
    $id = $cond['id'];
    if (isset($fields[$id])) {
        $fields[$id] = 1;
    }
}

$now = date('Y-m-d H:i:s');

// Try update first (simpler and avoids inserting other NOT NULL columns)
$updateSql = "UPDATE MedicalRecord SET
    Has_Diabetes = ?, Has_Heart_Condition = ?, Has_Asthma = ?,
    Has_Thyroid_Disorder = ?, Has_High_Cholesterol = ?,
    Has_Back_Injury = ?, Has_Neck_Injury = ?,
    Has_lactose_intolerance = ?, Has_gluten_intolerance = ?, Has_nut_Allergy = ?,
    Has_egg_allergy = ?, has_recent_surgery = ?, updated_at = ?
    WHERE Member_Id = ?";

// prepare update
$stmt = $conn->prepare($updateSql);
if ($stmt) {
    $stmt->bind_param("iiiiiiiiiiiisi",
        $fields['Has_Diabetes'], $fields['Has_Heart_Condition'], $fields['Has_Asthma'],
        $fields['Has_Thyroid_Disorder'], $fields['Has_High_Cholesterol'],
        $fields['Has_Back_Injury'], $fields['Has_Neck_Injury'],
        $fields['Has_lactose_intolerance'], $fields['Has_gluten_intolerance'], $fields['Has_nut_Allergy'],
        $fields['Has_egg_allergy'], $fields['has_recent_surgery'],
        $now,
        $user_id
    );

    if (!$stmt->execute()) {
        error_log('save_medical UPDATE execute error: ' . $stmt->error);
        $stmt->close();
        // Continue to INSERT if UPDATE fails
    } else {
        $affected = $stmt->affected_rows;
        $stmt->close();

        if ($affected > 0) {
            // Update memberprofile with height, weight, and experience level if provided
            updateMemberProfile($user_id, $height, $weight, $experience, $conn);
            
            ob_clean();
            echo json_encode(['success' => true, 'action' => 'updated']);
            exit;
        }
    }
}

// If no row was updated, insert a full record (only columns that exist in the actual table)
$insertSql = "INSERT INTO MedicalRecord (
  Has_Diabetes, Has_Heart_Condition, Has_Asthma,
  Has_Thyroid_Disorder, Has_High_Cholesterol,
  Has_Back_Injury, Has_Neck_Injury,
  Has_lactose_intolerance, Has_gluten_intolerance, Has_nut_Allergy,
  Has_egg_allergy, has_recent_surgery, updated_at, created_at, Member_Id
) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";

// prepare insert
$stmt = $conn->prepare($insertSql);
if (!$stmt) {
    error_log('save_medical INSERT prepare error: ' . $conn->error);
    ob_clean();
    echo json_encode(['success' => false, 'error' => 'Prepare failed', 'db_error' => $conn->error]);
    exit;
}

$params = [
    // Order matches the column list above (only columns that exist in the actual table)
    $fields['Has_Diabetes'], // Has_Diabetes
    $fields['Has_Heart_Condition'], // Has_Heart_Condition
    $fields['Has_Asthma'], // Has_Asthma
    $fields['Has_Thyroid_Disorder'], // Has_Thyroid_Disorder
    $fields['Has_High_Cholesterol'], // Has_High_Cholesterol
    $fields['Has_Back_Injury'], // Has_Back_Injury
    $fields['Has_Neck_Injury'], // Has_Neck_Injury
    $fields['Has_lactose_intolerance'], // Has_lactose_intolerance
    $fields['Has_gluten_intolerance'], // Has_gluten_intolerance
    $fields['Has_nut_Allergy'], // Has_nut_Allergy
    $fields['Has_egg_allergy'], // Has_egg_allergy
    $fields['has_recent_surgery'], // has_recent_surgery
    $now, // updated_at
    $now, // created_at
    $user_id // Member_Id
];

// Build types: Count parameters carefully
// Looking at $params array (15 elements):
// Positions 0-11: 12 ints (medical condition fields)
// Position 12: $now (s) - updated_at
// Position 13: $now (s) - created_at
// Position 14: $user_id (i) - Member_Id
// Total: 12 ints + 2 strings + 1 int = 15 parameters
$types = str_repeat('i', 12) . 'ssi'; // 12 ints + 2 strings + 1 int = 15 total

if (!$stmt->bind_param($types, ...$params)) {
    error_log('save_medical bind_param error: ' . $stmt->error);
}
$success = $stmt->execute();
if (!$success) {
    error_log('save_medical INSERT execute error: ' . $stmt->error);
    ob_clean();
    echo json_encode(['success' => false, 'error' => 'Execute failed', 'db_error' => $stmt->error]);
    $stmt->close();
    exit;
}

$stmt->close();

// Update memberprofile with height, weight, and experience level if provided
updateMemberProfile($user_id, $height, $weight, $experience, $conn);

ob_clean();
echo json_encode(['success' => true, 'action' => 'inserted']);
} catch (Throwable $e) {
    error_log('save_medical fatal: ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString());
    ob_clean();
    echo json_encode(['success' => false, 'error' => 'Server error', 'exception' => $e->getMessage()]);
    exit;
}
?>