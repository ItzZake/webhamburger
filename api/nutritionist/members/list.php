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

$nutritionistId = current_user_id();

// Get members assigned to this nutritionist (through meal plans)
$sql = "SELECT DISTINCT m.Member_Id, u.First_Name, u.Last_Name, u.Email, m.Training_Goals, m.Height, m.Weight, m.BMI
        FROM mealplan mp
        JOIN memberprofile m ON mp.Member_Id = m.Member_Id
        JOIN userprofile u ON m.Member_Id = u.User_ID
        WHERE mp.Nutritionist_ID = ? AND m.is_deleted = 0 AND u.is_deleted = 0 AND mp.is_deleted = 0
        ORDER BY u.First_Name, u.Last_Name";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    ob_clean();
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $conn->error]);
    exit;
}

$stmt->bind_param('i', $nutritionistId);
if (!$stmt->execute()) {
    ob_clean();
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $stmt->error]);
    $stmt->close();
    exit;
}

$result = $stmt->get_result();
$members = [];
while ($row = $result->fetch_assoc()) {
    $members[] = $row;
}
$stmt->close();

ob_clean();
echo json_encode($members);
ob_end_flush();
?>

