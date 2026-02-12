<?php
require_once __DIR__ . '/../../../DB.php';
require_once __DIR__ . '/../../helpers/auth.php';
header('Content-Type: application/json');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_role(['coach','admin']);

$raw = file_get_contents('php://input');
$data = json_decode($raw, true) ?: $_POST;

if (empty($data['member_id']) || empty($data['new_coach_id'])) {
    echo json_encode(['success' => false, 'error' => 'member_id and new_coach_id are required']);
    exit;
}

$memberId = (int)$data['member_id'];
$newCoachId = (int)$data['new_coach_id'];
$currentCoachId = current_user_id();

if (!$currentCoachId) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'User not authenticated']);
    exit;
}

// Verify the new coach exists
$coachCheck = $conn->prepare("SELECT Coach_ID FROM coachprofile WHERE Coach_ID = ? AND is_deleted = 0");
$coachCheck->bind_param('i', $newCoachId);
$coachCheck->execute();
$coachResult = $coachCheck->get_result();
if (!$coachResult->fetch_assoc()) {
    $coachCheck->close();
    echo json_encode(['success' => false, 'error' => 'New coach not found']);
    exit;
}
$coachCheck->close();

// Verify member exists
$memberCheck = $conn->prepare("SELECT Member_Id FROM memberprofile WHERE Member_Id = ? AND is_deleted = 0");
$memberCheck->bind_param('i', $memberId);
$memberCheck->execute();
$memberResult = $memberCheck->get_result();
if (!$memberResult->fetch_assoc()) {
    $memberCheck->close();
    echo json_encode(['success' => false, 'error' => 'Member not found']);
    exit;
}
$memberCheck->close();

// If coach (not admin), verify they own at least one program for this member
if (is_coach()) {
    // Check if coach has any programs for this member (matching the same logic as members/list.php)
    $ownershipCheck = $conn->prepare("SELECT wp.Workout_ID 
                                       FROM workoutprogram wp
                                       JOIN memberprofile m ON wp.Member_Id = m.Member_Id
                                       WHERE wp.Member_Id = ? AND wp.Coach_ID = ? AND wp.is_deleted = 0 AND m.is_deleted = 0
                                       LIMIT 1");
    if (!$ownershipCheck) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $conn->error]);
        exit;
    }
    $ownershipCheck->bind_param('ii', $memberId, $currentCoachId);
    $ownershipCheck->execute();
    $ownershipResult = $ownershipCheck->get_result();
    $ownedProgram = $ownershipResult->fetch_assoc();
    if (!$ownedProgram) {
        $ownershipCheck->close();
        // Debug: Check if member has any programs at all
        $debugCheck = $conn->prepare("SELECT COUNT(*) as count FROM workoutprogram WHERE Member_Id = ? AND is_deleted = 0");
        $debugCheck->bind_param('i', $memberId);
        $debugCheck->execute();
        $debugResult = $debugCheck->get_result();
        $debugRow = $debugResult->fetch_assoc();
        $debugCheck->close();
        
        http_response_code(403);
        echo json_encode([
            'success' => false, 
            'error' => 'You do not have permission to transfer this member. You must be the assigned coach for at least one of their workout programs.',
            'debug' => [
                'member_id' => $memberId,
                'current_coach_id' => $currentCoachId,
                'member_total_programs' => $debugRow['count'] ?? 0
            ]
        ]);
        exit;
    }
    $ownershipCheck->close();
}

// Prevent transferring to the same coach
if ($newCoachId == $currentCoachId && is_coach()) {
    echo json_encode(['success' => false, 'error' => 'Cannot transfer member to yourself']);
    exit;
}

// Start transaction to ensure atomicity
$conn->begin_transaction();

try {
    $now = date('Y-m-d H:i:s');
    
    // Get all workout programs for this member (regardless of current coach)
    $getProgramsStmt = $conn->prepare("SELECT Workout_ID, Coach_ID FROM workoutprogram 
                                        WHERE Member_Id = ? AND is_deleted = 0");
    $getProgramsStmt->bind_param('i', $memberId);
    $getProgramsStmt->execute();
    $programsResult = $getProgramsStmt->get_result();
    $programs = [];
    while ($row = $programsResult->fetch_assoc()) {
        $programs[] = $row;
    }
    $getProgramsStmt->close();
    
    if (empty($programs)) {
        throw new Exception('No workout programs found for this member');
    }
    
    // Update all workout programs to the new coach
    $updateStmt = $conn->prepare("UPDATE workoutprogram 
                                  SET Coach_ID = ?, Updated_at = ? 
                                  WHERE Member_Id = ? AND is_deleted = 0");
    $updateStmt->bind_param('isi', $newCoachId, $now, $memberId);
    
    if (!$updateStmt->execute()) {
        throw new Exception('Failed to update workout programs: ' . $updateStmt->error);
    }
    $updateStmt->close();
    
    // Commit transaction
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Member transferred successfully',
        'programs_updated' => count($programs)
    ]);
    
} catch (Exception $e) {
    $conn->rollback();
    error_log("Error transferring member: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Failed to transfer member: ' . $e->getMessage()
    ]);
}
?>

