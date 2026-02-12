<?php
require_once __DIR__ . '/../../../DB.php';
require_once __DIR__ . '/../../helpers/auth.php';
header('Content-Type: application/json');

require_role('admin');

$raw = file_get_contents('php://input');
$data = json_decode($raw, true) ?: $_POST;

if (empty($data['name']) || !isset($data['price'])) {
    echo json_encode(['error' => 'name and price required']);
    exit;
}

$name = $data['name'];
$description = isset($data['description']) ? $data['description'] : '';
$price = floatval($data['price']);
$img = isset($data['img']) ? $data['img'] : '';
$categoryId = isset($data['category_id']) ? (int)$data['category_id'] : 0; // default to 0 when not provided
$is_new = isset($data['is_new']) ? (int)$data['is_new'] : 0;
$is_sale = isset($data['is_sale']) ? (int)$data['is_sale'] : 0;
$is_active = isset($data['is_active']) ? (int)$data['is_active'] : 1;

$stmt = $conn->prepare("INSERT INTO product (Name, Description, Price, thumbnail_url, Category_ID, is_new, is_sale, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param('ssdsiiii', $name, $description, $price, $img, $categoryId, $is_new, $is_sale, $is_active);
try {
    if (!$stmt->execute()) {
        echo json_encode(['error' => $stmt->error]);
        exit;
    }
} catch (mysqli_sql_exception $ex) {
    echo json_encode(['error' => $ex->getMessage()]);
    exit;
}
$id = $conn->insert_id;
$stmt->close();

echo json_encode(['inserted' => true, 'id' => (int)$id]);

?>
