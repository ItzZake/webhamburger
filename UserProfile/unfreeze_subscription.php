<?php
include("../DB.php");
session_start();

header('Content-Type: application/json');

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    echo json_encode(['success' => false, 'error' => 'User not authenticated']);
    exit;
}

// Get active subscription
$stmt = $conn->prepare("SELECT ms.Subscription_ID, ms.Member_Id, ms.Is_Frozen 
                         FROM membershipsubscription ms 
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

// Check if subscription is frozen
if ($subscription['Is_Frozen'] == 0) {
    echo json_encode(['success' => false, 'error' => 'Subscription is not frozen']);
    exit;
}

// Get active freeze record
$freezeStmt = $conn->prepare("SELECT Freeze_ID, Start_Date, End_Date 
                              FROM membershipfreeze 
                              WHERE Subscription_ID = ? AND Status = 1 AND is_deleted = 0 
                              ORDER BY Created_at DESC LIMIT 1");
$freezeStmt->bind_param("i", $subscription['Subscription_ID']);
$freezeStmt->execute();
$freezeResult = $freezeStmt->get_result();
$freeze = $freezeResult->fetch_assoc();
$freezeStmt->close();

if (!$freeze) {
    echo json_encode(['success' => false, 'error' => 'No active freeze record found']);
    exit;
}

// Start transaction
$conn->begin_transaction();

try {
    // Update freeze status to inactive
    $updateFreezeStmt = $conn->prepare("UPDATE membershipfreeze 
                                        SET Status = 0, 
                                            Actual_End_Date = UNIX_TIMESTAMP(NOW()) 
                                        WHERE Freeze_ID = ?");
    $updateFreezeStmt->bind_param("i", $freeze['Freeze_ID']);
    $updateFreezeStmt->execute();
    $updateFreezeStmt->close();

    // Calculate actual freeze days
    $freezeStart = strtotime($freeze['Start_Date']);
    $actualEnd = time();
    $actualFreezeDays = max(0, floor(($actualEnd - $freezeStart) / (60 * 60 * 24)));

    // Update subscription to unfrozen
    $updateSubStmt = $conn->prepare("UPDATE membershipsubscription 
                                     SET Is_Frozen = 0, 
                                         Updated_at = NOW() 
                                     WHERE Subscription_ID = ?");
    $updateSubStmt->bind_param("i", $subscription['Subscription_ID']);
    $updateSubStmt->execute();
    $updateSubStmt->close();

    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Subscription unfrozen successfully']);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'error' => 'Failed to unfreeze subscription: ' . $e->getMessage()]);
}
?>

