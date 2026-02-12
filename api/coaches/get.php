<?php
ob_start(); // Start output buffering
ini_set('display_errors', 0); // Suppress error display
error_reporting(E_ALL); // But still log errors

require_once __DIR__ . '/../../DB.php';
header('Content-Type: application/json');

// Return coaches with joined user data. If a coach is missing a userprofile row,
// insert a minimal userprofile record with the same id to satisfy relations.
// This keeps responses consistent for the frontend.

$out = [];
$sql = "SELECT cp.Coach_ID AS id, u.User_ID AS user_id, u.First_Name, u.Last_Name, u.Profile_pic_url AS img, cp.Bio AS bio, cp.Specialization_Main AS specialty, cp.Is_Accepting_new AS accepting FROM coachprofile cp LEFT JOIN userprofile u ON cp.Coach_ID = u.User_ID WHERE (cp.is_deleted = 0 OR cp.is_deleted IS NULL) AND (u.is_deleted = 0 OR u.is_deleted IS NULL)";
$res = $conn->query($sql);
if (!$res) {
    ob_clean(); // Clear any buffered output
    echo json_encode(['error' => $conn->error]);
    exit;
}

while ($r = $res->fetch_assoc()) {
    // If there's no user record, insert a minimal one using the Coach_ID as User_ID
    if (empty($r['user_id'])) {
        $coachId = (int)$r['id'];
        $email = "coach{$coachId}@local.invalid";
        $first = 'Coach';
        $last = (string)$coachId;
        $now = date('Y-m-d H:i:s');
        $ins = $conn->prepare("INSERT INTO userprofile (User_ID, Email, Password, Last_Login, First_Name, Last_Name, Phone_Number, DOB, Role, Gender, Is_Active, Profile_pic_url, Created_at, Updated_at, is_deleted) VALUES (?, ?, '', '0000-00-00', ?, ?, 0, '0000-00-00', 'coach', '', 1, '', ?, ?, 0)");
        if ($ins) {
            $ins->bind_param('issss', $coachId, $email, $first, $last, $now, $now);
            $ins->execute();
            $ins->close();
        }
        // re-run a small select to get the inserted info
        $q = $conn->prepare("SELECT User_ID AS user_id, First_Name, Last_Name, Profile_pic_url FROM userprofile WHERE User_ID = ?");
        if ($q) {
            $q->bind_param('i', $coachId);
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
        'name' => trim(($r['First_Name'] ?? '') . ' ' . ($r['Last_Name'] ?? '')) ?: "Coach {$r['id']}",
        'img' => $r['img'] ?? '',
        'bio' => $r['bio'] ?? '',
        'specialty' => $r['specialty'] ?? '',
        'accepting' => isset($r['accepting']) ? (int)$r['accepting'] : 0,
    ];
}

ob_clean(); // Clear any buffered output before sending JSON
echo json_encode($out);
ob_end_flush(); // Send the output
?>