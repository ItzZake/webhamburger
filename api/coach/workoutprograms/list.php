<?php
require_once __DIR__ . '/../../../DB.php';
require_once __DIR__ . '/../../helpers/auth.php';
header('Content-Type: application/json');

require_role(['coach','admin']);

$memberId = isset($_GET['member_id']) ? (int)$_GET['member_id'] : null;

$sql = "SELECT wp.*, u.First_Name AS Member_First, u.Last_Name AS Member_Last, cu.First_Name AS Coach_First, cu.Last_Name AS Coach_Last
        FROM workoutprogram wp
        LEFT JOIN userprofile u ON wp.Member_Id = u.User_ID
        LEFT JOIN userprofile cu ON wp.Coach_ID = cu.User_ID
        WHERE wp.is_deleted = 0";
$params = [];
$types = '';

// Coaches should only see their own programs
if (is_coach()) {
    $coachId = current_user_id();
    $sql .= " AND wp.Coach_ID = ?";
    $params[] = $coachId;
    $types .= 'i';
}

if ($memberId) {
    $sql .= " AND wp.Member_Id = ?";
    $params[] = $memberId;
    $types .= 'i';
}

$sql .= " ORDER BY wp.Created_at DESC";

if (empty($params)) {
    $res = $conn->query($sql);
    $rows = [];
    while ($r = $res->fetch_assoc()) $rows[] = $r;
} else {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

echo json_encode($rows);
