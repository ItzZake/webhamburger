<?php
require_once __DIR__ . '/../../../DB.php';
require_once __DIR__ . '/../../helpers/auth.php';
header('Content-Type: application/json');

require_role(['coach','admin','doctor','nutritionist']);

// Coaches should only see their assigned members (members who have programs with their Coach_ID).
// Admins and doctors/nutritionists can see all members.
if (is_coach()) {
    $coachId = current_user_id();
    // Only show members with Active or Pending programs (not Inactive)
    $sql = "SELECT DISTINCT m.Member_Id, u.First_Name, u.Last_Name, u.Email, m.Training_Goals
            FROM workoutprogram wp
            JOIN memberprofile m ON wp.Member_Id = m.Member_Id
            JOIN userprofile u ON m.Member_Id = u.User_ID
            WHERE wp.Coach_ID = ? 
              AND wp.Status IN ('Active', 'Pending')
              AND wp.is_deleted = 0 
              AND m.is_deleted = 0 
              AND u.is_deleted = 0
            ORDER BY u.First_Name, u.Last_Name";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $coachId);
    $stmt->execute();
    $res = $stmt->get_result();
    $rows = [];
    while ($r = $res->fetch_assoc()) $rows[] = $r;
    echo json_encode($rows);
    exit;
}

$sql = "SELECT m.Member_Id, u.First_Name, u.Last_Name, u.Email, m.Training_Goals
        FROM memberprofile m
        JOIN userprofile u ON m.Member_Id = u.User_ID
        WHERE m.is_deleted = 0 AND u.is_deleted = 0
        ORDER BY u.First_Name, u.Last_Name";
$res = $conn->query($sql);
$rows = [];
while ($r = $res->fetch_assoc()) $rows[] = $r;

echo json_encode($rows);
