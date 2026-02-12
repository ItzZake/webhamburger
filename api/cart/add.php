<?php
require_once __DIR__ . '/../../DB.php';
header('Content-Type: application/json');
$input = json_decode(file_get_contents('php://input'), true);
if (!$input || !isset($input['items']) || !is_array($input['items'])) {
    echo json_encode(['error' => 'Invalid input']);
    exit;
}
$memberId = isset($input['member_id']) ? $input['member_id'] : null;
$now = date('Y-m-d H:i:s');

// Insert cart
$stmt = $conn->prepare("INSERT INTO cart (Status, Created_at, Updated_at, Member_Id, Order_ID) VALUES (?, ?, ?, ?, NULL)");
$status = 0;
// Order_ID is NULL until checkout - will be set when order is created
$stmt->bind_param('issi', $status, $now, $now, $memberId);
if (!$stmt->execute()) {
    echo json_encode(['error' => 'Insert cart failed: ' . $stmt->error]);
    exit;
}
$cartId = $conn->insert_id;
$stmt->close();

$inserted = 0;
foreach ($input['items'] as $it) {
    $productId = isset($it['product_id']) ? (int)$it['product_id'] : 0;
    $qty = isset($it['quantity']) ? (int)$it['quantity'] : 1;
    $unit = isset($it['unit_price']) ? floatval($it['unit_price']) : 0.0;
    $subtotal = $qty * $unit;

    $stmt = $conn->prepare("INSERT INTO cartitem (Quantity, Unit_price_at_add_time, Subtotal_amount, created_at, Updated_at, Cart_ID, Product_ID) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('iddssii', $qty, $unit, $subtotal, $now, $now, $cartId, $productId);
    if ($stmt->execute()) {
        $inserted++;
    }
    $stmt->close();
}

echo json_encode(['cart_id' => $cartId, 'items_inserted' => $inserted]);
?>