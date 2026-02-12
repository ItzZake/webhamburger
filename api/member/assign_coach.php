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

if (empty($data['coach_id'])) {
    ob_clean();
    echo json_encode(['success' => false, 'error' => 'Coach ID is required']);
    exit;
}

$coach_id = (int)$data['coach_id'];

// Verify coach exists and is accepting new clients
$coachCheck = $conn->prepare("SELECT cp.Coach_ID, cp.Is_Accepting_new, cp.Max_Clients 
                               FROM coachprofile cp 
                               WHERE cp.Coach_ID = ? AND cp.is_deleted = 0");
$coachCheck->bind_param('i', $coach_id);
$coachCheck->execute();
$coachResult = $coachCheck->get_result();
$coach = $coachResult->fetch_assoc();
$coachCheck->close();

if (!$coach) {
    ob_clean();
    echo json_encode(['success' => false, 'error' => 'Coach not found']);
    exit;
}

// Check if coach is accepting new clients
if ($coach['Is_Accepting_new'] == 0) {
    ob_clean();
    echo json_encode(['success' => false, 'error' => 'Coach is not accepting new clients']);
    exit;
}

// Check if user already has an active workout program with any coach
$existingCheck = $conn->prepare("SELECT Workout_ID, Coach_ID FROM workoutprogram 
                                  WHERE Member_Id = ? AND Status = 'Active' AND is_deleted = 0 
                                  LIMIT 1");
$existingCheck->bind_param('i', $user_id);
$existingCheck->execute();
$existingResult = $existingCheck->get_result();
$existing = $existingResult->fetch_assoc();
$existingCheck->close();

if ($existing) {
    // User already has a coach assigned
    if ($existing['Coach_ID'] == $coach_id) {
        ob_clean();
        echo json_encode(['success' => true, 'message' => 'Coach already assigned', 'workout_id' => $existing['Workout_ID']]);
        exit;
    } else {
        ob_clean();
        echo json_encode(['success' => false, 'error' => 'You already have a coach assigned. Please contact support to change coaches.']);
        exit;
    }
}

// Create a pending workout program (coach will assign exercises later)
$now = date('Y-m-d H:i:s');
$insertSql = "INSERT INTO workoutprogram 
              (Title, Description, Goal, Weeks_Duration, Start_Date, End_Date, Status, Created_at, Updated_at, Member_Id, Coach_ID) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$title = "Pending Assignment";
$description = "Waiting for coach to assign workout plan";
$goal = "General Fitness";
$weeks = 4;
$startDate = date('Y-m-d');
$endDate = date('Y-m-d', strtotime("+{$weeks} weeks"));
$status = "Pending"; // Status will be "Active" once coach assigns exercises

$stmt = $conn->prepare($insertSql);
if (!$stmt) {
    ob_clean();
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $conn->error]);
    exit;
}

$stmt->bind_param('sssisssssii', $title, $description, $goal, $weeks, $startDate, $endDate, $status, $now, $now, $user_id, $coach_id);

if (!$stmt->execute()) {
    ob_clean();
    echo json_encode(['success' => false, 'error' => 'Failed to assign coach: ' . $stmt->error]);
    $stmt->close();
    exit;
}

$workout_id = $stmt->insert_id;
$stmt->close();

ob_clean();
echo json_encode([
    'success' => true,
    'message' => 'Coach assigned successfully',
    'workout_id' => $workout_id,
    'coach_id' => $coach_id
]);
ob_end_flush();
?>

