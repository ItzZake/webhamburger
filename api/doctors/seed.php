<?php
require_once __DIR__ . '/../../DB.php';
header('Content-Type: application/json');

$dataFile = __DIR__ . '/../../Doctors/doctors.json';
if (!file_exists($dataFile)) {
    echo json_encode(['error' => 'doctors.json not found']);
    exit;
}
$raw = file_get_contents($dataFile);
$json = json_decode($raw, true);
if (!is_array($json)) {
    echo json_encode(['error' => 'invalid json']);
    exit;
}

// create table if not exists
$create = "CREATE TABLE IF NOT EXISTS doctor (
  Doctor_ID INT AUTO_INCREMENT PRIMARY KEY,
  Name VARCHAR(255) NOT NULL,
  Specialty VARCHAR(255),
  Bio TEXT,
  Img_url VARCHAR(1024),
  Rating FLOAT DEFAULT 0,
  Created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);";
$conn->query($create);

$inserted = 0;
foreach ($json as $d) {
    $name = $d['name'];
    $spec = isset($d['specialty']) ? $d['specialty'] : null;
    $bio = isset($d['bio']) ? $d['bio'] : null;
    $img = isset($d['img']) ? $d['img'] : null;
    $rating = isset($d['rating']) ? floatval($d['rating']) : 0;

    // skip if exists
    $stmt = $conn->prepare("SELECT Doctor_ID FROM doctor WHERE Name = ? LIMIT 1");
    $stmt->bind_param('s', $name);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->fetch_assoc()) { $stmt->close(); continue; }
    $stmt->close();

    $ins = $conn->prepare("INSERT INTO doctor (Name, Specialty, Bio, Img_url, Rating) VALUES (?, ?, ?, ?, ?)");
    $ins->bind_param('ssssd', $name, $spec, $bio, $img, $rating);
    if ($ins->execute()) $inserted++;
    $ins->close();
}

echo json_encode(['inserted' => $inserted]);

?>
