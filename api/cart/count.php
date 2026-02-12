<?php
require_once __DIR__ . '/../../DB.php';
require_once __DIR__ . '/../helpers/auth.php';
header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    echo json_encode(['count' => 0]);
    exit;
}

// Get active cart for user (Status = 0 means active/pending)
$stmt = $conn->prepare("SELECT Cart_ID FROM cart WHERE Member_Id = ? AND Status = 0 ORDER BY Created_at DESC LIMIT 1");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
$cart = $result->fetch_assoc();
$stmt->close();

if (!$cart) {
    echo json_encode(['count' => 0, 'cart_id' => null]);
    exit;
}

$cartId = $cart['Cart_ID'];

// Count items in cart
$stmt = $conn->prepare("SELECT SUM(Quantity) as total FROM cartitem WHERE Cart_ID = ?");
$stmt->bind_param('i', $cartId);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$stmt->close();

$count = (int)($row['total'] ?? 0);

echo json_encode([
    'count' => $count,
    'cart_id' => $cartId
]);
?>

