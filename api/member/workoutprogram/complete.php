<?php
/**
 * Complete current workout program and create a new pending one for the coach
 * POST: No parameters needed (uses session user_id)
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

require_role(['member', 'admin']);

$user_id = current_user_id();

if (!$user_id) {
    ob_clean();
    echo json_encode(['success' => false, 'error' => 'User not authenticated']);
    exit;
}

// Get current active workout program
$getProgramSql = "SELECT Workout_ID, Coach_ID, Title, Description, Goal, Weeks_Duration 
                  FROM workoutprogram 
                  WHERE Member_Id = ? AND Status = 'Active' AND is_deleted = 0 
                  LIMIT 1";
$getStmt = $conn->prepare($getProgramSql);
if (!$getStmt) {
    ob_clean();
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $conn->error]);
    exit;
}

$getStmt->bind_param('i', $user_id);
$getStmt->execute();
$result = $getStmt->get_result();
$currentProgram = $result->fetch_assoc();
$getStmt->close();

if (!$currentProgram) {
    ob_clean();
    echo json_encode(['success' => false, 'error' => 'No active workout program found']);
    exit;
}

$workoutId = $currentProgram['Workout_ID'];
$coachId = $currentProgram['Coach_ID'];

// Start transaction
$conn->begin_transaction();

try {
    // Mark current workout program as Inactive (completed)
    $now = date('Y-m-d H:i:s');
    $updateSql = "UPDATE workoutprogram 
                  SET Status = 'Completed', Updated_at = ? 
                  WHERE Workout_ID = ?";
    $updateStmt = $conn->prepare($updateSql);
    if (!$updateStmt) {
        throw new Exception('Database error: ' . $conn->error);
    }
    
    $updateStmt->bind_param('si', $now, $workoutId);
    if (!$updateStmt->execute()) {
        throw new Exception('Failed to complete workout program: ' . $updateStmt->error);
    }
    $updateStmt->close();
    
    // Create a new pending workout program for the coach to create a new plan
    $insertSql = "INSERT INTO workoutprogram 
                  (Title, Description, Goal, Weeks_Duration, Start_Date, End_Date, Status, Created_at, Updated_at, Member_Id, Coach_ID) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $title = "Pending Assignment";
    $description = "Member has completed previous plan. Waiting for coach to create new workout plan.";
    $goal = $currentProgram['Goal'] ?? "General Fitness";
    $weeks = 4; // Default weeks
    $startDate = date('Y-m-d');
    $endDate = date('Y-m-d', strtotime("+{$weeks} weeks"));
    $status = "Pending";
    
    $insertStmt = $conn->prepare($insertSql);
    if (!$insertStmt) {
        throw new Exception('Database error: ' . $conn->error);
    }
    
    $insertStmt->bind_param('sssisssssii', $title, $description, $goal, $weeks, $startDate, $endDate, $status, $now, $now, $user_id, $coachId);
    
    if (!$insertStmt->execute()) {
        throw new Exception('Failed to create pending workout program: ' . $insertStmt->error);
    }
    
    $newWorkoutId = $conn->insert_id;
    $insertStmt->close();
    
    // Commit transaction
    $conn->commit();
    
    ob_clean();
    echo json_encode([
        'success' => true,
        'message' => 'Workout program completed successfully. Your coach will create a new plan for you.',
        'completed_workout_id' => $workoutId,
        'new_workout_id' => $newWorkoutId
    ]);
    ob_end_flush();
    
} catch (Exception $e) {
    // Rollback on error
    $conn->rollback();
    ob_clean();
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
    ob_end_flush();
}
?>

