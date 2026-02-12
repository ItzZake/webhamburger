<?php
include("../DB.php");
session_start();

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    echo json_encode(null);
    exit;
}

$stmt = $conn->prepare("SELECT * FROM medicalrecord WHERE Member_Id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$conditions = [];
if ($row = $result->fetch_assoc()) {
    $fields = [
        'Has_Diabetes', 'Has_Heart_Condition', 'Has_Asthma', 'Has_Thyroid_Disorder',
        'Has_High_Cholesterol', 'Has_Back_Injury', 'Has_Neck_Injury',
        'Has_lactose_intolerance', 'Has_gluten_intolerance', 'Has_nut_Allergy', 'Has_egg_allergy', 'has_recent_surgery'
    ];
    foreach ($fields as $f) {
        if ($row[$f] == 1) {
            $conditions[] = ['id' => $f, 'note' => ''];
        }
    }
}
$stmt->close();

echo json_encode(['conditions' => $conditions, 'weight' => '', 'age' => '', 'allergies' => '', 'height' => '']);
?>