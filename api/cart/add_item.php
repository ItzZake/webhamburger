<?php
// Turn off error display, capture errors instead
ini_set('display_errors', 0);
error_reporting(E_ALL);
ob_start();

// Register shutdown function to catch fatal errors
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        ob_clean();
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode([
            'error' => 'Server error: ' . basename($error['file']) . ':' . $error['line'] . ' - ' . $error['message']
        ]);
        exit;
    }
});

try {
    require_once __DIR__ . '/../../DB.php';
    require_once __DIR__ . '/helpers.php';
} catch (Exception $e) {
    ob_clean();
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Failed to load required files: ' . $e->getMessage()]);
    exit;
}

// Start session to get user_id
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Clear any output that might have been generated
ob_clean();
header('Content-Type: application/json');

$rawInput = file_get_contents('php://input');

// Try to decode JSON body. If that fails, try a couple of fallbacks to be forgiving
$input = json_decode($rawInput, true);
if (!$input) {
    // Sometimes Windows/curl/PowerShell produce backslash-escaped payloads like: {\"product_id\":1}
    $clean = str_replace('\\', '', $rawInput);
    $input = json_decode($clean, true);
    if (!$input) {
        // Try to remove stray quotes/spaces that some PowerShell variants include
        $clean2 = preg_replace('/"\s+/', '', $clean);
        $clean2 = str_replace('"', '', $clean2);
        // Try to quote unquoted keys (e.g. {product_id:1,cart_id:5} -> {"product_id":1,...})
        $fixed = preg_replace('/([\{,\s])(\w+)\s*:/', '$1"$2":', $clean2);
        $input = json_decode($fixed, true);
    }
}
// optional debug logging when requested
if (isset($_GET['debug']) && $_GET['debug']) {
    $logDir = __DIR__ . '/../../tmp';
    if (!is_dir($logDir)) @mkdir($logDir, 0777, true);
    @file_put_contents($logDir . '/add_item_input_debug.log', date('c') . " RAW: " . $rawInput . PHP_EOL, FILE_APPEND);
    @file_put_contents($logDir . '/add_item_input_debug.log', date('c') . " CLEAN: " . ($clean ?? '') . PHP_EOL, FILE_APPEND);
    @file_put_contents($logDir . '/add_item_input_debug.log', date('c') . " CLEAN2: " . ($clean2 ?? '') . PHP_EOL, FILE_APPEND);
    @file_put_contents($logDir . '/add_item_input_debug.log', date('c') . " FIXED: " . ($fixed ?? '') . PHP_EOL, FILE_APPEND);
}
if (!$input && !empty($_POST)) {
    // Fallback to form-encoded POST fields
    $input = $_POST;
}
if (!$input || !isset($input['product_id'])) {
    ob_clean();
    http_response_code(400);
    echo json_encode(['error' => 'product_id required']);
    exit;
}

$cartId = isset($input['cart_id']) ? (int)$input['cart_id'] : null;
$memberId = isset($input['member_id']) ? (int)$input['member_id'] : null;

// Get member_id from session if not provided and user is logged in
if (!$memberId) {
    $memberId = $_SESSION['user_id'] ?? null;
}

$productId = (int)$input['product_id'];
$quantity = isset($input['quantity']) ? max(1, (int)$input['quantity']) : 1;
$unit = isset($input['unit_price']) ? floatval($input['unit_price']) : 0.0;
$now = date('Y-m-d H:i:s');

// Check if user is logged in
if (!$memberId) {
    ob_clean();
    http_response_code(401);
    echo json_encode([
        'error' => 'login_required',
        'message' => 'You must create an account and log in to add items to your cart.'
    ]);
    exit;
}

// Get or create order and cart (creates order first, then cart)
$orderAndCart = getOrCreateOrderAndCart($memberId, $conn);
if (!$orderAndCart) {
    ob_clean();
    http_response_code(500);
    echo json_encode(['error' => 'Failed to get or create order and cart']);
    exit;
}

$orderId = $orderAndCart['order_id'];
$cartId = $orderAndCart['cart_id'];

// Check if item exists in cart
$stmt = $conn->prepare("SELECT Cart_Item_ID, Quantity FROM cartitem WHERE Cart_ID = ? AND Product_ID = ?");
$stmt->bind_param('ii', $cartId, $productId);
$stmt->execute();
$res = $stmt->get_result();
if ($r = $res->fetch_assoc()) {
    // update quantity
    $newQty = (int)$r['Quantity'] + $quantity;
    $subtotal = $newQty * $unit;
    $stmt->close();
    $u = $conn->prepare("UPDATE cartitem SET Quantity = ?, Unit_price_at_add_time = ?, Subtotal_amount = ?, Updated_at = ? WHERE Cart_Item_ID = ?");
    $u->bind_param('iddsi', $newQty, $unit, $subtotal, $now, $r['Cart_Item_ID']);
    if (!$u->execute()) {
        ob_clean();
        http_response_code(500);
        echo json_encode(['error' => 'Update cart item failed: ' . $u->error]);
        exit;
    }
    // Update order totals
    $stmt = $conn->prepare("SELECT SUM(Subtotal_amount) as total FROM cartitem WHERE Cart_ID = ?");
    $stmt->bind_param('i', $cartId);
    $stmt->execute();
    $totalResult = $stmt->get_result();
    $totalRow = $totalResult->fetch_assoc();
    $stmt->close();
    
    $newSubtotal = (float)($totalRow['total'] ?? 0);
    $totalItems = (int)$newQty; // This is just for this item, we'll recalculate
    
    // Recalculate total items
    $stmt = $conn->prepare("SELECT SUM(Quantity) as total_qty FROM cartitem WHERE Cart_ID = ?");
    $stmt->bind_param('i', $cartId);
    $stmt->execute();
    $qtyResult = $stmt->get_result();
    $qtyRow = $qtyResult->fetch_assoc();
    $stmt->close();
    $totalItems = (int)($qtyRow['total_qty'] ?? 0);
    
    // Update order
    $stmt = $conn->prepare("UPDATE corder SET Subtotal_amount = ?, Total_items = ?, updated_at = ? WHERE Order_ID = ?");
    $stmt->bind_param('disi', $newSubtotal, $totalItems, $now, $orderId);
    $stmt->execute();
    $stmt->close();
    
    // Get updated cart item info
    $stmt = $conn->prepare("SELECT ci.Cart_Item_ID, ci.Quantity, ci.Unit_price_at_add_time, ci.Subtotal_amount FROM cartitem ci WHERE ci.Cart_Item_ID = ?");
    $stmt->bind_param('i', $r['Cart_Item_ID']);
    $stmt->execute();
    $updatedResult = $stmt->get_result();
    $updatedItem = $updatedResult->fetch_assoc();
    $stmt->close();
    
    ob_clean();
    echo json_encode([
        'cart_id' => $cartId, 
        'order_id' => $orderId, 
        'updated' => true,
        'cart_item_id' => (int)$updatedItem['Cart_Item_ID'],
        'quantity' => (int)$updatedItem['Quantity'],
        'unit_price' => (float)$updatedItem['Unit_price_at_add_time'],
        'subtotal' => (float)$updatedItem['Subtotal_amount']
    ]);
    exit;
}
$stmt->close();

// Insert new item
$subtotal = $quantity * $unit;
$ins = $conn->prepare("INSERT INTO cartitem (Quantity, Unit_price_at_add_time, Subtotal_amount, created_at, Updated_at, Cart_ID, Product_ID) VALUES (?, ?, ?, ?, ?, ?, ?)");
$ins->bind_param('iddssii', $quantity, $unit, $subtotal, $now, $now, $cartId, $productId);
if (!$ins->execute()) {
    ob_clean();
    http_response_code(500);
    echo json_encode(['error' => 'Insert cart item failed: ' . $ins->error]);
    exit;
}
$newCartItemId = $conn->insert_id;
$ins->close();

// Update order totals
$stmt = $conn->prepare("SELECT SUM(Subtotal_amount) as total, SUM(Quantity) as total_qty FROM cartitem WHERE Cart_ID = ?");
$stmt->bind_param('i', $cartId);
$stmt->execute();
$totalResult = $stmt->get_result();
$totalRow = $totalResult->fetch_assoc();
$stmt->close();

$newSubtotal = (float)($totalRow['total'] ?? 0);
$totalItems = (int)($totalRow['total_qty'] ?? 0);

// Update order
$stmt = $conn->prepare("UPDATE corder SET Subtotal_amount = ?, Total_items = ?, updated_at = ? WHERE Order_ID = ?");
$stmt->bind_param('disi', $newSubtotal, $totalItems, $now, $orderId);
$stmt->execute();
$stmt->close();

// Get inserted cart item info for response
$stmt = $conn->prepare("SELECT ci.Cart_Item_ID, ci.Quantity, ci.Unit_price_at_add_time, ci.Subtotal_amount FROM cartitem ci WHERE ci.Cart_Item_ID = ?");
$stmt->bind_param('i', $newCartItemId);
$stmt->execute();
$itemResult = $stmt->get_result();
$itemRow = $itemResult->fetch_assoc();
$stmt->close();

ob_clean();
echo json_encode([
    'cart_id' => $cartId, 
    'order_id' => $orderId, 
    'inserted' => true,
    'cart_item_id' => (int)$itemRow['Cart_Item_ID'],
    'quantity' => (int)$itemRow['Quantity'],
    'unit_price' => (float)$itemRow['Unit_price_at_add_time'],
    'subtotal' => (float)$itemRow['Subtotal_amount']
]);
ob_end_flush();
?>
