<?php
require_once __DIR__ . '/../../../DB.php';
require_once __DIR__ . '/../../helpers/auth.php';
header('Content-Type: application/json');

require_role(['coach','admin']);

$raw = file_get_contents('php://input');
$data = json_decode($raw, true) ?: $_POST;
if (empty($data['id'])) { echo json_encode(['error'=>'id required']); exit; }
$id = (int)$data['id'];

// If coach, ensure they own the workout
if (is_coach()) {
    $stmt = $conn->prepare("SELECT Coach_Id FROM workout WHERE Workout_ID = ?");
    $stmt->bind_param('i', $id); $stmt->execute(); $r = $stmt->get_result()->fetch_assoc(); $stmt->close();
    if (!$r || (int)$r['Coach_Id'] !== current_user_id()) {
        echo json_encode(['error'=>'forbidden']); exit;
    }
}

$stmt = $conn->prepare("UPDATE workout SET is_deleted = 1 WHERE Workout_ID = ?");
$stmt->bind_param('i', $id);
if (!$stmt->execute()) { echo json_encode(['error'=>$stmt->error]); exit; }

echo json_encode(['deleted'=>true]);

?>
