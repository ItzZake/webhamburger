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
$memberId = isset($input['member_id']) ? (int)$input['member_id'] : null;

// If cart_id provided, return it after validation
if (isset($input['cart_id']) && (int)$input['cart_id'] > 0) {
    $cartId = (int)$input['cart_id'];
    echo json_encode(['cart_id' => $cartId]);
    exit;
}

// Try to find active cart for member
if ($memberId) {
    $stmt = $conn->prepare("SELECT Cart_ID FROM cart WHERE Member_Id = ? AND Status = 0 ORDER BY Created_at DESC LIMIT 1");
    $stmt->bind_param('i', $memberId);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($r = $res->fetch_assoc()) {
        echo json_encode(['cart_id' => (int)$r['Cart_ID']]);
        exit;
    }
    $stmt->close();
}

// No existing cart — create one
$now = date('Y-m-d H:i:s');
$status = 0;
if ($memberId) {
    // Use NULL for Order_ID (no order yet, will be updated when order is created during checkout)
    $stmt = $conn->prepare("INSERT INTO cart (Status, Created_at, Updated_at, Member_Id, Order_ID) VALUES (?, ?, ?, ?, NULL)");
    $memberIdInt = $memberId ? $memberId : 0;
    $stmt->bind_param('issi', $status, $now, $now, $memberIdInt);
} else {
    // no member provided — try to use any existing member id to satisfy FK
    $res = $conn->query('SELECT Member_Id FROM memberprofile LIMIT 1');
    if ($res && $row = $res->fetch_assoc()) {
        $memberIdInt = (int)$row['Member_Id'];
        // insert with NULL for order id (no order yet)
        $stmt = $conn->prepare("INSERT INTO cart (Status, Created_at, Updated_at, Member_Id, Order_ID) VALUES (?, ?, ?, ?, NULL)");
        $stmt->bind_param('issi', $status, $now, $now, $memberIdInt);
    } else {
        // insert with NULL member and NULL for order
        $stmt = $conn->prepare("INSERT INTO cart (Status, Created_at, Updated_at, Member_Id, Order_ID) VALUES (?, ?, ?, NULL, NULL)");
        $stmt->bind_param('iss', $status, $now, $now);
    }
}
if (!$stmt->execute()) {
    echo json_encode(['error' => 'Create cart failed: ' . $stmt->error]);
    exit;
}
$cartId = $conn->insert_id;
$stmt->close();

echo json_encode(['cart_id' => (int)$cartId]);

?>
