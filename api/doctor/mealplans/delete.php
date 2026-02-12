<?php
require_once __DIR__ . '/../../../DB.php';
require_once __DIR__ . '/../../helpers/auth.php';
header('Content-Type: application/json');

require_role(['doctor','nutritionist','admin']);

$raw = file_get_contents('php://input');
$data = json_decode($raw, true) ?: $_POST;
if (empty($data['id'])) { echo json_encode(['error'=>'id required']); exit; }
$id = (int)$data['id'];

$affected = delete('mealplan', ['Meal_Plan_ID'=>$id]);
if ($affected === 0) echo json_encode(['deleted'=>false]);
else echo json_encode(['deleted'=>true,'affected'=>$affected]);
