<?php
require_once __DIR__ . '/../../DB.php';
require_once __DIR__ . '/../helpers/auth.php';
header('Content-Type: application/json');

// Return all members (for selects and admin tools)
require_role(['coach','admin','doctor','nutritionist']);

$sql = "SELECT m.Member_Id, u.First_Name, u.Last_Name, u.Email, m.Training_Goals
        FROM memberprofile m
        JOIN userprofile u ON m.Member_Id = u.User_ID
        WHERE m.is_deleted = 0 AND u.is_deleted = 0
        ORDER BY u.First_Name, u.Last_Name";

$res = $conn->query($sql);
$rows = [];
while ($r = $res->fetch_assoc()) $rows[] = $r;

echo json_encode($rows);
