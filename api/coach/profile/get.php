<?php
require_once __DIR__ . '/../../../DB.php';
require_once __DIR__ . '/../../helpers/auth.php';
header('Content-Type: application/json');

require_role(['coach','admin']);

$coachId = current_user_id();

// Get coach profile with user details
$sql = "SELECT 
    u.User_ID,
    u.First_Name,
    u.Last_Name,
    u.Email,
    u.Phone_Number,
    u.DOB,
    u.Gender,
    u.Profile_pic_url,
    u.Last_Login,
    u.Created_at,
    cp.Bio,
    cp.Certifications,
    cp.rating_count,
    cp.Avg_rating,
    cp.Is_Accepting_new,
    cp.Max_Clients,
    cp.Specialization_Main,
    cp.Specialization_Other,
    cp.Youtube_Url,
    cp.Instagram_Url,
    cp.Created_At AS Profile_Created_At,
    cp.Updated_At AS Profile_Updated_At
FROM userprofile u
LEFT JOIN coachprofile cp ON cp.Coach_ID = u.User_ID AND cp.is_deleted = 0
WHERE u.User_ID = ? AND u.is_deleted = 0 AND u.Role IN ('coach', 'admin')";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(['error' => 'Database error: ' . $conn->error]);
    exit;
}

$stmt->bind_param('i', $coachId);
$stmt->execute();
$result = $stmt->get_result();
$coach = $result->fetch_assoc();
$stmt->close();

if (!$coach) {
    echo json_encode(['error' => 'Coach profile not found']);
    exit;
}

// Get member count (members who have workout programs with this coach)
$memberCountSql = "SELECT COUNT(DISTINCT wp.Member_Id) AS count 
                   FROM workoutprogram wp
                   JOIN memberprofile m ON wp.Member_Id = m.Member_Id
                   JOIN userprofile u ON m.Member_Id = u.User_ID
                   WHERE wp.Coach_ID = ? AND wp.is_deleted = 0 AND m.is_deleted = 0 AND u.is_deleted = 0";
$stmt = $conn->prepare($memberCountSql);
$stmt->bind_param('i', $coachId);
$stmt->execute();
$memberCount = $stmt->get_result()->fetch_assoc()['count'];
$stmt->close();

// Get active workout programs count
$programCountSql = "SELECT COUNT(*) AS count 
                    FROM workoutprogram 
                    WHERE Coach_ID = ? AND Status = 'Active' AND is_deleted = 0";
$stmt = $conn->prepare($programCountSql);
$stmt->bind_param('i', $coachId);
$stmt->execute();
$programCount = $stmt->get_result()->fetch_assoc()['count'];
$stmt->close();

$coach['member_count'] = (int)$memberCount;
$coach['active_programs_count'] = (int)$programCount;

echo json_encode($coach);
?>

