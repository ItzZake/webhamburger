<?php
require_once __DIR__ . '/../../DB.php';
header('Content-Type: application/json');

$sql = "SELECT p.Product_ID as id, p.Name as name, p.Description as description, p.Price as price_num, p.thumbnail_url as img, COALESCE(pc.Name,'') as category, p.rating, p.reviews, p.is_new, p.is_sale FROM product p LEFT JOIN productcategory pc ON p.Category_ID = pc.Category_ID WHERE p.is_active = 1";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(['error' => $conn->error]);
    exit;
}
$stmt->execute();
$res = $stmt->get_result();
$rows = [];
while ($r = $res->fetch_assoc()) {
    $rows[] = [
        'id' => (int)$r['id'],
        'name' => $r['name'],
        'category' => $r['category'],
        'price' => '$' . (float)$r['price_num'],
        'desc' => $r['description'],
        'img' => $r['img'],
        'rating' => isset($r['rating']) ? (float)$r['rating'] : 0,
        'reviews' => isset($r['reviews']) ? (int)$r['reviews'] : 0,
        'is_new' => (bool)$r['is_new'],
        'is_sale' => (bool)$r['is_sale']
    ];
}

echo json_encode($rows);
?>