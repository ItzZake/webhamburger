<?php
// Dev helper: set a test session id and role for local development.
// Usage: /api/session/set_test.php?id=1&role=nutritionist
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json');

$id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$role = isset($_GET['role']) ? $_GET['role'] : null;
if (!$id || !$role) {
  echo json_encode(['error' => 'provide id and role in query string, e.g. ?id=1&role=nutritionist']);
  exit;
}
// Set multiple session keys to match helpers
$_SESSION['Member_Id'] = $id;
$_SESSION['user_id'] = $id;
$_SESSION['id'] = $id;
$_SESSION['Nutritionist_ID'] = $id;
$_SESSION['role'] = $role;

echo json_encode(['ok' => true, 'id' => $id, 'role' => $role]);
