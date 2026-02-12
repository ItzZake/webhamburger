<?php
// Simple RBAC helper — relies on PHP session variables set by your auth flow
if (session_status() === PHP_SESSION_NONE) session_start();

function current_user_id() {
    // Support multiple session key names used across the app (Member_Id, user_id, id)
    $keys = ['Member_Id', 'MemberId', 'user_id', 'userId', 'id', 'User_Id', 'Doctor_Id', 'Coach_ID', 'Nutritionist_ID', 'NutritionistId', 'Nutritionist_Id', 'Nutritionist'];
    foreach ($keys as $k) {
        if (isset($_SESSION[$k])) return (int)$_SESSION[$k];
    }
    return null;
}

function current_user_role() {
    return isset($_SESSION['role']) ? $_SESSION['role'] : null;
}

function require_role($roles) {
    if (!is_array($roles)) $roles = [$roles];
    $role = current_user_role();
    if (!$role || !in_array($role, $roles)) {
        header('Content-Type: application/json', true, 403);
        echo json_encode(['error' => 'forbidden']);
        exit;
    }
}

function is_admin() {
    return current_user_role() === 'admin';
}

function is_coach() {
    return current_user_role() === 'coach';
}

function is_doctor_or_nutritionist() {
    $r = current_user_role();
    return $r === 'doctor' || $r === 'nutritionist';
}

?>
