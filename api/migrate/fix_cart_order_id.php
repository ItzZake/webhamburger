<?php
require_once __DIR__ . '/../../DB.php';
header('Content-Type: application/json');

$results = [];

// Check if order with ID 0 exists
$check = $conn->query("SELECT Order_ID FROM corder WHERE Order_ID = 0");
if ($check && $check->num_rows === 0) {
    // Create a dummy order with ID 0 to satisfy foreign key constraint
    // This represents "no order yet" for active carts
    $now = date('Y-m-d H:i:s');
    $orderDate = date('Y-m-d');
    
    // First, temporarily disable foreign key checks
    $conn->query("SET FOREIGN_KEY_CHECKS = 0");
    
    // Insert dummy order with ID 0
    $stmt = $conn->prepare("INSERT INTO corder (Order_ID, Order_date, Status, Total_items, Subtotal_amount, tax_amount, shipping, discount_amount, total_amount, currency, notes, created_at, updated_at, Member_Id) 
                            VALUES (0, ?, 'Dummy', 0, 0, 0, 'N/A', 0, 0, 1, 'Dummy order for active carts', ?, ?, 0)");
    $stmt->bind_param('sss', $orderDate, $now, $now);
    
    if ($stmt->execute()) {
        $results[] = ['action' => 'created_dummy_order', 'success' => true, 'message' => 'Created dummy order with ID 0'];
    } else {
        $results[] = ['action' => 'created_dummy_order', 'success' => false, 'error' => $stmt->error];
    }
    $stmt->close();
    
    // Re-enable foreign key checks
    $conn->query("SET FOREIGN_KEY_CHECKS = 1");
} else {
    $results[] = ['action' => 'check_dummy_order', 'success' => true, 'message' => 'Dummy order with ID 0 already exists'];
}

echo json_encode($results, JSON_PRETTY_PRINT);
?>

