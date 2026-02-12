<?php
require_once __DIR__ . '/../../DB.php';
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON']);
    exit;
}

$fullName = trim($input['fullName'] ?? '');
$email = trim($input['email'] ?? '');
$message = trim($input['message'] ?? '');

if (!$fullName || !$email || !$message) {
    http_response_code(400);
    echo json_encode(['error' => 'All fields are required']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid email format']);
    exit;
}

// Insert the contact message
$data = [
    'Full_Name' => $fullName,
    'Email' => $email,
    'Message' => $message
];

$result = insert('ContactMessage', $data);

if ($result) {
    echo json_encode(['success' => true, 'message' => 'Message sent successfully']);
    exit;
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to save message']);
    exit;
}
?>
<parameter name="filePath">c:\xampp\htdocs\a\api\contact\submit.php