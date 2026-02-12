<?php
require_once __DIR__ . '/../../../DB.php';
require_once __DIR__ . '/../../helpers/auth.php';
header('Content-Type: application/json');

require_role(['coach','admin']);

$raw = file_get_contents('php://input');
$data = json_decode($raw, true) ?: $_POST;

if (empty($data['title'])) { echo json_encode(['error'=>'title required']); exit; }

$coachId = current_user_id();
if (is_admin() && isset($data['coach_id'])) $coachId = (int)$data['coach_id'];

$ins = $conn->prepare("INSERT INTO workout (Title, Description, Duration_Minutes, Difficulty, Coach_Id) VALUES (?, ?, ?, ?, ?)");
$title = $data['title'];
$desc = isset($data['description']) ? $data['description'] : '';
$duration = isset($data['duration']) ? (int)$data['duration'] : 0;
$difficulty = isset($data['difficulty']) ? $data['difficulty'] : null;
$ins->bind_param('ssisi', $title, $desc, $duration, $difficulty, $coachId);
if (!$ins->execute()) { echo json_encode(['error'=>$ins->error]); exit; }

echo json_encode(['inserted'=>true, 'id' => $conn->insert_id]);

?>
