<?php
require_once __DIR__ . '/../../../DB.php';
require_once __DIR__ . '/../../helpers/auth.php';
header('Content-Type: application/json');

require_role('admin');

$sql = "SELECT Product_ID as id, Name as name, Description as description, Price as price, thumbnail_url as img, Category_ID as category_id, rating, reviews, is_new, is_sale, is_active FROM product ORDER BY Product_ID DESC";
$stmt = $conn->prepare($sql);
if (!$stmt) { echo json_encode(['error' => $conn->error]); exit; }
$stmt->execute();
$res = $stmt->get_result();
$rows = [];
while ($r = $res->fetch_assoc()) $rows[] = $r;

echo json_encode($rows);

?>
