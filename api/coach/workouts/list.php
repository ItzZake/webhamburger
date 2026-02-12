<?php
// Suppress warnings/notices that might output HTML
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Start output buffering to catch any unexpected output
ob_start();

require_once __DIR__ . '/../../../DB.php';
require_once __DIR__ . '/../../helpers/auth.php';

// Clear any output that might have been generated
ob_clean();

// Set JSON header
header('Content-Type: application/json');

require_role(['coach','admin']);

try {
    // Check if workout table exists, create it if it doesn't
    $tableCheck = $conn->query("SHOW TABLES LIKE 'workout'");
    if (!$tableCheck || $tableCheck->num_rows === 0) {
        // Auto-create the workout table
        $createTable = "CREATE TABLE IF NOT EXISTS workout (
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
        
        if (!$conn->query($createTable)) {
            echo json_encode(['error' => 'Failed to create workout table: ' . $conn->error]);
            exit;
        }
    }

    $sql = "SELECT Workout_ID, Title, Description, Duration_Minutes, Difficulty, Coach_Id, Created_at, Updated_at
            FROM workout
            WHERE is_deleted = 0";
    $params = [];
    $types = '';

    // Coaches should only see their own workouts
    if (is_coach()) {
        $coachId = current_user_id();
        if (!$coachId) {
            echo json_encode(['error' => 'Coach ID not found in session']);
            exit;
        }
        $sql .= " AND Coach_Id = ?";
        $params[] = $coachId;
        $types .= 'i';
    }

    $sql .= " ORDER BY Created_at DESC";

    if (empty($params)) {
        $res = $conn->query($sql);
        if (!$res) {
            echo json_encode(['error' => 'Database error: ' . $conn->error]);
            exit;
        }
        $rows = [];
        while ($r = $res->fetch_assoc()) $rows[] = $r;
    } else {
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            echo json_encode(['error' => 'Prepare failed: ' . $conn->error]);
            exit;
        }
        $stmt->bind_param($types, ...$params);
        if (!$stmt->execute()) {
            echo json_encode(['error' => 'Execute failed: ' . $stmt->error]);
            exit;
        }
        $result = $stmt->get_result();
        $rows = [];
        while ($r = $result->fetch_assoc()) $rows[] = $r;
        $stmt->close();
    }

    echo json_encode($rows);
} catch (Exception $e) {
    echo json_encode(['error' => 'Exception: ' . $e->getMessage()]);
} catch (Error $e) {
    echo json_encode(['error' => 'Fatal error: ' . $e->getMessage()]);
}

// End output buffering
ob_end_flush();
?>
