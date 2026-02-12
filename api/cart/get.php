<?php
require_once __DIR__ . '/../../DB.php';
require_once __DIR__ . '/helpers.php';
header('Content-Type: application/json');

// Start session to get user_id
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$cartId = isset($_GET['cart_id']) ? (int)$_GET['cart_id'] : null;
$memberId = isset($_GET['member_id']) ? (int)$_GET['member_id'] : null;

// Get member_id from session if not provided
if (!$memberId) {
    $memberId = $_SESSION['user_id'] ?? null;
}

// If no member_id and no cart_id, return empty (user not logged in)
if (!$cartId && !$memberId) {
    echo json_encode(['items' => [], 'cart_id' => null, 'logged_in' => false]);
    exit;
}

if ($cartId) {
    $stmt = $conn->prepare("SELECT ci.Cart_Item_ID, ci.Quantity, ci.Unit_price_at_add_time, ci.Subtotal_amount, ci.created_at, p.Product_ID as id, p.Name as name, p.thumbnail_url as img FROM cartitem ci LEFT JOIN product p ON ci.Product_ID = p.Product_ID WHERE ci.Cart_ID = ?");
    $stmt->bind_param('i', $cartId);
} else {
    // find active cart for member (Status = 0 means active/pending)
    $stmt = $conn->prepare("SELECT c.Cart_ID FROM cart c WHERE c.Member_Id = ? AND c.Status = 0 ORDER BY c.Created_at DESC LIMIT 1");
    $stmt->bind_param('i', $memberId);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($r = $res->fetch_assoc()) {
        $cartId = (int)$r['Cart_ID'];
    } else {
        // No active cart found - return empty
        echo json_encode(['items' => [], 'cart_id' => null]);
        exit;
    }
    $stmt->close();
    $stmt = $conn->prepare("SELECT ci.Cart_Item_ID, ci.Quantity, ci.Unit_price_at_add_time, ci.Subtotal_amount, ci.created_at, p.Product_ID as id, p.Name as name, p.thumbnail_url as img FROM cartitem ci LEFT JOIN product p ON ci.Product_ID = p.Product_ID WHERE ci.Cart_ID = ?");
    $stmt->bind_param('i', $cartId);
}

$stmt->execute();
$res = $stmt->get_result();
$items = [];
while ($r = $res->fetch_assoc()) {
    $items[] = [
        'cart_item_id' => (int)$r['Cart_Item_ID'],
        'product' => [
            'id' => (int)$r['id'],
            'name' => $r['name'],
            'img' => $r['img']
        ],
        'quantity' => (int)$r['Quantity'],
        'unit_price' => (float)$r['Unit_price_at_add_time'],
        'subtotal' => (float)$r['Subtotal_amount']
    ];
}
$stmt->close();

// Get order totals from corder table
$orderSubtotal = 0;
$orderItemCount = 0;
if ($cartId) {
    // Get Order_ID from cart
    $stmt = $conn->prepare("SELECT Order_ID FROM cart WHERE Cart_ID = ?");
    $stmt->bind_param('i', $cartId);
    $stmt->execute();
    $cartResult = $stmt->get_result();
    if ($cartRow = $cartResult->fetch_assoc()) {
        $orderId = (int)$cartRow['Order_ID'];
        if ($orderId) {
            // Get totals from corder table
            $stmt = $conn->prepare("SELECT Subtotal_amount, Total_items FROM corder WHERE Order_ID = ?");
            $stmt->bind_param('i', $orderId);
            $stmt->execute();
            $orderResult = $stmt->get_result();
            if ($orderRow = $orderResult->fetch_assoc()) {
                $orderSubtotal = (float)($orderRow['Subtotal_amount'] ?? 0);
                $orderItemCount = (int)($orderRow['Total_items'] ?? 0);
            }
        }
    }
    $stmt->close();
}

echo json_encode([
    'cart_id' => $cartId,
    'items' => $items,
    'order_subtotal' => $orderSubtotal,
    'order_item_count' => $orderItemCount
]);
?>