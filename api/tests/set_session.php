<?php
// Test helper: set session vars via HTTP (use only in local dev)
if (php_sapi_name() === 'cli') { echo "Use via HTTP\n"; exit; }
header('Content-Type: application/json');
session_start();
$raw = file_get_contents('php://input');
$data = json_decode($raw, true) ?: $_POST;
if (isset($data['role'])) $_SESSION['role'] = $data['role'];
if (isset($data['Member_Id'])) $_SESSION['Member_Id'] = (int)$data['Member_Id'];
echo json_encode(['ok' => true, 'session_id' => session_id(), 'role' => $_SESSION['role'] ?? null, 'Member_Id' => $_SESSION['Member_Id'] ?? null]);

?>
