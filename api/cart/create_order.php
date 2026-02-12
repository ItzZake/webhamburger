<?php
require_once __DIR__ . '/../../DB.php';
require_once __DIR__ . '/../helpers/auth.php';
header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit;
}

$rawInput = file_get_contents('php://input');
$input = json_decode($rawInput, true);

// Optional: shipping, tax, discount can be passed in input
$shipping = isset($input['shipping']) ? $input['shipping'] : 'Standard';
$tax_rate = isset($input['tax_rate']) ? floatval($input['tax_rate']) : 0.0;
$discount_amount = isset($input['discount_amount']) ? floatval($input['discount_amount']) : 0.0;
$notes = isset($input['notes']) ? trim($input['notes']) : '';

// Get active cart and order for user
$stmt = $conn->prepare("SELECT Cart_ID, Order_ID FROM cart WHERE Member_Id = ? AND Status = 0 ORDER BY Created_at DESC LIMIT 1");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
$cart = $result->fetch_assoc();
$stmt->close();

if (!$cart) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'No active cart found']);
    exit;
}

$cartId = $cart['Cart_ID'];
$orderId = (int)$cart['Order_ID'];

// Get cart items
$stmt = $conn->prepare("SELECT ci.Cart_Item_ID, ci.Quantity, ci.Unit_price_at_add_time, ci.Subtotal_amount, ci.Product_ID, p.Name as product_name 
                        FROM cartitem ci 
                        LEFT JOIN product p ON ci.Product_ID = p.Product_ID 
                        WHERE ci.Cart_ID = ?");
$stmt->bind_param('i', $cartId);
$stmt->execute();
$itemsResult = $stmt->get_result();
$cartItems = [];
$totalItems = 0;
$subtotal = 0.0;

while ($item = $itemsResult->fetch_assoc()) {
    $cartItems[] = $item;
    $totalItems += (int)$item['Quantity'];
    $subtotal += (float)$item['Subtotal_amount'];
}
$stmt->close();

if (empty($cartItems)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Cart is empty']);
    exit;
}

// Calculate totals
$tax_amount = $subtotal * ($tax_rate / 100);
$shipping_cost = ($shipping === 'Express') ? 10.0 : 5.0;
$total_amount = $subtotal + $tax_amount + $shipping_cost - $discount_amount;

// Start transaction
$conn->begin_transaction();

try {
    $now = date('Y-m-d H:i:s');
    $orderStatus = 'Pending'; // Update from Draft to Pending on checkout
    
    // Create order items from cart items (order already exists)
    foreach ($cartItems as $item) {
        $quantity = (int)$item['Quantity'];
        $unitPrice = (float)$item['Unit_price_at_add_time'];
        $lineTotal = (int)($quantity * $unitPrice); // line_total_amount is INT
        $discount = 0.0; // Can be calculated per item if needed
        
        $stmt = $conn->prepare("INSERT INTO orderitem (Quantity, Unit_Price, Discount_amount, line_total_amount, created_at, Order_ID, Product_ID) 
                                VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('iddiisi', $quantity, $unitPrice, $discount, $lineTotal, $now, $orderId, $item['Product_ID']);
        
        if (!$stmt->execute()) {
            throw new Exception('Failed to create order item: ' . $stmt->error);
        }
        $stmt->close();
    }
    
    // Update order with final totals and status
    $stmt = $conn->prepare("UPDATE corder SET Status = ?, Subtotal_amount = ?, tax_amount = ?, shipping = ?, discount_amount = ?, total_amount = ?, Total_items = ?, notes = ?, updated_at = ? WHERE Order_ID = ?");
    $stmt->bind_param('sdddddissi', $orderStatus, $subtotal, $tax_amount, $shipping, $discount_amount, $total_amount, $totalItems, $notes, $now, $orderId);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to update order: ' . $stmt->error);
    }
    $stmt->close();
    
    // Update cart: set Status = 1 (completed)
    $stmt = $conn->prepare("UPDATE cart SET Status = 1, Updated_at = ? WHERE Cart_ID = ?");
    $stmt->bind_param('si', $now, $cartId);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to update cart: ' . $stmt->error);
    }
    $stmt->close();
    
    // Delete cart items (cart is now completed, items are in order)
    $stmt = $conn->prepare("DELETE FROM cartitem WHERE Cart_ID = ?");
    $stmt->bind_param('i', $cartId);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to clear cart items: ' . $stmt->error);
    }
    $stmt->close();
    
    // Commit transaction
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'order_id' => $orderId,
        'cart_id' => $cartId,
        'total_amount' => $total_amount,
        'message' => 'Order created successfully'
    ]);
    
} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>

