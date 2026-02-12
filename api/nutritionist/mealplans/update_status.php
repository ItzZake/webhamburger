<?php
/**
 * Update meal plan status (Activate/Deactivate)
 * POST: { meal_plan_id: int, status: 'Active' | 'Pending' | 'Inactive' }
 */

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

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!$data || empty($data['meal_plan_id']) || empty($data['status'])) {
    ob_clean();
    echo json_encode(['success' => false, 'error' => 'Meal plan ID and status are required']);
    exit;
}

$mealPlanId = (int)$data['meal_plan_id'];
$status = $data['status'];

// Validate status
$allowedStatuses = ['Active', 'Pending', 'Inactive'];
if (!in_array($status, $allowedStatuses)) {
    ob_clean();
    echo json_encode(['success' => false, 'error' => 'Invalid status. Must be one of: ' . implode(', ', $allowedStatuses)]);
    exit;
}

$nutritionistId = current_user_id();

// Verify meal plan exists and belongs to this nutritionist
$checkSql = "SELECT Meal_Plan_ID, Nutritionist_ID FROM mealplan 
             WHERE Meal_Plan_ID = ? AND Nutritionist_ID = ? AND is_deleted = 0";
$checkStmt = $conn->prepare($checkSql);
if (!$checkStmt) {
    ob_clean();
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $conn->error]);
    exit;
}

$checkStmt->bind_param('ii', $mealPlanId, $nutritionistId);
$checkStmt->execute();
$result = $checkStmt->get_result();
$mealPlan = $result->fetch_assoc();
$checkStmt->close();

if (!$mealPlan) {
    ob_clean();
    echo json_encode(['success' => false, 'error' => 'Meal plan not found or you do not have permission to modify it']);
    exit;
}

// Update status
$now = date('Y-m-d H:i:s');
$updateSql = "UPDATE mealplan 
              SET Status = ?, Updated_at = ? 
              WHERE Meal_Plan_ID = ?";
$updateStmt = $conn->prepare($updateSql);
if (!$updateStmt) {
    ob_clean();
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $conn->error]);
    exit;
}

$updateStmt->bind_param('ssi', $status, $now, $mealPlanId);
if (!$updateStmt->execute()) {
    ob_clean();
    echo json_encode(['success' => false, 'error' => 'Failed to update meal plan: ' . $updateStmt->error]);
    $updateStmt->close();
    exit;
}

$updateStmt->close();

ob_clean();
echo json_encode([
    'success' => true,
    'message' => "Meal plan status updated to {$status}",
    'meal_plan_id' => $mealPlanId,
    'status' => $status
]);
ob_end_flush();
?>

