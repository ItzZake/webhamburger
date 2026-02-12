<?php
require_once __DIR__ . '/../../../DB.php';
require_once __DIR__ . '/../../helpers/auth.php';
header('Content-Type: application/json');

require_role(['coach','admin']);

$raw = file_get_contents('php://input');
$data = json_decode($raw, true) ?: $_POST;

$required = ['workout_id','exercise_id','sequence_order'];
foreach ($required as $r) if (empty($data[$r]) && $data[$r] !== '0') { http_response_code(400); echo json_encode(['error'=> $r . ' required']); exit; }

$workoutId = (int)$data['workout_id'];
// ownership: ensure coach owns the workoutprogram if coach
if (is_coach()) {
    $stmt = $conn->prepare("SELECT Coach_Id FROM workoutprogram WHERE Workout_ID = ?");
    $stmt->bind_param('i', $workoutId);
    if (!$stmt->execute()) { http_response_code(500); echo json_encode(['error'=>$stmt->error]); exit; }
    $r = $stmt->get_result()->fetch_assoc(); $stmt->close();
    if (!$r || (int)$r['Coach_Id'] !== current_user_id()) { http_response_code(403); echo json_encode(['error'=>'forbidden']); exit; }
}

$insData = [
    'Sequence_Order' => (int)$data['sequence_order'],
    'Day_Number' => isset($data['day_number']) ? $data['day_number'] : null,
    'Sets' => isset($data['sets']) ? (int)$data['sets'] : 0,
    'Reps' => isset($data['reps']) ? (int)$data['reps'] : 0,
    'Rest_Time' => isset($data['rest_time']) ? $data['rest_time'] : null,
    'Notes' => isset($data['notes']) ? $data['notes'] : null,
    'Exercise_ID' => (int)$data['exercise_id'],
    'Workout_ID' => $workoutId,
];

$id = insert('workoutexercise', $insData);
if (!$id) { http_response_code(500); echo json_encode(['error'=>'insert failed']); exit; }

echo json_encode(['inserted'=>true,'id'=>$id]);
?>