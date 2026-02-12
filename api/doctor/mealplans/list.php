<?php
require_once __DIR__ . '/../../../DB.php';
require_once __DIR__ . '/../../helpers/auth.php';
header('Content-Type: application/json');

require_role(['doctor','nutritionist','admin']);

$memberId = isset($_GET['member_id']) ? (int)$_GET['member_id'] : null;
if (!$memberId) { echo json_encode([]); exit; }

$sql = "SELECT mp.*, u.First_Name AS Nutritionist_First, u.Last_Name AS Nutritionist_Last
        FROM mealplan mp
        LEFT JOIN nutritionistprofile np ON mp.Nutritionist_ID = np.Nutritionist_ID
        LEFT JOIN userprofile u ON np.Nutritionist_ID = u.User_ID
        WHERE mp.Member_Id = ? AND mp.is_deleted = 0";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $memberId);
$stmt->execute();
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

echo json_encode($rows);
