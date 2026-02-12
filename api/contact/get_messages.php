<?php
require_once __DIR__ . '/../../DB.php';
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

try {
    $messages = select('ContactMessage', '*', null, 'Submitted_At DESC');
    
    if ($messages === false) {
        echo json_encode(['success' => false, 'error' => 'Failed to fetch messages']);
        exit;
    }
    
    echo json_encode(['success' => true, 'messages' => $messages]);
    exit;
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    exit;
}
?>
<parameter name="filePath">c:\xampp\htdocs\a\api\contact\get_messages.php