<?php
require_once __DIR__ . '/../../DB.php';
require_once __DIR__ . '/helpers.php';
header('Content-Type: application/json');

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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
if (!$input) {
    echo json_encode(['error' => 'Invalid input']);
    exit;
}

if (isset($input['cart_item_id'])) {
    $id = (int)$input['cart_item_id'];
    
    // Check if user is logged in
    $userId = $_SESSION['user_id'] ?? null;
    if (!$userId) {
        http_response_code(401);
        echo json_encode(['error' => 'login_required', 'message' => 'You must be logged in to modify your cart.']);
        exit;
    }
    
    // Get cart_id and verify ownership before deleting
    $stmt = $conn->prepare("SELECT ci.Cart_ID, c.Member_Id FROM cartitem ci 
                           JOIN cart c ON ci.Cart_ID = c.Cart_ID 
                           WHERE ci.Cart_Item_ID = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $itemRow = $result->fetch_assoc();
    $stmt->close();
    
    if (!$itemRow) {
        http_response_code(404);
        echo json_encode(['error' => 'Cart item not found']);
        exit;
    }
    
    // Verify cart belongs to logged-in user
    if ((int)$itemRow['Member_Id'] !== (int)$userId) {
        http_response_code(403);
        echo json_encode(['error' => 'Unauthorized: This cart item does not belong to you.']);
        exit;
    }
    
    $cartId = (int)$itemRow['Cart_ID'];
    
    // Get Order_ID from cart before deleting
    $stmt = $conn->prepare("SELECT Order_ID FROM cart WHERE Cart_ID = ?");
    $stmt->bind_param('i', $cartId);
    $stmt->execute();
    $cartResult = $stmt->get_result();
    $cartRow = $cartResult->fetch_assoc();
    $stmt->close();
    $orderId = $cartRow ? (int)$cartRow['Order_ID'] : null;
    
    // Delete the item
    $stmt = $conn->prepare("DELETE FROM cartitem WHERE Cart_Item_ID = ?");
    $stmt->bind_param('i', $id);
    if (!$stmt->execute()) {
        http_response_code(500);
        echo json_encode(['error' => 'Delete failed: ' . $stmt->error]);
        exit;
    }
    $stmt->close();
    
    // Check if cart is empty and delete order/cart if so
    $cartEmpty = deleteOrderAndCartIfEmpty($cartId, $conn);
    
    // If cart is not empty, update order totals (same as add_item.php)
    if (!$cartEmpty && $orderId) {
        $now = date('Y-m-d H:i:s');
        
        // Recalculate subtotal from remaining items
        $stmt = $conn->prepare("SELECT SUM(Subtotal_amount) as total FROM cartitem WHERE Cart_ID = ?");
        $stmt->bind_param('i', $cartId);
        $stmt->execute();
        $totalResult = $stmt->get_result();
        $totalRow = $totalResult->fetch_assoc();
        $stmt->close();
        
        $newSubtotal = (float)($totalRow['total'] ?? 0);
        
        // Recalculate total items
        $stmt = $conn->prepare("SELECT SUM(Quantity) as total_qty FROM cartitem WHERE Cart_ID = ?");
        $stmt->bind_param('i', $cartId);
        $stmt->execute();
        $qtyResult = $stmt->get_result();
        $qtyRow = $qtyResult->fetch_assoc();
        $stmt->close();
        $totalItems = (int)($qtyRow['total_qty'] ?? 0);
        
        // Update order totals in corder table
        $stmt = $conn->prepare("UPDATE corder SET Subtotal_amount = ?, Total_items = ?, updated_at = ? WHERE Order_ID = ?");
        $stmt->bind_param('disi', $newSubtotal, $totalItems, $now, $orderId);
        $stmt->execute();
        $stmt->close();
    }
    
    ob_clean();
    echo json_encode(['deleted' => true, 'cart_id' => $cartId]);
    exit;
}

if (isset($input['cart_id']) && isset($input['product_id'])) {
    $cartId = (int)$input['cart_id'];
    $productId = (int)$input['product_id'];
    
    // Delete the item
    $stmt = $conn->prepare("DELETE FROM cartitem WHERE Cart_ID = ? AND Product_ID = ?");
    $stmt->bind_param('ii', $cartId, $productId);
    if (!$stmt->execute()) {
        http_response_code(500);
        echo json_encode(['error' => 'Delete failed: ' . $stmt->error]);
        exit;
    }
    $stmt->close();
    
    // Check if cart is empty and delete order/cart if so
    deleteOrderAndCartIfEmpty($cartId, $conn);
    
    echo json_encode(['deleted' => true, 'cart_id' => $cartId]);
    exit;
}

echo json_encode(['error' => 'cart_item_id or cart_id+product_id required']);

?>
