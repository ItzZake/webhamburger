<?php
// Set error handler to catch all errors
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== NULL && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        ob_clean();
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false, 
            'error' => 'PHP Fatal Error: ' . $error['message'] . ' in ' . $error['file'] . ' on line ' . $error['line']
        ]);
    }
});

ob_start();
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Log errors to a file for debugging
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../../error_log.txt');

try {
    require_once __DIR__ . '/../../../DB.php';
    require_once __DIR__ . '/../../helpers/auth.php';

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    header('Content-Type: application/json');

    // Check if database connection exists
    if (!isset($conn) || !$conn) {
        ob_clean();
        echo json_encode(['success' => false, 'error' => 'Database connection failed']);
        exit;
    }

    $user_id = current_user_id();
    if (!$user_id) {
        ob_clean();
        echo json_encode(['success' => false, 'error' => 'Not logged in. Session keys: ' . json_encode(array_keys($_SESSION ?? []))]);
        exit;
    }
} catch (Exception $e) {
    ob_clean();
    error_log("Error in mealplan/get.php: " . $e->getMessage() . "\n" . $e->getTraceAsString());
    echo json_encode(['success' => false, 'error' => 'Initialization error: ' . $e->getMessage()]);
    exit;
} catch (Error $e) {
    ob_clean();
    error_log("Fatal error in mealplan/get.php: " . $e->getMessage() . "\n" . $e->getTraceAsString());
    echo json_encode(['success' => false, 'error' => 'Fatal error: ' . $e->getMessage()]);
    exit;
}

try {
    // Get active meal plan for user (prioritize Active over Pending)
    $sql = "SELECT mp.*, u.First_Name AS Nutritionist_First, u.Last_Name AS Nutritionist_Last
            FROM mealplan mp
            LEFT JOIN nutritionistprofile np ON mp.Nutritionist_ID = np.Nutritionist_ID
            LEFT JOIN userprofile u ON np.Nutritionist_ID = u.User_ID
            WHERE mp.Member_Id = ? AND mp.Status = 'Active' AND mp.is_deleted = 0
            ORDER BY mp.Created_at DESC
            LIMIT 1";
            
    // If no active meal plan, try pending
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception('Database prepare error: ' . $conn->error);
    }

    $stmt->bind_param('i', $user_id);
    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        throw new Exception('Database execute error: ' . $error);
    }

    $result = $stmt->get_result();
    $mealPlan = $result->fetch_assoc();
    $stmt->close();
} catch (Exception $e) {
    ob_clean();
    error_log("Error fetching active meal plan: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    exit;
}

// If no active meal plan found, try pending
if (!$mealPlan) {
    try {
        $sqlPending = "SELECT mp.*, u.First_Name AS Nutritionist_First, u.Last_Name AS Nutritionist_Last
                       FROM mealplan mp
                       LEFT JOIN nutritionistprofile np ON mp.Nutritionist_ID = np.Nutritionist_ID
                       LEFT JOIN userprofile u ON np.Nutritionist_ID = u.User_ID
                       WHERE mp.Member_Id = ? AND mp.Status = 'Pending' AND mp.is_deleted = 0
                       ORDER BY mp.Created_at DESC
                       LIMIT 1";
        
        $stmtPending = $conn->prepare($sqlPending);
        if (!$stmtPending) {
            throw new Exception('Database prepare error (pending): ' . $conn->error);
        }
        
        $stmtPending->bind_param('i', $user_id);
        if (!$stmtPending->execute()) {
            $error = $stmtPending->error;
            $stmtPending->close();
            throw new Exception('Database execute error (pending): ' . $error);
        }
        
        $resultPending = $stmtPending->get_result();
        $mealPlan = $resultPending->fetch_assoc();
        $stmtPending->close();
    } catch (Exception $e) {
        error_log("Error fetching pending meal plan: " . $e->getMessage());
        // Don't exit here, let it continue to check if mealPlan is null
    }
}

if (!$mealPlan) {
    // Check if user has a nutritionist assigned (even if no meal plan yet)
    $nutritionistCheck = $conn->prepare("SELECT Nutritionist_ID FROM mealplan 
                                        WHERE Member_Id = ? AND Nutritionist_ID IS NOT NULL AND is_deleted = 0 
                                        LIMIT 1");
    if ($nutritionistCheck) {
        $nutritionistCheck->bind_param('i', $user_id);
        if ($nutritionistCheck->execute()) {
            $nutritionistResult = $nutritionistCheck->get_result();
            if ($nutritionistResult->num_rows > 0) {
                $nutritionistData = $nutritionistResult->fetch_assoc();
                $nutritionistCheck->close();
                // User has a nutritionist but no active meal plan - show message
                ob_clean();
                echo json_encode([
                    'success' => false, 
                    'error' => 'No active meal plan found',
                    'has_nutritionist' => true,
                    'message' => 'Your nutritionist is currently creating your personalized meal plan. Please check back soon!'
                ]);
                exit;
            }
        }
        $nutritionistCheck->close();
    }
    
    ob_clean();
    echo json_encode(['success' => false, 'error' => 'No active meal plan found']);
    exit;
}

$mealPlanId = $mealPlan['Meal_Plan_ID'];

try {
    // Get all meals for this meal plan, grouped by time of day
    // Calculate calories, protein, carbs, and fats from food items
    // Note: Food items have Serving_Size (typically 100g), and values are per Serving_Size
    // So if Quantity_Servings = 2 and Serving_Size = 100, we get 200g worth
    // Calculation: value * Quantity_Servings (since value is already per Serving_Size)
    $mealsSql = "SELECT m.Meal_ID, m.Name, m.Sequence_Order, m.Target_Time_of_Day,
                        COALESCE(SUM(fi.Calories * mpi.Quantity_Servings), 0) AS total_calories,
                        COALESCE(SUM(fi.Protein_Grams * mpi.Quantity_Servings), 0) AS total_protein,
                        COALESCE(SUM(fi.Carbs_Grams * mpi.Quantity_Servings), 0) AS total_carbs,
                        COALESCE(SUM(fi.Fats_Grams * mpi.Quantity_Servings), 0) AS total_fats
                 FROM meal m
                 LEFT JOIN mealplanitem mpi ON m.Meal_ID = mpi.Meal_ID AND mpi.is_deleted = 0
                 LEFT JOIN fooditem fi ON mpi.Food_Item_ID = fi.Food_Item_ID AND fi.is_deleted = 0
                 WHERE m.Meal_Plan_ID = ? AND m.is_deleted = 0
                 GROUP BY m.Meal_ID, m.Name, m.Sequence_Order, m.Target_Time_of_Day
                 ORDER BY m.Target_Time_of_Day, m.Sequence_Order";

    $mealsStmt = $conn->prepare($mealsSql);
    if (!$mealsStmt) {
        throw new Exception('Database prepare error (meals): ' . $conn->error);
    }

    $mealsStmt->bind_param('i', $mealPlanId);
    if (!$mealsStmt->execute()) {
        $error = $mealsStmt->error;
        $mealsStmt->close();
        throw new Exception('Database execute error (meals): ' . $error);
    }
} catch (Exception $e) {
    ob_clean();
    error_log("Error fetching meals: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    exit;
}

$mealsResult = $mealsStmt->get_result();
$meals = [];
while ($row = $mealsResult->fetch_assoc()) {
    $timeOfDay = (int)$row['Target_Time_of_Day'];
    $timeName = 'Breakfast';
    if ($timeOfDay == 2) $timeName = 'Lunch';
    elseif ($timeOfDay == 3) $timeName = 'Dinner';
    elseif ($timeOfDay == 4) $timeName = 'Pre-Workout';
    elseif ($timeOfDay == 5) $timeName = 'Post-Workout';
    elseif ($timeOfDay == 6) $timeName = 'Snacks';
    
    if (!isset($meals[$timeName])) {
        $meals[$timeName] = [];
    }
    
    // Get food items for this meal
    $foodItems = [];
    $foodSql = "SELECT fi.Name, fi.Calories, fi.Protein_Grams, fi.Carbs_Grams, fi.Fats_Grams, fi.Serving_Size, mpi.Quantity_Servings
                FROM mealplanitem mpi
                JOIN fooditem fi ON mpi.Food_Item_ID = fi.Food_Item_ID
                WHERE mpi.Meal_ID = ? AND mpi.is_deleted = 0 AND fi.is_deleted = 0";
    $foodStmt = $conn->prepare($foodSql);
    if ($foodStmt) {
      $foodStmt->bind_param('i', $row['Meal_ID']);
      if ($foodStmt->execute()) {
        $foodResult = $foodStmt->get_result();
        while ($foodRow = $foodResult->fetch_assoc()) {
          $servings = (int)$foodRow['Quantity_Servings'];
          $servingSize = (int)$foodRow['Serving_Size'];
          // If Serving_Size is 0 or null, default to 100 (standard per 100g)
          if ($servingSize <= 0) $servingSize = 100;
          
          // Calculate: (value per serving_size) * quantity_servings
          // Since values are per serving_size, multiply by servings directly
          $foodItems[] = [
            'name' => $foodRow['Name'],
            'calories' => (int)($foodRow['Calories'] * $servings),
            'protein' => (int)($foodRow['Protein_Grams'] * $servings),
            'carbs' => (int)($foodRow['Carbs_Grams'] * $servings),
            'fats' => (int)($foodRow['Fats_Grams'] * $servings),
            'servings' => $servings
          ];
        }
      }
      $foodStmt->close();
    }
    
    $meals[$timeName][] = [
      'meal_id' => (int)$row['Meal_ID'],
      'name' => $row['Name'],
      'calories' => (int)($row['total_calories'] ?? 0),
      'protein' => (int)($row['total_protein'] ?? 0),
      'carbs' => (int)($row['total_carbs'] ?? 0),
      'fats' => (int)($row['total_fats'] ?? 0),
      'food_items' => $foodItems
    ];
}
$mealsStmt->close();

// Get today's consumed meals
// Note: The meallog table doesn't directly track individual meals consumed,
// so we'll return an empty array. The frontend uses localStorage to track consumed meals.
$today = date('Y-m-d');
$consumedSql = "SELECT ml.Meal_Log_ID, ml.Log_date, ml.Notes, ml.Meal_Plan_ID
                FROM meallog ml
                WHERE ml.Member_Id = ? AND ml.Log_date = ? AND ml.is_deleted = 0";

$consumedStmt = $conn->prepare($consumedSql);
$consumedMealIds = [];
if ($consumedStmt) {
    $consumedStmt->bind_param('is', $user_id, $today);
    if ($consumedStmt->execute()) {
        $consumedResult = $consumedStmt->get_result();
        // The meallog table doesn't track individual meals, so we return empty array
        // Frontend will use localStorage to track consumed meals
        while ($row = $consumedResult->fetch_assoc()) {
            // If we had a mealconsumption table, we'd join it here
            // For now, just acknowledge that meals were logged today
        }
    } else {
        error_log("Failed to execute consumed meals query: " . $consumedStmt->error);
    }
    $consumedStmt->close();
}

ob_clean();
echo json_encode([
    'success' => true,
    'meal_plan' => [
        'id' => (int)$mealPlan['Meal_Plan_ID'],
        'title' => $mealPlan['Title'],
        'description' => $mealPlan['Description'],
        'target_calories' => (int)$mealPlan['Total_daily_Calories'],
        'carbs' => (int)$mealPlan['Carbs_grams_per_day'],
        'protein' => (int)$mealPlan['Protein_grams_per_day'],
        'fats' => (int)$mealPlan['Fats_grams_per_day'],
        'nutritionist_name' => trim(($mealPlan['Nutritionist_First'] ?? '') . ' ' . ($mealPlan['Nutritionist_Last'] ?? ''))
    ],
    'meals' => $meals,
    'consumed_meal_ids' => array_unique($consumedMealIds)
]);
ob_end_flush();
?>

