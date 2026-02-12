<?php
/**
 * Create order for a user (initial order with default values)
 * Returns the order_id or false on failure
 */
function createOrderForUser($userId, $conn) {
    if (!$userId) {
        return false;
    }
    
    $now = date('Y-m-d H:i:s');
    $orderDate = date('Y-m-d');
    $orderStatus = 'Draft'; // Draft status for cart orders, will be updated to Pending on checkout
    
    $stmt = $conn->prepare("INSERT INTO corder (Order_date, Status, Total_items, Subtotal_amount, tax_amount, shipping, discount_amount, total_amount, currency, notes, created_at, updated_at, Member_Id) 
                            VALUES (?, ?, 0, 0, 0, 'Standard', 0, 0, 1, 'Cart order', ?, ?, ?)");
    if (!$stmt) {
        error_log('createOrderForUser: Failed to prepare: ' . $conn->error);
        return false;
    }
    
    $stmt->bind_param('ssssi', $orderDate, $orderStatus, $now, $now, $userId);
    if (!$stmt->execute()) {
        error_log('createOrderForUser: Failed to execute: ' . $stmt->error);
        $stmt->close();
        return false;
    }
    
    $orderId = $conn->insert_id;
    $stmt->close();
    
    return $orderId;
}

/**
 * Create cart for a user with an existing order
 * Returns the cart_id or false on failure
 */
function createCartForUser($userId, $orderId, $conn) {
    if (!$userId || !$orderId) {
        return false;
    }
    
    // Check if user already has an active cart
    $stmt = $conn->prepare("SELECT Cart_ID FROM cart WHERE Member_Id = ? AND Status = 0 ORDER BY Created_at DESC LIMIT 1");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $stmt->close();
        return (int)$row['Cart_ID'];
    }
    $stmt->close();
    
    // Create new cart with Order_ID
    $now = date('Y-m-d H:i:s');
    $status = 0; // 0 = active/pending
    
    $stmt = $conn->prepare("INSERT INTO cart (Status, Created_at, Updated_at, Member_Id, Order_ID) VALUES (?, ?, ?, ?, ?)");
    if (!$stmt) {
        error_log('createCartForUser: Failed to prepare: ' . $conn->error);
        return false;
    }
    
    $stmt->bind_param('issii', $status, $now, $now, $userId, $orderId);
    if (!$stmt->execute()) {
        error_log('createCartForUser: Failed to execute: ' . $stmt->error);
        $stmt->close();
        return false;
    }
    
    $cartId = $conn->insert_id;
    $stmt->close();
    
    return $cartId;
}

/**
 * Get or create order and cart for a user
 * Returns array with 'order_id' and 'cart_id' or false on failure
 */
function getOrCreateOrderAndCart($userId, $conn) {
    if (!$userId) {
        return false;
    }
    
    // Try to get existing active cart
    $stmt = $conn->prepare("SELECT Cart_ID, Order_ID FROM cart WHERE Member_Id = ? AND Status = 0 ORDER BY Created_at DESC LIMIT 1");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $stmt->close();
        return [
            'order_id' => (int)$row['Order_ID'],
            'cart_id' => (int)$row['Cart_ID']
        ];
    }
    $stmt->close();
    
    // No active cart exists - create order first, then cart
    $orderId = createOrderForUser($userId, $conn);
    if (!$orderId) {
        return false;
    }
    
    $cartId = createCartForUser($userId, $orderId, $conn);
    if (!$cartId) {
        // If cart creation fails, we should delete the order to avoid orphaned records
        $conn->query("DELETE FROM corder WHERE Order_ID = $orderId");
        return false;
    }
    
    return [
        'order_id' => $orderId,
        'cart_id' => $cartId
    ];
}

/**
 * Check if cart is empty and delete order and cart if so
 * Returns true if deleted, false otherwise
 */
function deleteOrderAndCartIfEmpty($cartId, $conn) {
    if (!$cartId) {
        return false;
    }
    
    // Check if cart has any items
    $stmt = $conn->prepare("SELECT COUNT(*) as item_count FROM cartitem WHERE Cart_ID = ?");
    $stmt->bind_param('i', $cartId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    
    if ((int)$row['item_count'] > 0) {
        return false; // Cart is not empty
    }
    
    // Cart is empty - get Order_ID and delete both
    $stmt = $conn->prepare("SELECT Order_ID FROM cart WHERE Cart_ID = ?");
    $stmt->bind_param('i', $cartId);
    $stmt->execute();
    $result = $stmt->get_result();
    $cartRow = $result->fetch_assoc();
    $stmt->close();
    
    if ($cartRow) {
        $orderId = (int)$cartRow['Order_ID'];
        
        // Delete cart first (due to foreign key)
        $stmt = $conn->prepare("DELETE FROM cart WHERE Cart_ID = ?");
        $stmt->bind_param('i', $cartId);
        $stmt->execute();
        $stmt->close();
        
        // Delete order
        $stmt = $conn->prepare("DELETE FROM corder WHERE Order_ID = ?");
        $stmt->bind_param('i', $orderId);
        $stmt->execute();
        $stmt->close();
        
        return true;
    }
    
    return false;
}
?>
