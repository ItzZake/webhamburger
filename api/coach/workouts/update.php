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

$fields = [];
foreach (['title','description','duration','difficulty'] as $k) if (isset($data[$k])) $fields[$k]=$data[$k];
if (empty($fields)) { echo json_encode(['error'=>'no fields']); exit; }

$sets=[];$vals=[];$types='';
if (isset($fields['title'])) { $sets[]='Title=?'; $vals[]=$fields['title']; }
if (isset($fields['description'])) { $sets[]='Description=?'; $vals[]=$fields['description']; }
if (isset($fields['duration'])) { $sets[]='Duration_Minutes=?'; $vals[]=(int)$fields['duration']; }
if (isset($fields['difficulty'])) { $sets[]='Difficulty=?'; $vals[]=$fields['difficulty']; }

$sql = "UPDATE workout SET " . implode(', ', $sets) . " WHERE Workout_ID = ?";
$vals[] = $id;
foreach ($vals as $v) { $types .= (is_int($v)?'i':(is_float($v)?'d':'s')); }
$stmt = $conn->prepare($sql); if (!$stmt) { echo json_encode(['error'=>$conn->error]); exit; }
$stmt->bind_param($types, ...$vals);
if (!$stmt->execute()) { echo json_encode(['error'=>$stmt->error]); exit; }

echo json_encode(['updated'=>true]);

?>
