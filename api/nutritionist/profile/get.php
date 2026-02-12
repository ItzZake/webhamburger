<?php
require_once __DIR__ . '/../../../DB.php';
require_once __DIR__ . '/../../helpers/auth.php';
header('Content-Type: application/json');

require_role(['doctor','nutritionist','admin']);

$nutritionistId = current_user_id();

// Get nutritionist profile with user details
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
    np.Bio,
    np.Certifications,
    np.rating_count,
    np.Avg_rating,
    np.Is_accepting_new,
    np.Years_Experience,
    np.Specialization_Main,
    np.Clinic_Location,
    np.Licence_Number,
    np.Updated_at AS Profile_Updated_At,
    np.Created_at AS Profile_Created_At
FROM userprofile u
LEFT JOIN nutritionistprofile np ON np.Nutritionist_ID = u.User_ID AND np.is_deleted = 0
WHERE u.User_ID = ? AND u.is_deleted = 0 AND u.Role IN ('doctor', 'nutritionist', 'admin')";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(['error' => 'Database error: ' . $conn->error]);
    exit;
}

$stmt->bind_param('i', $nutritionistId);
$stmt->execute();
$result = $stmt->get_result();
$nutritionist = $result->fetch_assoc();
$stmt->close();

if (!$nutritionist) {
    echo json_encode(['error' => 'Nutritionist profile not found']);
    exit;
}

// Get member count (members who have meal plans with this nutritionist)
$memberCountSql = "SELECT COUNT(DISTINCT mp.Member_Id) AS count 
                   FROM mealplan mp
                   JOIN memberprofile m ON mp.Member_Id = m.Member_Id
                   JOIN userprofile u ON m.Member_Id = u.User_ID
                   WHERE mp.Nutritionist_ID = ? AND mp.is_deleted = 0 AND m.is_deleted = 0 AND u.is_deleted = 0";
$stmt = $conn->prepare($memberCountSql);
$stmt->bind_param('i', $nutritionistId);
$stmt->execute();
$memberCount = $stmt->get_result()->fetch_assoc()['count'];
$stmt->close();

// Get active meal plans count
$planCountSql = "SELECT COUNT(*) AS count 
                 FROM mealplan 
                 WHERE Nutritionist_ID = ? AND Status = 'Active' AND is_deleted = 0";
$stmt = $conn->prepare($planCountSql);
$stmt->bind_param('i', $nutritionistId);
$stmt->execute();
$planCount = $stmt->get_result()->fetch_assoc()['count'];
$stmt->close();

$nutritionist['member_count'] = (int)$memberCount;
$nutritionist['active_plans_count'] = (int)$planCount;

echo json_encode($nutritionist);
?>

