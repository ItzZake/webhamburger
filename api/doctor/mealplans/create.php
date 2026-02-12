<?php
require_once __DIR__ . '/../../../DB.php';
require_once __DIR__ . '/../../helpers/auth.php';
header('Content-Type: application/json');

require_role(['doctor','nutritionist','admin']);

$raw = file_get_contents('php://input');
$data = json_decode($raw, true) ?: $_POST;

if (empty($data['title']) || empty($data['member_id'])) { echo json_encode(['error'=>'title and member_id required']); exit; }

$now = date('Y-m-d H:i:s');
// Ensure we have a nutritionist/creator id from the session when required by DB
$nid = isset($data['nutritionist_id']) ? (int)$data['nutritionist_id'] : current_user_id();
if ($nid === null) {
  http_response_code(400);
  echo json_encode(['error' => 'missing user id in session']);
  exit;
}

// Verify nutritionist exists to avoid foreign-key DB errors
$nid = (int)$nid;
if ($nid <= 0) {
  http_response_code(400);
  echo json_encode(['error' => 'invalid user id']);
  exit;
}
$nprof = select('nutritionistprofile', ['Nutritionist_ID'], ['Nutritionist_ID' => $nid]);
if (empty($nprof)) {
  http_response_code(400);
  echo json_encode(['error' => 'nutritionist profile not found for id ' . $nid]);
  exit;
}

$insData = [
  'Title' => $data['title'],
  'Description' => isset($data['description']) ? $data['description'] : '',
  'Nutritionist_ID' => (int)$nid,
  'Member_Id' => (int)$data['member_id'],
  'Start_Date' => isset($data['start_date']) ? $data['start_date'] : null,
  'End_Date' => isset($data['end_date']) ? $data['end_date'] : null,
  'Created_at' => $now,
  'Updated_at' => $now
];

$id = insert('mealplan', $insData);
if (!$id) { echo json_encode(['error'=>'insert failed']); exit; }

echo json_encode(['inserted'=>true,'id'=>$id]);
