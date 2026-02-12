<?php
require_once __DIR__ . '/../../../DB.php';
require_once __DIR__ . '/../../helpers/auth.php';
header('Content-Type: application/json');

require_role(['doctor','nutritionist','admin']);

$mealPlanId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$mealPlanId) {
    echo json_encode(['success' => false, 'error' => 'Meal plan ID is required']);
    exit;
}

$nutritionistId = current_user_id();

// Get meal plan details
$sql = "SELECT mp.*, 
               u.First_Name AS Member_First, u.Last_Name AS Member_Last,
               nu.First_Name AS Nutritionist_First, nu.Last_Name AS Nutritionist_Last
        FROM mealplan mp
        LEFT JOIN memberprofile m ON mp.Member_Id = m.Member_Id
        LEFT JOIN userprofile u ON m.Member_Id = u.User_ID
        LEFT JOIN nutritionistprofile np ON mp.Nutritionist_ID = np.Nutritionist_ID
        LEFT JOIN userprofile nu ON np.Nutritionist_ID = nu.User_ID
        WHERE mp.Meal_Plan_ID = ? AND mp.is_deleted = 0";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $conn->error]);
    exit;
}

$stmt->bind_param('i', $mealPlanId);
$stmt->execute();
$result = $stmt->get_result();
$mealPlan = $result->fetch_assoc();
$stmt->close();

if (!$mealPlan) {
    echo json_encode(['success' => false, 'error' => 'Meal plan not found']);
    exit;
}

// If nutritionist/doctor (not admin), verify they own this meal plan
$userRole = current_user_role();
if (($userRole === 'nutritionist' || $userRole === 'doctor') && !is_admin()) {
    if ($mealPlan['Nutritionist_ID'] != $nutritionistId) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'You do not have permission to view this meal plan']);
        exit;
    }
}

// Get all meals for this meal plan, grouped by time of day
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
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $conn->error]);
    exit;
}

$mealsStmt->bind_param('i', $mealPlanId);
$mealsStmt->execute();
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
    $foodSql = "SELECT fi.Name, fi.Calories, fi.Protein_Grams, fi.Carbs_Grams, fi.Fats_Grams, 
                       fi.Serving_Size, mpi.Quantity_Servings
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
                $foodItems[] = [
                    'name' => $foodRow['Name'],
                    'calories' => (int)($foodRow['Calories'] * $servings),
                    'protein' => (int)($foodRow['Protein_Grams'] * $servings),
                    'carbs' => (int)($foodRow['Carbs_Grams'] * $servings),
                    'fats' => (int)($foodRow['Fats_Grams'] * $servings),
                    'servings' => $servings,
                    'serving_size' => (int)$foodRow['Serving_Size']
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
        'status' => $mealPlan['Status'],
        'start_date' => $mealPlan['Start_Date'],
        'end_date' => $mealPlan['End_Date'],
        'member_name' => trim(($mealPlan['Member_First'] ?? '') . ' ' . ($mealPlan['Member_Last'] ?? '')),
        'nutritionist_name' => trim(($mealPlan['Nutritionist_First'] ?? '') . ' ' . ($mealPlan['Nutritionist_Last'] ?? ''))
    ],
    'meals' => $meals
]);
?>

