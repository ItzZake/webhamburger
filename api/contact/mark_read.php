<?php
require_once __DIR__ . '/../../DB.php';
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$id = $_GET['id'] ?? null;

if (!$id || !is_numeric($id)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid message ID']);
    exit;
}

try {
    // Update the message to mark as read
    $conn->query("UPDATE ContactMessage SET Is_Read = 1, Read_At = NOW() WHERE Contact_ID = $id");
    
    if ($conn->affected_rows > 0) {
        echo json_encode(['success' => true]);
        exit;
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Message not found or already read']);
        exit;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    exit;
}
?>
<parameter name="filePath">c:\xampp\htdocs\a\api\contact\mark_read.php