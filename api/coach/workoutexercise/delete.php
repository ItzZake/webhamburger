<?php
require_once __DIR__ . '/../../../DB.php';
require_once __DIR__ . '/../../helpers/auth.php';
header('Content-Type: application/json');

require_role(['coach','admin']);

$raw = file_get_contents('php://input');
$data = json_decode($raw, true) ?: $_POST;
if (empty($data['id'])) { http_response_code(400); echo json_encode(['error'=>'id required']); exit; }
$id = (int)$data['id'];

// fetch and check ownership by workoutprogram
$stmt = $conn->prepare("SELECT Workout_ID, is_deleted FROM workoutexercise WHERE Workout_Exercise_ID = ?");
$stmt->bind_param('i', $id);
if (!$stmt->execute()) { http_response_code(500); echo json_encode(['error'=>$stmt->error]); exit; }
$r = $stmt->get_result()->fetch_assoc(); $stmt->close();
if (!$r) { http_response_code(404); echo json_encode(['error'=>'not found']); exit; }
if ((int)$r['is_deleted'] === 1) { echo json_encode(['deleted'=>true,'note'=>'already deleted']); exit; }
$workoutId = (int)$r['Workout_ID'];
if (is_coach()) {
    $stmt = $conn->prepare("SELECT Coach_Id FROM workoutprogram WHERE Workout_ID = ?");
    $stmt->bind_param('i', $workoutId);
    if (!$stmt->execute()) { http_response_code(500); echo json_encode(['error'=>$stmt->error]); exit; }
    $rr = $stmt->get_result()->fetch_assoc(); $stmt->close();
    if (!$rr || (int)$rr['Coach_Id'] !== current_user_id()) { http_response_code(403); echo json_encode(['error'=>'forbidden']); exit; }
}

$stmt = $conn->prepare("UPDATE workoutexercise SET is_deleted = 1 WHERE Workout_Exercise_ID = ?");
$stmt->bind_param('i', $id);
if (!$stmt->execute()) { http_response_code(500); echo json_encode(['error'=>$stmt->error]); exit; }

echo json_encode(['deleted'=>true]);
?>