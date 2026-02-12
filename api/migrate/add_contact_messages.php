<?php
require_once __DIR__ . '/../../DB.php';
header('Content-Type: application/json');

$queries = [];
$queries[] = "ALTER TABLE AdminProfile ADD COLUMN Can_Receive_Contact_Messages INT NOT NULL DEFAULT 0";

$queries[] = "CREATE TABLE IF NOT EXISTS ContactMessage (
    Contact_ID INT AUTO_INCREMENT PRIMARY KEY,
    Full_Name VARCHAR(100) NOT NULL,
    Email VARCHAR(100) NOT NULL,
    Message TEXT NOT NULL,
    Submitted_At DATETIME DEFAULT CURRENT_TIMESTAMP,
    Is_Read INT DEFAULT 0,
    Read_At DATETIME NULL,
    Admin_ID INT NULL,
    FOREIGN KEY (Admin_ID) REFERENCES AdminProfile(Admin_ID)
)";

foreach ($queries as $query) {
    $result = $conn->query($query);
    if (!$result) {
        echo json_encode(['error' => 'Query failed: ' . $conn->error . ' for query: ' . $query]);
        exit;
    }
}

echo json_encode(['success' => 'Contact message schema updated']);
?></content>
<parameter name="filePath">c:\xampp\htdocs\a\api\migrate\add_contact_messages.php