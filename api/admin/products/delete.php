<?php
require_once __DIR__ . '/../../../DB.php';
require_once __DIR__ . '/../../helpers/auth.php';
header('Content-Type: application/json');

require_role('admin');

$raw = file_get_contents('php://input');
$data = json_decode($raw, true) ?: $_POST;

if (empty($data['id'])) { echo json_encode(['error'=>'id required']); exit; }
$id = (int)$data['id'];

// Soft-delete by marking is_active = 0
$stmt = $conn->prepare("UPDATE product SET is_active = 0 WHERE Product_ID = ?");
$stmt->bind_param('i', $id);
if (!$stmt->execute()) { echo json_encode(['error'=>$stmt->error]); exit; }

echo json_encode(['deleted'=>true]);

?>
