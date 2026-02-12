<?php
require_once __DIR__ . '/../../../DB.php';
require_once __DIR__ . '/../../helpers/auth.php';
header('Content-Type: application/json');

require_role(['coach','admin']);

$workout_id = isset($_GET['workout_id']) ? (int)$_GET['workout_id'] : null;
$where = null;
if ($workout_id) $where = ['Workout_ID' => $workout_id];
$res = select('workoutexercise', '*', $where);
echo json_encode($res);
?>