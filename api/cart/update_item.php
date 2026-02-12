<?php
require_once __DIR__ . '/../../DB.php';
require_once __DIR__ . '/helpers.php';
header('Content-Type: application/json');

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// robust input parsing to support various clients (JSON, backslash-escaped, or form POST)
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
if (!$input || !isset($input['cart_item_id'])) {
    echo json_encode(['error' => 'cart_item_id required']);
    exit;
}

$cartItemId = (int)$input['cart_item_id'];
$quantity = isset($input['quantity']) ? (int)$input['quantity'] : null;
$unit = isset($input['unit_price']) ? floatval($input['unit_price']) : null;
$now = date('Y-m-d H:i:s');

// Check if user is logged in
$userId = $_SESSION['user_id'] ?? null;
if (!$userId) {
    http_response_code(401);
    echo json_encode(['error' => 'login_required', 'message' => 'You must be logged in to modify your cart.']);
    exit;
}

$stmt = $conn->prepare("SELECT ci.Cart_Item_ID, ci.Quantity, ci.Unit_price_at_add_time, ci.Cart_ID, c.Member_Id 
                       FROM cartitem ci 
                       JOIN cart c ON ci.Cart_ID = c.Cart_ID 
                       WHERE ci.Cart_Item_ID = ?");
$stmt->bind_param('i', $cartItemId);
$stmt->execute();
$res = $stmt->get_result();
if (!($r = $res->fetch_assoc())) {
    http_response_code(404);
    echo json_encode(['error' => 'cart item not found']);
    exit;
}

// Verify cart belongs to logged-in user
if ((int)$r['Member_Id'] !== (int)$userId) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized: This cart item does not belong to you.']);
    exit;
}

$cartId = (int)$r['Cart_ID'];
$stmt->close();

$newQty = $quantity !== null ? max(0, $quantity) : (int)$r['Quantity'];
$newUnit = $unit !== null ? $unit : (float)$r['Unit_price_at_add_time'];

if ($newQty === 0) {
    $d = $conn->prepare("DELETE FROM cartitem WHERE Cart_Item_ID = ?");
    $d->bind_param('i', $cartItemId);
    if (!$d->execute()) {
        http_response_code(500);
        echo json_encode(['error' => 'Delete failed: ' . $d->error]);
        exit;
    }
    $d->close();
    
    // Check if cart is empty and delete order/cart if so
    deleteOrderAndCartIfEmpty($cartId, $conn);
    
    echo json_encode(['deleted' => true, 'cart_id' => $cartId]);
    exit;
}

$subtotal = $newQty * $newUnit;
$u = $conn->prepare("UPDATE cartitem SET Quantity = ?, Unit_price_at_add_time = ?, Subtotal_amount = ?, Updated_at = ? WHERE Cart_Item_ID = ?");
$u->bind_param('iddsi', $newQty, $newUnit, $subtotal, $now, $cartItemId);
if (!$u->execute()) {
    http_response_code(500);
    echo json_encode(['error' => 'Update failed: ' . $u->error]);
    exit;
}
$u->close();

// Update order totals
$stmt = $conn->prepare("SELECT SUM(Subtotal_amount) as total, SUM(Quantity) as total_qty FROM cartitem WHERE Cart_ID = ?");
$stmt->bind_param('i', $cartId);
$stmt->execute();
$totalResult = $stmt->get_result();
$totalRow = $totalResult->fetch_assoc();
$stmt->close();

$newSubtotal = (float)($totalRow['total'] ?? 0);
$totalItems = (int)($totalRow['total_qty'] ?? 0);

// Get Order_ID from cart
$stmt = $conn->prepare("SELECT Order_ID FROM cart WHERE Cart_ID = ?");
$stmt->bind_param('i', $cartId);
$stmt->execute();
$cartResult = $stmt->get_result();
$cartRow = $cartResult->fetch_assoc();
$stmt->close();

if ($cartRow) {
    $orderId = (int)$cartRow['Order_ID'];
    
    // Update order
    $stmt = $conn->prepare("UPDATE corder SET Subtotal_amount = ?, Total_items = ?, updated_at = ? WHERE Order_ID = ?");
    $stmt->bind_param('disi', $newSubtotal, $totalItems, $now, $orderId);
    $stmt->execute();
    $stmt->close();
}

echo json_encode(['updated' => true, 'cart_id' => $cartId]);

?>
