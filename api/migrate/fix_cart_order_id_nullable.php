<?php
require_once __DIR__ . '/../../DB.php';
header('Content-Type: application/json');

$results = [];

try {
    // First, drop the foreign key constraint
    $results[] = ['action' => 'drop_foreign_key', 'query' => 'ALTER TABLE cart DROP FOREIGN KEY cart_ibfk_2'];
    if ($conn->query("ALTER TABLE cart DROP FOREIGN KEY cart_ibfk_2")) {
        $results[] = ['action' => 'drop_foreign_key', 'success' => true];
    } else {
        $results[] = ['action' => 'drop_foreign_key', 'success' => false, 'error' => $conn->error];
    }
    
    // Make Order_ID nullable
    $results[] = ['action' => 'make_nullable', 'query' => 'ALTER TABLE cart MODIFY Order_ID INT(11) NULL'];
    if ($conn->query("ALTER TABLE cart MODIFY Order_ID INT(11) NULL")) {
        $results[] = ['action' => 'make_nullable', 'success' => true];
    } else {
        $results[] = ['action' => 'make_nullable', 'success' => false, 'error' => $conn->error];
    }
    
    // Re-add foreign key constraint (allows NULL values)
    $results[] = ['action' => 'add_foreign_key', 'query' => 'ALTER TABLE cart ADD CONSTRAINT cart_ibfk_2 FOREIGN KEY (Order_ID) REFERENCES corder(Order_ID)'];
    if ($conn->query("ALTER TABLE cart ADD CONSTRAINT cart_ibfk_2 FOREIGN KEY (Order_ID) REFERENCES corder(Order_ID)")) {
        $results[] = ['action' => 'add_foreign_key', 'success' => true];
    } else {
        $results[] = ['action' => 'add_foreign_key', 'success' => false, 'error' => $conn->error];
    }
    
} catch (Exception $e) {
    $results[] = ['action' => 'error', 'success' => false, 'error' => $e->getMessage()];
}

echo json_encode($results, JSON_PRETTY_PRINT);
?>

