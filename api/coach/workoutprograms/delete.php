<?php
require_once __DIR__ . '/../../../DB.php';
require_once __DIR__ . '/../../helpers/auth.php';
header('Content-Type: application/json');

require_role(['coach','admin']);

$raw = file_get_contents('php://input');
$data = json_decode($raw, true) ?: $_POST;

if (empty($data['id'])) { echo json_encode(['error'=>'id required']); exit; }
$id = (int)$data['id'];

// Ownership check: coaches may only delete programs they own
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

$affected = delete('workoutprogram', ['Workout_ID'=>$id]);
if ($affected === 0) echo json_encode(['deleted'=>false]);
else echo json_encode(['deleted'=>true,'affected'=>$affected]);
