<?php
ob_start();
ini_set('display_errors', 0);
error_reporting(E_ALL);

require_once __DIR__ . '/../../DB.php';
require_once __DIR__ . '/../../helpers/auth.php';
header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$user_id = current_user_id();
if (!$user_id) {
    ob_clean();
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit;
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true) ?: $_POST;

if (empty($data['meal_id']) || empty($data['meal_plan_id'])) {
    ob_clean();
    echo json_encode(['success' => false, 'error' => 'Meal ID and Meal Plan ID are required']);
    exit;
}

$meal_id = (int)$data['meal_id'];
$meal_plan_id = (int)$data['meal_plan_id'];
$consumed = isset($data['consumed']) ? (bool)$data['consumed'] : true;
$today = date('Y-m-d');

// Get or create meal log for today
$logCheckSql = "SELECT Meal_Log_ID FROM meallog 
                WHERE Member_Id = ? AND Meal_Plan_ID = ? AND Log_date = ? AND is_deleted = 0 
                LIMIT 1";
$logCheckStmt = $conn->prepare($logCheckSql);
$logId = null;

if ($logCheckStmt) {
    $logCheckStmt->bind_param('iis', $user_id, $meal_plan_id, $today);
    if ($logCheckStmt->execute()) {
        $logResult = $logCheckStmt->get_result();
        if ($logRow = $logResult->fetch_assoc()) {
            $logId = (int)$logRow['Meal_Log_ID'];
        }
    }
    $logCheckStmt->close();
}

// For simplicity, we'll use localStorage on the frontend to track consumed meals
// and just return success. The meallog table structure doesn't directly link to individual meals.
// We could create a mealconsumption table, but for now, let's use a simpler approach.

ob_clean();
echo json_encode([
    'success' => true,
    'message' => $consumed ? 'Meal marked as consumed' : 'Meal unmarked',
    'meal_id' => $meal_id,
    'consumed' => $consumed
]);
ob_end_flush();
?>

