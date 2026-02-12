<?php
ob_start(); // Start output buffering
ini_set('display_errors', 0); // Suppress error display
error_reporting(E_ALL); // But still log errors

require_once __DIR__ . '/../../DB.php';
header('Content-Type: application/json');

// Return doctors (nutritionists) from nutritionistprofile joined with userprofile.
// Insert missing userprofile rows if needed.

$out = [];
$sql = "SELECT np.Nutritionist_ID AS id, u.User_ID AS user_id, u.First_Name, u.Last_Name, u.Profile_pic_url AS img, np.Bio AS bio, np.Specialization_Main AS specialty FROM nutritionistprofile np LEFT JOIN userprofile u ON np.Nutritionist_ID = u.User_ID WHERE (np.is_deleted = 0 OR np.is_deleted IS NULL) AND (u.is_deleted = 0 OR u.is_deleted IS NULL)";
$res = $conn->query($sql);
if (!$res) {
    ob_clean(); // Clear any buffered output
    echo json_encode(['error' => $conn->error]);
    exit;
}

while ($r = $res->fetch_assoc()) {
    if (empty($r['user_id'])) {
        $nid = (int)$r['id'];
        $email = "nutritionist{$nid}@local.invalid";
        $first = 'Nutritionist';
        $last = (string)$nid;
        $now = date('Y-m-d H:i:s');
        $ins = $conn->prepare("INSERT INTO userprofile (User_ID, Email, Password, Last_Login, First_Name, Last_Name, Phone_Number, DOB, Role, Gender, Is_Active, Profile_pic_url, Created_at, Updated_at, is_deleted) VALUES (?, ?, '', '0000-00-00', ?, ?, 0, '0000-00-00', 'nutritionist', '', 1, '', ?, ?, 0)");
        if ($ins) {
            $ins->bind_param('issss', $nid, $email, $first, $last, $now, $now);
            $ins->execute();
            $ins->close();
        }
        $q = $conn->prepare("SELECT User_ID AS user_id, First_Name, Last_Name, Profile_pic_url FROM userprofile WHERE User_ID = ?");
        if ($q) {
            $q->bind_param('i', $nid);
            $q->execute();
            $res2 = $q->get_result();
            if ($row2 = $res2->fetch_assoc()) {
                $r['user_id'] = $row2['user_id'];
                $r['First_Name'] = $row2['First_Name'];
                $r['Last_Name'] = $row2['Last_Name'];
                $r['img'] = $row2['Profile_pic_url'];
            }
            $q->close();
        }
    }

    $out[] = [
        'id' => (int)$r['id'],
        'user_id' => isset($r['user_id']) ? (int)$r['user_id'] : null,
        'name' => trim(($r['First_Name'] ?? '') . ' ' . ($r['Last_Name'] ?? '')) ?: "Nutritionist {$r['id']}",
        'img' => $r['img'] ?? '',
        'bio' => $r['bio'] ?? '',
        'specialty' => $r['specialty'] ?? '',
    ];
}

ob_clean(); // Clear any buffered output before sending JSON
echo json_encode($out);
ob_end_flush(); // Send the output
?>