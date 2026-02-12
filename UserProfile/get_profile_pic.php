<?php
include("../DB.php");
session_start();

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    echo json_encode(['url' => '']);
    exit;
}

$stmt = $conn->prepare("SELECT Profile_pic_url FROM UserProfile WHERE User_ID = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$url = '';
if ($row = $result->fetch_assoc()) {
    $url = $row['Profile_pic_url'];
}
$stmt->close();

echo json_encode(['url' => $url]);
?>