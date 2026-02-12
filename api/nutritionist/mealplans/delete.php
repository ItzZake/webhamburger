<?php
/**
 * Delete meal plan and all associated meals and meal plan items
 * POST: { meal_plan_id: int }
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

if (!$data || empty($data['meal_plan_id'])) {
    ob_clean();
    echo json_encode(['success' => false, 'error' => 'Meal plan ID is required']);
    exit;
}

$mealPlanId = (int)$data['meal_plan_id'];
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
    echo json_encode(['success' => false, 'error' => 'Meal plan not found or you do not have permission to delete it']);
    exit;
}

// Start transaction
$conn->begin_transaction();

try {
    $now = date('Y-m-d H:i:s');
    
    // Get all meal IDs for this meal plan
    $mealsSql = "SELECT Meal_ID FROM meal WHERE Meal_Plan_ID = ? AND is_deleted = 0";
    $mealsStmt = $conn->prepare($mealsSql);
    $mealsStmt->bind_param('i', $mealPlanId);
    $mealsStmt->execute();
    $mealsResult = $mealsStmt->get_result();
    $mealIds = [];
    while ($row = $mealsResult->fetch_assoc()) {
        $mealIds[] = $row['Meal_ID'];
    }
    $mealsStmt->close();
    
    // Delete meal plan items (soft delete)
    if (count($mealIds) > 0) {
        $placeholders = implode(',', array_fill(0, count($mealIds), '?'));
        $deleteItemsSql = "UPDATE mealplanitem 
                          SET is_deleted = 1, Updated_at = ? 
                          WHERE Meal_ID IN ($placeholders) AND is_deleted = 0";
        $deleteItemsStmt = $conn->prepare($deleteItemsSql);
        if (!$deleteItemsStmt) {
            throw new Exception('Database error: ' . $conn->error);
        }
        
        $params = array_merge([$now], $mealIds);
        $types = 's' . str_repeat('i', count($mealIds));
        $deleteItemsStmt->bind_param($types, ...$params);
        if (!$deleteItemsStmt->execute()) {
            throw new Exception('Failed to delete meal plan items: ' . $deleteItemsStmt->error);
        }
        $deleteItemsStmt->close();
    }
    
    // Delete meals (soft delete)
    if (count($mealIds) > 0) {
        $placeholders = implode(',', array_fill(0, count($mealIds), '?'));
        $deleteMealsSql = "UPDATE meal 
                          SET is_deleted = 1, Updated_at = ? 
                          WHERE Meal_ID IN ($placeholders) AND is_deleted = 0";
        $deleteMealsStmt = $conn->prepare($deleteMealsSql);
        if (!$deleteMealsStmt) {
            throw new Exception('Database error: ' . $conn->error);
        }
        
        $params = array_merge([$now], $mealIds);
        $types = 's' . str_repeat('i', count($mealIds));
        $deleteMealsStmt->bind_param($types, ...$params);
        if (!$deleteMealsStmt->execute()) {
            throw new Exception('Failed to delete meals: ' . $deleteMealsStmt->error);
        }
        $deleteMealsStmt->close();
    }
    
    // Delete meal plan (soft delete)
    $deletePlanSql = "UPDATE mealplan 
                      SET is_deleted = 1, Updated_at = ? 
                      WHERE Meal_Plan_ID = ?";
    $deletePlanStmt = $conn->prepare($deletePlanSql);
    if (!$deletePlanStmt) {
        throw new Exception('Database error: ' . $conn->error);
    }
    
    $deletePlanStmt->bind_param('si', $now, $mealPlanId);
    if (!$deletePlanStmt->execute()) {
        throw new Exception('Failed to delete meal plan: ' . $deletePlanStmt->error);
    }
    $deletePlanStmt->close();
    
    // Commit transaction
    $conn->commit();
    
    ob_clean();
    echo json_encode([
        'success' => true,
        'message' => 'Meal plan and all associated meals deleted successfully',
        'meal_plan_id' => $mealPlanId,
        'meals_deleted' => count($mealIds)
    ]);
    
} catch (Exception $e) {
    $conn->rollback();
    ob_clean();
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

ob_end_flush();
?>

