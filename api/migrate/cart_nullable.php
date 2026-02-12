<?php
require_once __DIR__ . '/../../DB.php';
header('Content-Type: application/json');

$queries = [];
$queries[] = "ALTER TABLE cart MODIFY Member_Id INT(11) NULL DEFAULT NULL";
$queries[] = "ALTER TABLE cart MODIFY Order_ID INT(11) NULL DEFAULT NULL";

$results = [];
foreach ($queries as $q) {
    if ($conn->query($q) === TRUE) {
        $results[] = ['query' => $q, 'ok' => true];
    } else {
        $results[] = ['query' => $q, 'ok' => false, 'error' => $conn->error];
    }
}

echo json_encode($results);

?>
