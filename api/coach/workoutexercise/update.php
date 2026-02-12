<?php
require_once __DIR__ . '/../../../DB.php';
require_once __DIR__ . '/../../helpers/auth.php';
header('Content-Type: application/json');

require_role(['coach','admin']);

$raw = file_get_contents('php://input');
$data = json_decode($raw, true) ?: $_POST;
if (empty($data['id'])) { http_response_code(400); echo json_encode(['error'=>'id required']); exit; }
$id = (int)$data['id'];

// fetch item and check ownership via workoutprogram
$stmt = $conn->prepare("SELECT Workout_ID FROM workoutexercise WHERE Workout_Exercise_ID = ?");
$stmt->bind_param('i', $id);
if (!$stmt->execute()) { http_response_code(500); echo json_encode(['error'=>$stmt->error]); exit; }
$r = $stmt->get_result()->fetch_assoc(); $stmt->close();
if (!$r) { http_response_code(404); echo json_encode(['error'=>'not found']); exit; }
$workoutId = (int)$r['Workout_ID'];
if (is_coach()) {
    $stmt = $conn->prepare("SELECT Coach_Id FROM workoutprogram WHERE Workout_ID = ?");
    $stmt->bind_param('i', $workoutId);
    if (!$stmt->execute()) { http_response_code(500); echo json_encode(['error'=>$stmt->error]); exit; }
    $rr = $stmt->get_result()->fetch_assoc(); $stmt->close();
    if (!$rr || (int)$rr['Coach_Id'] !== current_user_id()) { http_response_code(403); echo json_encode(['error'=>'forbidden']); exit; }
}

$fields = [];
foreach (['sequence_order','day_number','sets','reps','rest_time','notes','exercise_id'] as $k) if (isset($data[$k])) $fields[$k]=$data[$k];
if (empty($fields)) { http_response_code(400); echo json_encode(['error'=>'no fields']); exit; }

$sets=[];$vals=[];$types='';
if (isset($fields['sequence_order'])) { $sets[]='Sequence_Order=?'; $vals[]=(int)$fields['sequence_order']; }
if (isset($fields['day_number'])) { $sets[]='Day_Number=?'; $vals[]=$fields['day_number']; }
if (isset($fields['sets'])) { $sets[]='Sets=?'; $vals[]=(int)$fields['sets']; }
if (isset($fields['reps'])) { $sets[]='Reps=?'; $vals[]=(int)$fields['reps']; }
if (isset($fields['rest_time'])) { $sets[]='Rest_Time=?'; $vals[]=$fields['rest_time']; }
if (isset($fields['notes'])) { $sets[]='Notes=?'; $vals[]=$fields['notes']; }
if (isset($fields['exercise_id'])) { $sets[]='Exercise_ID=?'; $vals[]=(int)$fields['exercise_id']; }

$sql = "UPDATE workoutexercise SET " . implode(', ', $sets) . " WHERE Workout_Exercise_ID = ?";
$vals[] = $id;
foreach ($vals as $v) { $types .= (is_int($v)?'i':(is_float($v)?'d':'s')); }
$stmt = $conn->prepare($sql); if (!$stmt) { http_response_code(500); echo json_encode(['error'=>$conn->error]); exit; }
$stmt->bind_param($types, ...$vals);
if (!$stmt->execute()) { http_response_code(500); echo json_encode(['error'=>$stmt->error]); exit; }

echo json_encode(['updated'=>true]);
?>