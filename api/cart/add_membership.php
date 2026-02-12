<?php
// Start output buffering to prevent HTML output on errors
ob_start();
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Register shutdown function to catch fatal errors
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        ob_clean();
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error' => 'Server error: ' . basename($error['file']) . ':' . $error['line'] . ' - ' . $error['message']
        ]);
        ob_end_flush();
        exit;
    }
});

try {
    require_once __DIR__ . '/../../DB.php';
    require_once __DIR__ . '/../helpers/auth.php';
} catch (Exception $e) {
    ob_clean();
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Failed to load required files: ' . $e->getMessage()]);
    exit;
}

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

ob_clean();
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    ob_clean();
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'User must be logged in']);
    exit;
}

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    ob_clean();
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'User ID not found in session']);
    exit;
}

// Get request data
$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    ob_clean();
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid JSON data']);
    exit;
}

$planName = $data['plan'] ?? '';
$duration = $data['duration'] ?? '1 Month';
$price = floatval($data['price'] ?? 0);

if (empty($planName) || $price <= 0) {
    ob_clean();
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid membership data']);
    exit;
}

// Get or create member profile
$memberCheck = $conn->prepare("SELECT Member_Id FROM MemberProfile WHERE Member_Id = ?");
$memberCheck->bind_param('i', $user_id);
$memberCheck->execute();
$memberExists = $memberCheck->get_result()->num_rows > 0;
$memberCheck->close();

if (!$memberExists) {
    // Create basic member profile
    $now = date('Y-m-d H:i:s');
    $createMember = $conn->prepare("INSERT INTO MemberProfile 
        (Member_Id, Em_Contact_Num, EM_Contact_Name, Body_fat, Height, Weight, BMI, 
         Experience_Level, Training_Goals, Injuries, Medical_Condition, Created_at, Updated_at) 
        VALUES (?, 0, '', 0, 0, 0, 0, 'Beginner', 'General fitness', '', '', ?, ?)");
    $createMember->bind_param('iss', $user_id, $now, $now);
    $createMember->execute();
    $createMember->close();
}

// Get or create order and cart (same as add_item.php)
require_once __DIR__ . '/helpers.php';
$orderAndCart = getOrCreateOrderAndCart($user_id, $conn);
if (!$orderAndCart) {
    ob_clean();
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Failed to get or create order and cart']);
    exit;
}

$orderId = $orderAndCart['order_id'];
$cartId = $orderAndCart['cart_id'];

// Store membership in cart metadata (we'll use a JSON field or store in session/localStorage for now)
// Since CartItem requires Product_ID, we'll store membership info separately
// For now, return success with membership data to be stored client-side
// In a full implementation, you might want to add a CartMembership table

ob_clean();
echo json_encode([
    'success' => true,
    'cart_id' => $cartId,
    'order_id' => $orderId,
    'membership' => [
        'plan' => $planName,
        'duration' => $duration,
        'price' => $price
    ]
]);
?>

