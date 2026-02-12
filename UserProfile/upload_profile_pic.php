<?php
include("../DB.php");
session_start();

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    echo json_encode(['success' => false]);
    exit;
}

if (!isset($_FILES['profile_pic'])) {
    echo json_encode(['success' => false]);
    exit;
}

$file = $_FILES['profile_pic'];
$uploadDir = 'images/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

$filename = 'profile_' . $user_id . '_' . time() . '.' . pathinfo($file['name'], PATHINFO_EXTENSION);
$filepath = $uploadDir . $filename;

if (move_uploaded_file($file['tmp_name'], $filepath)) {
    $url = $filepath;
    $stmt = $conn->prepare("UPDATE UserProfile SET Profile_pic_url = ? WHERE User_ID = ?");
    $stmt->bind_param("si", $url, $user_id);
    $success = $stmt->execute();
    $stmt->close();
    echo json_encode(['success' => $success, 'url' => $url]);
} else {
    echo json_encode(['success' => false]);
}
?>