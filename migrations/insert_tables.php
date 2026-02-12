<?php
include 'DB.php'; // Include your database connection

// Disable foreign key checks
$conn->query("SET FOREIGN_KEY_CHECKS = 0");

// Path to the SQL file
$sqlFile = 'c:\Users\user\AppData\Local\Microsoft\Windows\INetCache\IE\FH7C9BM8\product[1].sql';

// Read the SQL file
$content = file_get_contents($sqlFile);

if ($content === false) {
    die("Error reading SQL file.\n");
}

// Remove comments and unnecessary lines
$content = preg_replace('/--.*$/m', '', $content); // Remove -- comments
$content = preg_replace('/\/\*.*?\*\//s', '', $content); // Remove /* */ comments
$content = preg_replace('/SET\s+[^;]+;/i', '', $content); // Remove SET statements
$content = preg_replace('/START\s+TRANSACTION;/i', '', $content);
$content = preg_replace('/COMMIT;/i', '', $content);

// Split into statements
$statements = array_filter(array_map('trim', explode(';', $content)));

foreach ($statements as $statement) {
    $statement = trim($statement);
    if (!empty($statement) && !preg_match('/^\/\*!/', $statement)) { // Skip version comments
        if ($conn->query($statement) === TRUE) {
            echo "Executed successfully.\n";
        } else {
            echo "Error: " . $conn->error . "\nStatement: " . substr($statement, 0, 100) . "\n";
        }
    }
}

// Re-enable foreign key checks
$conn->query("SET FOREIGN_KEY_CHECKS = 1");

$conn->close();
echo "Tables inserted successfully.\n";
?>