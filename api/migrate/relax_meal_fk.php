<?php
require_once __DIR__ . '/../../DB.php';
header('Content-Type: application/json');

$results = [];
// drop foreign key if exists
$res = $conn->query("SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME='meal' AND CONSTRAINT_TYPE='FOREIGN KEY'");
while ($r = $res->fetch_assoc()) {
    $fk = $r['CONSTRAINT_NAME'];
    $ok = $conn->query("ALTER TABLE meal DROP FOREIGN KEY `" . $fk . "`");
    $results[] = ['drop_fk' => $fk, 'ok' => (bool)$ok, 'error' => $ok ? '' : $conn->error];
}

// set Meal_Plan_ID nullable
$ok = $conn->query("ALTER TABLE meal MODIFY Meal_Plan_ID INT DEFAULT NULL");
$results[] = ['modify_col' => 'Meal_Plan_ID NULL', 'ok' => (bool)$ok, 'error' => $ok ? '' : $conn->error];

echo json_encode($results);

?>
