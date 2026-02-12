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

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!$data) {
    ob_clean();
    echo json_encode(['success' => false, 'error' => 'Invalid JSON data']);
    exit;
}

// Validate required fields
if (empty($data['title']) || empty($data['member_id'])) {
    ob_clean();
    echo json_encode(['success' => false, 'error' => 'Title and member_id are required']);
    exit;
}

$nutritionistId = current_user_id();
$memberId = (int)$data['member_id'];
$now = date('Y-m-d H:i:s');

// Verify nutritionist exists
$nutCheck = $conn->prepare("SELECT Nutritionist_ID FROM nutritionistprofile WHERE Nutritionist_ID = ?");
$nutCheck->bind_param('i', $nutritionistId);
$nutCheck->execute();
$nutResult = $nutCheck->get_result();
if (!$nutResult->num_rows) {
    $nutCheck->close();
    ob_clean();
    echo json_encode(['success' => false, 'error' => 'Nutritionist profile not found']);
    exit;
}
$nutCheck->close();

// Use user-specified totals (not calculated from meals)
// The user sets these values in the form, and they should be used as-is
$totalCalories = (int)($data['total_daily_calories'] ?? 0);
$totalCarbs = (int)($data['carbs_grams_per_day'] ?? 0);
$totalProtein = (int)($data['protein_grams_per_day'] ?? 0);
$totalFats = (int)($data['fats_grams_per_day'] ?? 0);

// Validate that user provided values
if ($totalCalories <= 0) {
    ob_clean();
    echo json_encode(['success' => false, 'error' => 'Total daily calories must be greater than 0']);
    exit;
}

// Calculate actual meal totals for validation (allow ±100 calories tolerance)
$calculatedMealCalories = 0;
if (isset($data['meals']) && is_array($data['meals'])) {
    foreach ($data['meals'] as $category => $mealList) {
        if (is_array($mealList)) {
            foreach ($mealList as $meal) {
                if (isset($meal['calories']) && $meal['calories'] > 0) {
                    $calculatedMealCalories += (int)$meal['calories'];
                }
            }
        }
    }
}

// Warn if meal totals are more than 100 calories away from target (but still allow it)
$calorieDifference = abs($calculatedMealCalories - $totalCalories);
if ($calorieDifference > 100 && $calculatedMealCalories > 0) {
    error_log("Meal plan calorie warning: Target is {$totalCalories} cal, but meals total {$calculatedMealCalories} cal (difference: {$calorieDifference} cal)");
    // Still allow it, just log a warning
}

// Start transaction
$conn->begin_transaction();

try {
    // Find and hard delete any "Pending Assignment" meal plan for this member
    $pendingCheckSql = "SELECT Meal_Plan_ID FROM mealplan 
                        WHERE Member_Id = ? AND Title = 'Pending Assignment' AND Status = 'Pending'";
    $pendingCheckStmt = $conn->prepare($pendingCheckSql);
    if ($pendingCheckStmt) {
        $pendingCheckStmt->bind_param('i', $memberId);
        $pendingCheckStmt->execute();
        $pendingResult = $pendingCheckStmt->get_result();
        $pendingPlans = [];
        while ($row = $pendingResult->fetch_assoc()) {
            $pendingPlans[] = $row['Meal_Plan_ID'];
        }
        $pendingCheckStmt->close();
        
        // Hard delete all pending assignment meal plans and their associated data
        foreach ($pendingPlans as $pendingPlanId) {
            // Get all meal IDs for this pending meal plan
            $mealsSql = "SELECT Meal_ID FROM meal WHERE Meal_Plan_ID = ?";
            $mealsStmt = $conn->prepare($mealsSql);
            if ($mealsStmt) {
                $mealsStmt->bind_param('i', $pendingPlanId);
                $mealsStmt->execute();
                $mealsResult = $mealsStmt->get_result();
                $mealIds = [];
                while ($mealRow = $mealsResult->fetch_assoc()) {
                    $mealIds[] = $mealRow['Meal_ID'];
                }
                $mealsStmt->close();
                
                // Hard delete meal plan items
                if (count($mealIds) > 0) {
                    $placeholders = implode(',', array_fill(0, count($mealIds), '?'));
                    $deleteItemsSql = "DELETE FROM mealplanitem WHERE Meal_ID IN ($placeholders)";
                    $deleteItemsStmt = $conn->prepare($deleteItemsSql);
                    if ($deleteItemsStmt) {
                        $types = str_repeat('i', count($mealIds));
                        $deleteItemsStmt->bind_param($types, ...$mealIds);
                        $deleteItemsStmt->execute();
                        $deleteItemsStmt->close();
                    }
                }
                
                // Hard delete meals
                if (count($mealIds) > 0) {
                    $placeholders = implode(',', array_fill(0, count($mealIds), '?'));
                    $deleteMealsSql = "DELETE FROM meal WHERE Meal_ID IN ($placeholders)";
                    $deleteMealsStmt = $conn->prepare($deleteMealsSql);
                    if ($deleteMealsStmt) {
                        $types = str_repeat('i', count($mealIds));
                        $deleteMealsStmt->bind_param($types, ...$mealIds);
                        $deleteMealsStmt->execute();
                        $deleteMealsStmt->close();
                    }
                }
            }
            
            // Hard delete meal logs associated with this pending meal plan
            $deleteLogsSql = "DELETE FROM meallog WHERE Meal_Plan_ID = ?";
            $deleteLogsStmt = $conn->prepare($deleteLogsSql);
            if ($deleteLogsStmt) {
                $deleteLogsStmt->bind_param('i', $pendingPlanId);
                $deleteLogsStmt->execute();
                $deleteLogsStmt->close();
            }
            
            // Hard delete the meal plan itself
            $deletePlanSql = "DELETE FROM mealplan WHERE Meal_Plan_ID = ?";
            $deletePlanStmt = $conn->prepare($deletePlanSql);
            if ($deletePlanStmt) {
                $deletePlanStmt->bind_param('i', $pendingPlanId);
                $deletePlanStmt->execute();
                $deletePlanStmt->close();
            }
        }
    }
    
    // Create meal plan
    $insertSql = "INSERT INTO mealplan 
                  (Title, Description, Total_daily_Calories, Carbs_grams_per_day, Protein_grams_per_day, 
                   Fats_grams_per_day, Status, Start_Date, End_Date, Created_at, Updated_at, 
                   Nutritionist_ID, Member_Id) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $title = $data['title'];
    $description = $data['description'] ?? '';
    $status = 'Active';
    $startDate = $data['start_date'] ?? date('Y-m-d');
    $endDate = $data['end_date'] ?? date('Y-m-d', strtotime('+4 weeks'));
    
    $stmt = $conn->prepare($insertSql);
    if (!$stmt) {
        throw new Exception('Database error: ' . $conn->error);
    }
    
    $stmt->bind_param('ssiiiiissssii', $title, $description, $totalCalories, 
                      $totalCarbs, $totalProtein, $totalFats, $status, 
                      $startDate, $endDate, $now, $now, $nutritionistId, $memberId);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to create meal plan: ' . $stmt->error);
    }
    
    $mealPlanId = $conn->insert_id;
    $stmt->close();
    
    // Create meals and food items
    $meals = $data['meals'] ?? [];
    $mealsCreated = 0;
    $foodItemsCreated = 0;
    
    foreach ($meals as $mealCategory => $mealList) {
        // Map category name to time of day
        $timeOfDayMap = [
            'Breakfast' => 1,
            'Lunch' => 2,
            'Dinner' => 3,
            'Pre-Workout' => 4,
            'Post-Workout' => 5,
            'Snacks' => 6
        ];
        
        $timeOfDay = $timeOfDayMap[$mealCategory] ?? 1;
        
        foreach ($mealList as $mealIndex => $meal) {
            if (empty($meal['name']) || empty($meal['food_items']) || count($meal['food_items']) === 0) {
                continue;
            }
            
            // Create meal
            $mealSql = "INSERT INTO meal (Name, Sequence_Order, Target_Time_of_Day, Created_at, Updated_at, Meal_Plan_ID) 
                       VALUES (?, ?, ?, ?, ?, ?)";
            $mealStmt = $conn->prepare($mealSql);
            if (!$mealStmt) {
                throw new Exception('Database error: ' . $conn->error);
            }
            
            $mealName = $meal['name'];
            $sequenceOrder = $mealIndex + 1;
            $mealStmt->bind_param('siissi', $mealName, $sequenceOrder, $timeOfDay, $now, $now, $mealPlanId);
            
            if (!$mealStmt->execute()) {
                $mealStmt->close();
                throw new Exception('Failed to create meal: ' . $mealStmt->error);
            }
            
            $mealId = $conn->insert_id;
            $mealsCreated++;
            $mealStmt->close();
            
            // Create meal plan items (food items in meal)
            foreach ($meal['food_items'] as $foodItem) {
                if (empty($foodItem['food_item_id']) || empty($foodItem['quantity_servings'])) {
                    continue;
                }
                
                $itemSql = "INSERT INTO mealplanitem 
                           (Meal_ID, Food_Item_ID, Quantity_Servings, Notes, Created_at, Updated_at) 
                           VALUES (?, ?, ?, ?, ?, ?)";
                $itemStmt = $conn->prepare($itemSql);
                if (!$itemStmt) {
                    throw new Exception('Database error: ' . $conn->error);
                }
                
                $foodItemId = (int)$foodItem['food_item_id'];
                $quantity = (int)$foodItem['quantity_servings'];
                $notes = $foodItem['notes'] ?? '';
                
                $itemStmt->bind_param('iiisss', $mealId, $foodItemId, $quantity, $notes, $now, $now);
                
                if (!$itemStmt->execute()) {
                    $itemStmt->close();
                    throw new Exception('Failed to create meal plan item: ' . $itemStmt->error);
                }
                
                $foodItemsCreated++;
                $itemStmt->close();
            }
        }
    }
    
    // Commit transaction
    $conn->commit();
    
    ob_clean();
    echo json_encode([
        'success' => true,
        'meal_plan_id' => $mealPlanId,
        'meals_created' => $mealsCreated,
        'food_items_created' => $foodItemsCreated,
        'message' => 'Meal plan created successfully'
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

