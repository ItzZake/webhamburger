<?php
ob_start();
ini_set('display_errors', 0);
error_reporting(E_ALL);

require_once __DIR__ . '/../../DB.php';
require_once __DIR__ . '/../helpers/auth.php';
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

// Check if user is a member
$memberCheck = $conn->prepare("SELECT Member_Id FROM memberprofile WHERE Member_Id = ? AND is_deleted = 0");
$memberCheck->bind_param('i', $user_id);
$memberCheck->execute();
$memberResult = $memberCheck->get_result();
if (!$memberResult->num_rows) {
    $memberCheck->close();
    ob_clean();
    echo json_encode(['success' => false, 'error' => 'User is not a member']);
    exit;
}
$memberCheck->close();

$raw = file_get_contents('php://input');
$data = json_decode($raw, true) ?: $_POST;

if (empty($data['nutritionist_id'])) {
    ob_clean();
    echo json_encode(['success' => false, 'error' => 'Nutritionist ID is required']);
    exit;
}

$nutritionist_id = (int)$data['nutritionist_id'];

// Verify nutritionist exists and is accepting new clients
$nutritionistCheck = $conn->prepare("SELECT np.Nutritionist_ID, np.Is_accepting_new 
                                     FROM nutritionistprofile np 
                                     WHERE np.Nutritionist_ID = ? AND np.is_deleted = 0");
if (!$nutritionistCheck) {
    ob_clean();
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $conn->error]);
    exit;
}
$nutritionistCheck->bind_param('i', $nutritionist_id);
if (!$nutritionistCheck->execute()) {
    ob_clean();
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $nutritionistCheck->error]);
    $nutritionistCheck->close();
    exit;
}
$nutritionistResult = $nutritionistCheck->get_result();
$nutritionist = $nutritionistResult->fetch_assoc();
$nutritionistCheck->close();

if (!$nutritionist) {
    ob_clean();
    echo json_encode(['success' => false, 'error' => 'Nutritionist not found']);
    exit;
}

// Check if nutritionist is accepting new clients
if ($nutritionist['Is_accepting_new'] == 0) {
    ob_clean();
    echo json_encode(['success' => false, 'error' => 'Nutritionist is not accepting new clients']);
    exit;
}

// Check if user already has a nutritionist assigned (check meal plans)
$existingCheck = $conn->prepare("SELECT Meal_Plan_ID, Nutritionist_ID FROM mealplan 
                                  WHERE Member_Id = ? AND is_deleted = 0 
                                  LIMIT 1");
if (!$existingCheck) {
    ob_clean();
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $conn->error]);
    exit;
}
$existingCheck->bind_param('i', $user_id);
if (!$existingCheck->execute()) {
    ob_clean();
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $existingCheck->error]);
    $existingCheck->close();
    exit;
}
$existingResult = $existingCheck->get_result();
$existing = $existingResult->fetch_assoc();
$existingCheck->close();

if ($existing) {
    // User already has a nutritionist assigned
    if ($existing['Nutritionist_ID'] == $nutritionist_id) {
        ob_clean();
        echo json_encode(['success' => true, 'message' => 'Nutritionist already assigned', 'mealplan_id' => $existing['Meal_Plan_ID']]);
        exit;
    } else {
        ob_clean();
        echo json_encode(['success' => false, 'error' => 'You already have a nutritionist assigned. Please contact support to change nutritionists.']);
        exit;
    }
}

// Create a pending meal plan (nutritionist will assign meals later)
$now = date('Y-m-d H:i:s');
$insertSql = "INSERT INTO mealplan 
              (Title, Description, Start_Date, End_Date, Status, Created_at, Updated_at, Member_Id, Nutritionist_ID, Total_daily_Calories, Carbs_grams_per_day, Protein_grams_per_day, Fats_grams_per_day) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$title = "Pending Assignment";
$description = "Waiting for nutritionist to assign meal plan";
$startDate = date('Y-m-d');
$endDate = date('Y-m-d', strtotime("+4 weeks")); // Default 4 weeks
$status = "Pending"; // Status will be "Active" once nutritionist assigns meals
$targetCalories = 2000; // Default values
$carbs = 250;
$protein = 150;
$fats = 70;

$stmt = $conn->prepare($insertSql);
if (!$stmt) {
    ob_clean();
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $conn->error]);
    exit;
}

// 13 parameters: 7 strings (title, description, startDate, endDate, status, now, now) + 6 integers (user_id, nutritionist_id, targetCalories, carbs, protein, fats)
$stmt->bind_param('sssssssiiiiii', $title, $description, $startDate, $endDate, $status, $now, $now, $user_id, $nutritionist_id, $targetCalories, $carbs, $protein, $fats);

if (!$stmt->execute()) {
    ob_clean();
    echo json_encode(['success' => false, 'error' => 'Failed to create pending meal plan: ' . $stmt->error]);
    exit;
}

$mealPlanId = $conn->insert_id;
$stmt->close();

ob_clean();
echo json_encode([
    'success' => true,
    'message' => 'Nutritionist assigned and pending meal plan created.',
    'nutritionist_id' => $nutritionist_id,
    'meal_plan_id' => $mealPlanId
]);
ob_end_flush();
?>

