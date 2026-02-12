<?php
require_once __DIR__ . '/../../../DB.php';
require_once __DIR__ . '/../../helpers/auth.php';
header('Content-Type: application/json');

require_role(['doctor','nutritionist','admin']);

$raw = file_get_contents('php://input');
$data = json_decode($raw, true) ?: $_POST;

if (empty($data['name'])) { echo json_encode(['error'=>'name required']); exit; }

$doctorId = current_user_id();
if (is_admin() && isset($data['doctor_id'])) $doctorId = (int)$data['doctor_id'];

$name = $data['name'];
$desc = isset($data['description']) ? $data['description'] : '';
$cal = isset($data['calories']) ? (int)$data['calories'] : 0;
$protein = isset($data['protein']) ? floatval($data['protein']) : 0;
$carbs = isset($data['carbs']) ? floatval($data['carbs']) : 0;
$fat = isset($data['fat']) ? floatval($data['fat']) : 0;

$stmt = $conn->prepare("INSERT INTO meal (Name, Description, Calories, Protein, Carbs, Fat, Doctor_Id) VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param('ssidddi', $name, $desc, $cal, $protein, $carbs, $fat, $doctorId);
if (!$stmt->execute()) { echo json_encode(['error'=>$stmt->error]); exit; }

echo json_encode(['inserted'=>true, 'id' => $conn->insert_id]);

?>
