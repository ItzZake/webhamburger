<?php
require_once __DIR__ . '/../../DB.php';
header('Content-Type: application/json');

$queries = [];
$queries[] = "CREATE TABLE IF NOT EXISTS workout (
    Workout_ID INT AUTO_INCREMENT PRIMARY KEY,
    Title VARCHAR(255) NOT NULL,
    Description TEXT,
    Duration_Minutes INT DEFAULT 0,
    Difficulty VARCHAR(50),
    Coach_Id INT DEFAULT NULL,
    Created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    Updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    is_deleted TINYINT(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

$queries[] = "CREATE TABLE IF NOT EXISTS meal (
    Meal_ID INT AUTO_INCREMENT PRIMARY KEY,
    Name VARCHAR(255) NOT NULL,
    Description TEXT,
    Calories INT DEFAULT 0,
    Protein FLOAT DEFAULT 0,
    Carbs FLOAT DEFAULT 0,
    Fat FLOAT DEFAULT 0,
    Doctor_Id INT DEFAULT NULL,
    Created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    Updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    is_deleted TINYINT(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

$results = [];
foreach ($queries as $q) {
    $ok = $conn->query($q);
    $results[] = ['query' => $q, 'ok' => (bool)$ok, 'error' => $ok ? '' : $conn->error];
}

echo json_encode($results);

?>
