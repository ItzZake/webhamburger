<?php
include("../DB.php");
session_start();

header('Content-Type: application/json');

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    echo json_encode(['success' => false, 'error' => 'User not authenticated']);
    exit;
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true) ?: $_POST;

if (empty($data['start_date']) || empty($data['end_date']) || empty($data['reason'])) {
    echo json_encode(['success' => false, 'error' => 'Start date, end date, and reason are required']);
    exit;
}

$startDate = $data['start_date'];
$endDate = $data['end_date'];
$reason = trim($data['reason']);

// Validate dates
if (strtotime($startDate) === false || strtotime($endDate) === false) {
    echo json_encode(['success' => false, 'error' => 'Invalid date format']);
    exit;
}

if (strtotime($startDate) > strtotime($endDate)) {
    echo json_encode(['success' => false, 'error' => 'Start date must be before end date']);
    exit;
}

// Get active subscription
$stmt = $conn->prepare("SELECT ms.Subscription_ID, ms.Member_Id, ms.Start_Date, ms.End_Date, ms.Is_Frozen, mp.Max_Freeze_Length_days, mp.Max_Freezes_Allowed 
                         FROM membershipsubscription ms 
                         JOIN membershipplan mp ON ms.Plan_ID = mp.Plan_ID 
                         WHERE ms.Member_Id = ? AND ms.Status = 1 AND ms.is_deleted = 0 
                         ORDER BY ms.Created_at DESC LIMIT 1");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$subscription = $result->fetch_assoc();
$stmt->close();

if (!$subscription) {
    echo json_encode(['success' => false, 'error' => 'No active subscription found']);
    exit;
}

// Check if already frozen
if ($subscription['Is_Frozen'] == 1) {
    echo json_encode(['success' => false, 'error' => 'Subscription is already frozen']);
    exit;
}

// Check freeze limits
$freezeLength = (strtotime($endDate) - strtotime($startDate)) / (60 * 60 * 24); // days
$maxFreezeLength = (int)$subscription['Max_Freeze_Length_days'];
$maxFreezes = (int)$subscription['Max_Freezes_Allowed'];

if ($freezeLength > $maxFreezeLength) {
    echo json_encode(['success' => false, 'error' => "Freeze period cannot exceed {$maxFreezeLength} days"]);
    exit;
}

// Count existing freezes for this subscription
$countStmt = $conn->prepare("SELECT COUNT(*) as freeze_count FROM membershipfreeze 
                             WHERE Subscription_ID = ? AND is_deleted = 0");
$countStmt->bind_param("i", $subscription['Subscription_ID']);
$countStmt->execute();
$countResult = $countStmt->get_result();
$freezeCount = $countResult->fetch_assoc()['freeze_count'];
$countStmt->close();

if ($freezeCount >= $maxFreezes) {
    echo json_encode(['success' => false, 'error' => "Maximum number of freezes ({$maxFreezes}) already used"]);
    exit;
}

// Validate freeze dates are within subscription period
$subStart = strtotime($subscription['Start_Date']);
$subEnd = strtotime($subscription['End_Date']);
$freezeStart = strtotime($startDate);
$freezeEnd = strtotime($endDate);

if ($freezeStart < $subStart || $freezeEnd > $subEnd) {
    echo json_encode(['success' => false, 'error' => 'Freeze period must be within subscription period']);
    exit;
}

// Start transaction
$conn->begin_transaction();

try {
    // Insert freeze record
    $insertStmt = $conn->prepare("INSERT INTO membershipfreeze 
                                   (Start_Date, End_Date, Actual_End_Date, Status, Reason, Created_at, Subscription_ID, Member_Id) 
                                   VALUES (?, ?, 0, 1, ?, NOW(), ?, ?)");
    $insertStmt->bind_param("sssii", $startDate, $endDate, $reason, $subscription['Subscription_ID'], $subscription['Member_Id']);
    $insertStmt->execute();
    $freezeId = $conn->insert_id;
    $insertStmt->close();

    // Update subscription to frozen
    $updateStmt = $conn->prepare("UPDATE membershipsubscription 
                                   SET Is_Frozen = 1, 
                                       Total_Frozen_Days = Total_Frozen_Days + ?, 
                                       Updated_at = NOW() 
                                   WHERE Subscription_ID = ?");
    $updateStmt->bind_param("ii", $freezeLength, $subscription['Subscription_ID']);
    $updateStmt->execute();
    $updateStmt->close();

    // Extend subscription end date by freeze length
    $extendStmt = $conn->prepare("UPDATE membershipsubscription 
                                   SET End_Date = DATE_ADD(End_Date, INTERVAL ? DAY), 
                                       Updated_at = NOW() 
                                   WHERE Subscription_ID = ?");
    $extendStmt->bind_param("ii", $freezeLength, $subscription['Subscription_ID']);
    $extendStmt->execute();
    $extendStmt->close();

    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Subscription frozen successfully', 'freeze_id' => $freezeId]);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'error' => 'Failed to freeze subscription: ' . $e->getMessage()]);
}
?>

