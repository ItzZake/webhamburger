<?php
require_once __DIR__ . '/../../DB.php';
header('Content-Type: application/json');

$rawInput = file_get_contents('php://input');
$input = json_decode($rawInput, true);
if (!$input) {
    $clean = str_replace('\\', '', $rawInput);
    $input = json_decode($clean, true);
    if (!$input) {
        $clean2 = preg_replace('/"\s+/', '', $clean);
        $clean2 = str_replace('"', '', $clean2);
        $fixed = preg_replace('/([\{,\s])(\w+)\s*:/', '$1"$2":', $clean2);
        $input = json_decode($fixed, true);
    }
}
if (!$input && !empty($_POST)) $input = $_POST;
$cartId = isset($input['cart_id']) ? (int)$input['cart_id'] : null;
if (!$cartId) {
    echo json_encode(['error' => 'cart_id required']);
    exit;
}

$now = date('Y-m-d H:i:s');
$status = 1; // completed
$stmt = $conn->prepare("UPDATE cart SET Status = ?, Updated_at = ? WHERE Cart_ID = ?");
$stmt->bind_param('isi', $status, $now, $cartId);
if (!$stmt->execute()) {
    echo json_encode(['error' => 'Checkout failed: ' . $stmt->error]);
    exit;
}

echo json_encode(['success' => true]);

?>
