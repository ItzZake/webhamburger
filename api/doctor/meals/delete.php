<?php
require_once __DIR__ . '/../../../DB.php';
require_once __DIR__ . '/../../helpers/auth.php';
header('Content-Type: application/json');

require_role(['doctor','nutritionist','admin']);

 $raw = file_get_contents('php://input');
 $data = json_decode($raw, true) ?: $_POST;
 if (empty($data['id'])) { http_response_code(400); echo json_encode(['error'=>'id required']); exit; }
 $id = (int)$data['id'];

 // fetch the row and check ownership
 $stmt = $conn->prepare("SELECT Doctor_Id, is_deleted FROM meal WHERE Meal_ID = ?");
 $stmt->bind_param('i', $id);
 if (!$stmt->execute()) { http_response_code(500); echo json_encode(['error'=>$stmt->error]); exit; }
 $r = $stmt->get_result()->fetch_assoc();
 $stmt->close();

 if (!$r) { http_response_code(404); echo json_encode(['error'=>'not found']); exit; }

 // allow idempotent delete: if already deleted, return success
 if ((int)$r['is_deleted'] === 1) { echo json_encode(['deleted'=>true,'note'=>'already deleted']); exit; }

 // ownership check for non-admins
 // Allow nutritionists to manage meals (bypass strict owner check)
 if (!is_admin() && current_user_role() !== 'nutritionist') {
    $owner = isset($r['Doctor_Id']) ? (int)$r['Doctor_Id'] : null;
    $you = current_user_id();
    if ($owner === null || $owner !== $you) {
        http_response_code(403);
        echo json_encode([
            'error' => 'forbidden',
            'owner' => $owner,
            'you' => $you,
            'role' => current_user_role()
        ]);
        exit;
    }
 }

 $stmt = $conn->prepare("UPDATE meal SET is_deleted = 1 WHERE Meal_ID = ?");
 $stmt->bind_param('i', $id);
 if (!$stmt->execute()) { http_response_code(500); echo json_encode(['error'=>$stmt->error]); exit; }

 echo json_encode(['deleted'=>true]);

?>
