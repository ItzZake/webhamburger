<?php
require_once __DIR__ . '/../../../DB.php';
require_once __DIR__ . '/../../helpers/auth.php';
header('Content-Type: application/json');

require_role(['doctor','nutritionist','admin']);

$raw = file_get_contents('php://input');
$data = json_decode($raw, true) ?: $_POST;
if (empty($data['id'])) { echo json_encode(['error'=>'id required']); exit; }
$id = (int)$data['id'];

$update = [];
$allowed = ['Title','Description','Start_Date','End_Date','Nutritionist_ID','Member_Id'];
foreach ($allowed as $k) {
  $field = strtolower($k);
  if (isset($data[$field])) $update[$k] = $data[$field];
}
if (empty($update)) { echo json_encode(['error'=>'no fields to update']); exit; }
$update['Updated_at'] = date('Y-m-d H:i:s');

$affected = update('mealplan', $update, ['Meal_Plan_ID'=>$id]);
if ($affected === 0) echo json_encode(['updated'=>false]);
else echo json_encode(['updated'=>true,'affected'=>$affected]);
