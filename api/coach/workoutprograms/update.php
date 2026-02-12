<?php
require_once __DIR__ . '/../../../DB.php';
require_once __DIR__ . '/../../helpers/auth.php';
header('Content-Type: application/json');

require_role(['coach','admin']);

$raw = file_get_contents('php://input');
$data = json_decode($raw, true) ?: $_POST;

if (empty($data['id'])) { echo json_encode(['error'=>'id required']); exit; }
$id = (int)$data['id'];

// Ownership check: coaches may only update programs they own
if (is_coach()) {
  $q = $conn->prepare("SELECT Coach_ID FROM workoutprogram WHERE Workout_ID = ?");
  $q->bind_param('i',$id);
  $q->execute();
  $r = $q->get_result()->fetch_assoc();
  $q->close();
  if (!$r || (int)$r['Coach_ID'] !== (int)current_user_id()) {
    http_response_code(403);
    echo json_encode(['error'=>'forbidden']); exit;
  }
}

$update = [];
$allowed = ['Title','Description','Goal','Weeks_Duration','Start_Date','End_Date','Status','Coach_ID','Member_Id'];
foreach ($allowed as $k) {
  $field = strtolower($k);
  if (isset($data[$field])) $update[$k] = $data[$field];
}
if (empty($update)) { echo json_encode(['error'=>'no fields to update']); exit; }
$update['Updated_at'] = date('Y-m-d H:i:s');

$affected = update('workoutprogram', $update, ['Workout_ID'=>$id]);
if ($affected === 0) echo json_encode(['updated'=>false]);
else echo json_encode(['updated'=>true,'affected'=>$affected]);
