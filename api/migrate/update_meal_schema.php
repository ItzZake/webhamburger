<?php
require_once __DIR__ . '/../../DB.php';
header('Content-Type: application/json');

$cols = [
    'Description' => 'TEXT',
    'Calories' => 'INT DEFAULT 0',
    'Protein' => 'FLOAT DEFAULT 0',
    'Carbs' => 'FLOAT DEFAULT 0',
    'Fat' => 'FLOAT DEFAULT 0',
    'Doctor_Id' => 'INT DEFAULT NULL'
];

$results = [];
foreach ($cols as $col => $def) {
    $check = $conn->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'meal' AND COLUMN_NAME = '" . $conn->real_escape_string($col) . "'");
    if ($check && $check->num_rows === 0) {
        $q = "ALTER TABLE meal ADD COLUMN $col $def";
        $ok = $conn->query($q);
        $results[] = ['query' => $q, 'ok' => (bool)$ok, 'error' => $ok ? '' : $conn->error];
    } else {
        $results[] = ['query' => 'exists ' . $col, 'ok' => true, 'error' => ''];
    }
}

echo json_encode($results);

?>
