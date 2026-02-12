<?php
require_once __DIR__ . '/../../../DB.php';
require_once __DIR__ . '/../../helpers/auth.php';
header('Content-Type: application/json');

require_role(['doctor','nutritionist','admin']);

$raw = file_get_contents('php://input');
$data = json_decode($raw, true) ?: $_POST;
if (empty($data['id'])) { echo json_encode(['error'=>'id required']); exit; }
$id = (int)$data['id'];

// If doctor role, ensure they own the meal
if (!is_admin()) {
    $stmt = $conn->prepare("SELECT Doctor_Id FROM meal WHERE Meal_ID = ?");
    $stmt->bind_param('i', $id); $stmt->execute(); $r = $stmt->get_result()->fetch_assoc(); $stmt->close();
    if (!$r || (int)$r['Doctor_Id'] !== current_user_id()) { echo json_encode(['error'=>'forbidden']); exit; }
}

$fields = [];
foreach (['name','description','calories','protein','carbs','fat'] as $k) if (isset($data[$k])) $fields[$k]=$data[$k];
if (empty($fields)) { echo json_encode(['error'=>'no fields']); exit; }

$sets=[];$vals=[];$types='';
if (isset($fields['name'])) { $sets[]='Name=?'; $vals[]=$fields['name']; }
if (isset($fields['description'])) { $sets[]='Description=?'; $vals[]=$fields['description']; }
if (isset($fields['calories'])) { $sets[]='Calories=?'; $vals[]=(int)$fields['calories']; }
if (isset($fields['protein'])) { $sets[]='Protein=?'; $vals[]=(float)$fields['protein']; }
if (isset($fields['carbs'])) { $sets[]='Carbs=?'; $vals[]=(float)$fields['carbs']; }
if (isset($fields['fat'])) { $sets[]='Fat=?'; $vals[]=(float)$fields['fat']; }

$sql = "UPDATE meal SET " . implode(', ', $sets) . " WHERE Meal_ID = ?";
$vals[] = $id;
foreach ($vals as $v) { $types .= (is_int($v)?'i':(is_float($v)?'d':'s')); }
$stmt = $conn->prepare($sql); if (!$stmt) { echo json_encode(['error'=>$conn->error]); exit; }
$stmt->bind_param($types, ...$vals);
if (!$stmt->execute()) { echo json_encode(['error'=>$stmt->error]); exit; }

echo json_encode(['updated'=>true]);

?>
