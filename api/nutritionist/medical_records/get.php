<?php
ob_start();
ini_set('display_errors', 0);
error_reporting(E_ALL);

require_once __DIR__ . '/../../../DB.php';
require_once __DIR__ . '/../../helpers/auth.php';
header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_role(['doctor','nutritionist','admin']);

$memberId = isset($_GET['member_id']) ? (int)$_GET['member_id'] : 0;

if (!$memberId) {
    ob_clean();
    echo json_encode(['success' => false, 'error' => 'Member ID is required']);
    exit;
}

// Get medical record
$sql = "SELECT * FROM medicalrecord WHERE Member_Id = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    ob_clean();
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $conn->error]);
    exit;
}

$stmt->bind_param('i', $memberId);
if (!$stmt->execute()) {
    ob_clean();
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $stmt->error]);
    $stmt->close();
    exit;
}

$result = $stmt->get_result();
$medicalRecord = $result->fetch_assoc();
$stmt->close();

// Get member profile for additional info
$memberSql = "SELECT Height, Weight, BMI, Medical_Condition, Injuries FROM memberprofile WHERE Member_Id = ?";
$memberStmt = $conn->prepare($memberSql);
$memberProfile = null;
if ($memberStmt) {
    $memberStmt->bind_param('i', $memberId);
    if ($memberStmt->execute()) {
        $memberResult = $memberStmt->get_result();
        $memberProfile = $memberResult->fetch_assoc();
    }
    $memberStmt->close();
}

// Format medical conditions
$conditions = [];
if ($medicalRecord) {
    $conditionFields = [
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
    
    foreach ($conditionFields as $field => $label) {
        if (isset($medicalRecord[$field]) && $medicalRecord[$field] == 1) {
            $conditions[] = $label;
        }
    }
}

ob_clean();
echo json_encode([
    'success' => true,
    'medical_record' => $medicalRecord,
    'member_profile' => $memberProfile,
    'conditions' => $conditions
]);
ob_end_flush();
?>

