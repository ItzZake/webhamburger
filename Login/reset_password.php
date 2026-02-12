<?php
include '../DB.php';
// simple reset endpoint (note: in production verify ownership via email/token)
header('Content-Type: application/json');
try {
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);
    if (!is_array($data)) {
        echo json_encode(['success' => false, 'error' => 'Invalid JSON']);
        exit;
    }
    $identifier = trim($data['identifier'] ?? '');
    $newpass = $data['newpass'] ?? '';
    if ($identifier === '' || strlen($newpass) < 6) {
        echo json_encode(['success' => false, 'error' => 'Invalid input']);
        exit;
    }

    // Determine if identifier is numeric (User_ID/gym code) or email
    $userId = null;
    if (ctype_digit($identifier)) {
        $userId = intval($identifier);
    }

    if ($userId) {
        $stmt = $conn->prepare("SELECT User_ID FROM UserProfile WHERE User_ID = ?");
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($row = $res->fetch_assoc()) {
            // ok
        } else {
            echo json_encode(['success' => false, 'error' => 'User not found by id']);
            exit;
        }
        $stmt->close();
    } else {
        // search by email
        $email = $identifier;
        $stmt = $conn->prepare("SELECT User_ID FROM UserProfile WHERE Email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($row = $res->fetch_assoc()) {
            $userId = intval($row['User_ID']);
        } else {
            echo json_encode(['success' => false, 'error' => 'User not found by email']);
            exit;
        }
        $stmt->close();
    }

    // Update password
    $hash = password_hash($newpass, PASSWORD_DEFAULT);
    $up = $conn->prepare("UPDATE UserProfile SET Password = ? WHERE User_ID = ?");
    if (!$up) {
        echo json_encode(['success' => false, 'error' => 'Prepare failed', 'db_error' => $conn->error]);
        exit;
    }
    $up->bind_param('si', $hash, $userId);
    if (!$up->execute()) {
        echo json_encode(['success' => false, 'error' => 'Execute failed', 'db_error' => $up->error]);
        $up->close();
        exit;
    }
    $up->close();

    echo json_encode(['success' => true]);
} catch (Throwable $e) {
    echo json_encode(['success' => false, 'error' => 'Server error', 'exception' => $e->getMessage()]);
}
?>