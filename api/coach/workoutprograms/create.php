<?php
require_once __DIR__ . '/../../../DB.php';
require_once __DIR__ . '/../../helpers/auth.php';
header('Content-Type: application/json');

require_role(['coach','admin']);

$raw = file_get_contents('php://input');
$data = json_decode($raw, true) ?: $_POST;

if (empty($data['title']) || empty($data['member_id'])) { echo json_encode(['error'=>'title and member_id required']); exit; }

$coachId = current_user_id();
// Allow admin to pick any coach; allow coach to optionally set a coach_id to transfer ownership
if (isset($data['coach_id'])) {
    $coachId = (int)$data['coach_id'];
} else if (is_admin() && isset($data['coach_id'])) {
    $coachId = (int)$data['coach_id'];
}
// Note: we allow coaches to set coach_id but ownership checks are enforced on update/delete.

$memberId = (int)$data['member_id'];

// Start transaction for atomic operations
$conn->begin_transaction();

try {
    // Deactivate any existing active programs for this member
    $deactivateStmt = $conn->prepare("UPDATE workoutprogram SET Status = 'Inactive', Updated_at = NOW() WHERE Member_Id = ? AND Status = 'Active' AND Coach_ID = ?");
    $deactivateStmt->bind_param('ii', $memberId, $coachId);
    $deactivateStmt->execute();
    $deactivateStmt->close();
    
    // Hard delete any pending workout programs for this member
    // First, get all pending workout program IDs
    $getPendingStmt = $conn->prepare("SELECT Workout_ID FROM workoutprogram WHERE Member_Id = ? AND Status = 'Pending' AND Coach_ID = ?");
    $getPendingStmt->bind_param('ii', $memberId, $coachId);
    $getPendingStmt->execute();
    $pendingResult = $getPendingStmt->get_result();
    $pendingWorkoutIds = [];
    while ($row = $pendingResult->fetch_assoc()) {
        $pendingWorkoutIds[] = (int)$row['Workout_ID'];
    }
    $getPendingStmt->close();
    
    // Hard delete related workout exercises for pending programs
    if (!empty($pendingWorkoutIds)) {
        $placeholders = implode(',', array_fill(0, count($pendingWorkoutIds), '?'));
        $deleteExercisesStmt = $conn->prepare("DELETE FROM workoutexercise WHERE Workout_ID IN ($placeholders)");
        $types = str_repeat('i', count($pendingWorkoutIds));
        $deleteExercisesStmt->bind_param($types, ...$pendingWorkoutIds);
        $deleteExercisesStmt->execute();
        $deleteExercisesStmt->close();
        
        // Hard delete the pending workout programs
        $deletePendingStmt = $conn->prepare("DELETE FROM workoutprogram WHERE Workout_ID IN ($placeholders)");
        $deletePendingStmt->bind_param($types, ...$pendingWorkoutIds);
        $deletePendingStmt->execute();
        $deletePendingStmt->close();
    }
    
    // Commit transaction before creating new program
    $conn->commit();
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['error' => 'Failed to clean up existing programs: ' . $e->getMessage()]);
    exit;
}

$now = date('Y-m-d H:i:s');
$insData = [
  'Title' => $data['title'],
  'Description' => isset($data['description']) ? $data['description'] : '',
  'Goal' => isset($data['goal']) ? $data['goal'] : '',
  'Weeks_Duration' => isset($data['weeks']) ? (int)$data['weeks'] : 0,
  'Start_Date' => isset($data['start_date']) ? $data['start_date'] : null,
  'End_Date' => isset($data['end_date']) ? $data['end_date'] : null,
  'Status' => isset($data['status']) ? $data['status'] : 'Active',
  'Created_at' => $now,
  'Updated_at' => $now,
  'Member_Id' => $memberId,
  'Coach_ID' => (int)$coachId,
];

$id = insert('workoutprogram', $insData);
if (!$id) { echo json_encode(['error'=>'insert failed']); exit; }

echo json_encode(['inserted'=>true,'id'=>$id]);
