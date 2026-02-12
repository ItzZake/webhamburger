<?php
require_once __DIR__ . '/../helpers/auth.php';
header('Content-Type: application/json');

echo json_encode([
  'role' => current_user_role(),
  'id' => current_user_id()
]);
