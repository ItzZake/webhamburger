<?php
/**
 * Update script for Meals - Recalculate and fix fat grams
 * This script recalculates fat grams for all meals based on their associated food items
 * and updates the meal records if they have a Fat column
 */

require_once __DIR__ . '/../../DB.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Only allow admins or run from command line
if (php_sapi_name() !== 'cli' && (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin')) {
    die("Access denied. This script can only be run by admins or from command line.");
}

$now = date('Y-m-d H:i:s');

// Check if meal table has a Fat column
$checkColumnSql = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS 
                   WHERE TABLE_SCHEMA = DATABASE() 
                   AND TABLE_NAME = 'meal' 
                   AND COLUMN_NAME IN ('Fat', 'Fats_Grams')";
$columnResult = $conn->query($checkColumnSql);
$hasFatColumn = false;
$fatColumnName = null;
if ($columnResult && $columnResult->num_rows > 0) {
    $hasFatColumn = true;
    $row = $columnResult->fetch_assoc();
    $fatColumnName = $row['COLUMN_NAME'];
    echo "Found Fat column: $fatColumnName\n";
} else {
    echo "No Fat column found in meal table. Meals are calculated dynamically from food items.\n";
    echo "This script will verify and report fat calculations.\n";
}

// Get all meals that are not deleted
$mealsSql = "SELECT m.Meal_ID, m.Name, m.Meal_Plan_ID
              FROM meal m
              WHERE m.is_deleted = 0
              ORDER BY m.Meal_ID";
$mealsResult = $conn->query($mealsSql);

if (!$mealsResult) {
    die("Error fetching meals: " . $conn->error . "\n");
}

$updated = 0;
$skipped = 0;
$errors = 0;
$totalMeals = 0;

echo "\n=== Processing Meals ===\n";

while ($mealRow = $mealsResult->fetch_assoc()) {
    $mealId = (int)$mealRow['Meal_ID'];
    $mealName = $mealRow['Name'];
    $totalMeals++;
    
    // Calculate total fat from food items for this meal
    $fatSql = "SELECT COALESCE(SUM(fi.Fats_Grams * mpi.Quantity_Servings), 0) AS total_fats
               FROM mealplanitem mpi
               JOIN fooditem fi ON mpi.Food_Item_ID = fi.Food_Item_ID
               WHERE mpi.Meal_ID = ? 
               AND mpi.is_deleted = 0 
               AND fi.is_deleted = 0";
    
    $fatStmt = $conn->prepare($fatSql);
    if (!$fatStmt) {
        echo "Error preparing statement for meal $mealId: " . $conn->error . "\n";
        $errors++;
        continue;
    }
    
    $fatStmt->bind_param('i', $mealId);
    if (!$fatStmt->execute()) {
        echo "Error executing query for meal $mealId: " . $fatStmt->error . "\n";
        $fatStmt->close();
        $errors++;
        continue;
    }
    
    $fatResult = $fatStmt->get_result();
    $fatRow = $fatResult->fetch_assoc();
    $calculatedFats = (float)($fatRow['total_fats'] ?? 0);
    $fatStmt->close();
    
    // Also get calories, protein, and carbs for verification
    $macroSql = "SELECT 
                    COALESCE(SUM(fi.Calories * mpi.Quantity_Servings), 0) AS total_calories,
                    COALESCE(SUM(fi.Protein_Grams * mpi.Quantity_Servings), 0) AS total_protein,
                    COALESCE(SUM(fi.Carbs_Grams * mpi.Quantity_Servings), 0) AS total_carbs,
                    COALESCE(SUM(fi.Fats_Grams * mpi.Quantity_Servings), 0) AS total_fats
                 FROM mealplanitem mpi
                 JOIN fooditem fi ON mpi.Food_Item_ID = fi.Food_Item_ID
                 WHERE mpi.Meal_ID = ? 
                 AND mpi.is_deleted = 0 
                 AND fi.is_deleted = 0";
    
    $macroStmt = $conn->prepare($macroSql);
    if ($macroStmt) {
        $macroStmt->bind_param('i', $mealId);
        if ($macroStmt->execute()) {
            $macroResult = $macroStmt->get_result();
            $macroRow = $macroResult->fetch_assoc();
            $totalCalories = (int)($macroRow['total_calories'] ?? 0);
            $totalProtein = (float)($macroRow['total_protein'] ?? 0);
            $totalCarbs = (float)($macroRow['total_carbs'] ?? 0);
            $totalFats = (float)($macroRow['total_fats'] ?? 0);
            
            // Calculate fat percentage
            $fatPercentage = $totalCalories > 0 ? ($totalFats * 9 / $totalCalories) * 100 : 0;
            
            // Check if fat percentage is reasonable (should be between 10% and 40% typically)
            $isReasonable = $fatPercentage >= 10 && $fatPercentage <= 40;
            
            if ($hasFatColumn && $fatColumnName) {
                // Update the meal's Fat column
                $updateSql = "UPDATE meal SET `$fatColumnName` = ?, Updated_at = ? WHERE Meal_ID = ?";
                $updateStmt = $conn->prepare($updateSql);
                if ($updateStmt) {
                    $updateStmt->bind_param('dsi', $totalFats, $now, $mealId);
                    if ($updateStmt->execute()) {
                        $updated++;
                        $status = $isReasonable ? "✓" : "⚠";
                        echo "$status Meal #$mealId ($mealName): Fats = {$totalFats}g ({$fatPercentage}% of {$totalCalories} cal) - UPDATED\n";
                    } else {
                        $errors++;
                        echo "✗ Error updating meal $mealId: " . $updateStmt->error . "\n";
                    }
                    $updateStmt->close();
                } else {
                    $errors++;
                    echo "✗ Error preparing update for meal $mealId: " . $conn->error . "\n";
                }
            } else {
                // Just report the calculated values
                $status = $isReasonable ? "✓" : "⚠";
                echo "$status Meal #$mealId ($mealName): Fats = {$totalFats}g ({$fatPercentage}% of {$totalCalories} cal)\n";
                if (!$isReasonable && $totalCalories > 0) {
                    echo "   Warning: Fat percentage is outside normal range (10-40%)\n";
                }
                $skipped++;
            }
        }
        $macroStmt->close();
    }
}

echo "\n=== Update Summary ===\n";
echo "Total meals processed: $totalMeals\n";
if ($hasFatColumn) {
    echo "Updated: $updated meals\n";
    echo "Errors: $errors meals\n";
} else {
    echo "Meals analyzed: $totalMeals\n";
    echo "Note: Meal table doesn't have a Fat column. Fat values are calculated dynamically from food items.\n";
    echo "Since food items have been updated with correct fat values, meals will now show correct fat amounts.\n";
}
echo "\n=== Recommendations ===\n";
echo "1. Food items have been updated with correct fat values.\n";
echo "2. Meals are calculated dynamically from food items, so they should now show correct values.\n";
echo "3. If you see meals with fat percentages outside 10-40%, review the food items in those meals.\n";
?>

