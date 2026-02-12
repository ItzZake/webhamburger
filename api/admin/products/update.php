<?php
require_once __DIR__ . '/../../../DB.php';
require_once __DIR__ . '/../../helpers/auth.php';
header('Content-Type: application/json');

require_role('admin');

$raw = file_get_contents('php://input');
$data = json_decode($raw, true) ?: $_POST;

if (empty($data['id'])) { echo json_encode(['error'=>'id required']); exit; }
$id = (int)$data['id'];

$fields = [];
foreach (['name','description','price','img','category_id','is_new','is_sale','is_active'] as $k) {
    if (isset($data[$k])) $fields[$k] = $data[$k];
}

if (empty($fields)) { echo json_encode(['error'=>'no fields to update']); exit; }

$sets = [];$types=[];$vals=[];
if (isset($fields['name'])) { $sets[]='Name=?'; $vals[]=$fields['name']; }
if (isset($fields['description'])) { $sets[]='Description=?'; $vals[]=$fields['description']; }
if (isset($fields['price'])) { $sets[]='Price=?'; $vals[]=(float)$fields['price']; }
if (isset($fields['img'])) { $sets[]='thumbnail_url=?'; $vals[]=$fields['img']; }
if (isset($fields['category_id'])) { $sets[]='Category_ID=?'; $vals[]=(int)$fields['category_id']; }
if (isset($fields['is_new'])) { $sets[]='is_new=?'; $vals[]=(int)$fields['is_new']; }
if (isset($fields['is_sale'])) { $sets[]='is_sale=?'; $vals[]=(int)$fields['is_sale']; }
if (isset($fields['is_active'])) { $sets[]='is_active=?'; $vals[]=(int)$fields['is_active']; }

$sql = "UPDATE product SET " . implode(', ', $sets) . " WHERE Product_ID = ?";
$vals[] = $id;
$types = '';
foreach ($vals as $v) { $types .= (is_int($v)?'i':(is_float($v)?'d':'s')); }

$stmt = $conn->prepare($sql);
if (!$stmt) { echo json_encode(['error'=>$conn->error]); exit; }
$stmt->bind_param($types, ...$vals);
if (!$stmt->execute()) { echo json_encode(['error'=>$stmt->error]); exit; }

echo json_encode(['updated'=>true]);

?>
