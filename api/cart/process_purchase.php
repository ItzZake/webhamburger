<?php
// Start output buffering to catch any unexpected output
ob_start();

// Suppress error display, but log them
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Suppress any output from required files
ob_start();
require_once __DIR__ . '/../../DB.php';
require_once __DIR__ . '/../helpers/auth.php';
ob_clean();

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set JSON header
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    echo json_encode(['success' => false, 'error' => 'User must be logged in to make a purchase']);
    exit;
}

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    echo json_encode(['success' => false, 'error' => 'User ID not found in session']);
    exit;
}

// Get request data
$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    ob_clean();
    echo json_encode(['success' => false, 'error' => 'Invalid JSON data: ' . json_last_error_msg()]);
    exit;
}

if (empty($data) || !is_array($data)) {
    ob_clean();
    echo json_encode(['success' => false, 'error' => 'No data provided']);
    exit;
}

if (empty($data['items']) || !is_array($data['items'])) {
    ob_clean();
    echo json_encode(['success' => false, 'error' => 'No items provided in request']);
    exit;
}

$items = $data['items'];
$memberships = [];
$products = [];

// Separate memberships from products
foreach ($items as $item) {
    if (isset($item['type']) && $item['type'] === 'membership') {
        $memberships[] = $item;
    } else {
        $products[] = $item;
    }
}

// Check database connection
if (!isset($conn) || !$conn) {
    ob_clean();
    echo json_encode(['success' => false, 'error' => 'Database connection object not available']);
    exit;
}

if ($conn->connect_error) {
    ob_clean();
    echo json_encode(['success' => false, 'error' => 'Database connection failed: ' . $conn->connect_error]);
    exit;
}

$conn->begin_transaction();

try {
    $errors = [];
    $processedMemberships = [];
    $needsProfileCompletion = false; // Initialize outside loop

    // Process memberships
    foreach ($memberships as $membership) {
        $planName = $membership['plan'] ?? '';
        $duration = $membership['duration'] ?? '1 Month';
        $planId = $membership['planId'] ?? null;

        // Map plan name to Plan_ID if not provided
        if (!$planId) {
            $planMap = ['Silver' => 1, 'Gold' => 2, 'Platinum' => 3];
            $planId = $planMap[$planName] ?? null;
        }

        if (!$planId) {
            $errors[] = "Invalid membership plan: {$planName}";
            continue;
        }

        // Get plan details
        $planStmt = $conn->prepare("SELECT Plan_ID, Duration, Price FROM MembershipPlan WHERE Plan_ID = ? AND Is_Active = 1");
        $planStmt->bind_param('i', $planId);
        $planStmt->execute();
        $planResult = $planStmt->get_result();
        $plan = $planResult->fetch_assoc();
        $planStmt->close();

        if (!$plan) {
            $errors[] = "Membership plan not found: {$planName}";
            continue;
        }

        // Calculate duration in days based on selected duration
        $durationDays = 30; // Default 1 month
        if (strpos($duration, '3') !== false || strpos($duration, 'Three') !== false) {
            $durationDays = 90;
        } elseif (strpos($duration, 'Year') !== false || strpos($duration, '12') !== false) {
            $durationDays = 365;
        }

        // Check if user exists in MemberProfile, create if not exists
        $memberCheck = $conn->prepare("SELECT Member_Id FROM MemberProfile WHERE Member_Id = ?");
        $memberCheck->bind_param('i', $user_id);
        $memberCheck->execute();
        $memberResult = $memberCheck->get_result();
        $memberExists = $memberResult->num_rows > 0;
        $memberCheck->close();
        
        if (!$memberExists) {
            // Ensure user role is set to 'Member' (capitalized to match login system)
            $roleCheck = $conn->prepare("UPDATE UserProfile SET Role = 'Member' WHERE User_ID = ?");
            $roleCheck->bind_param('i', $user_id);
            $roleCheck->execute();
            $roleCheck->close();
            
            // Create a basic MemberProfile with default values
            $now = date('Y-m-d H:i:s');
            $createMemberStmt = $conn->prepare("INSERT INTO MemberProfile 
                (Member_Id, Em_Contact_Num, EM_Contact_Name, Body_fat, Height, Weight, BMI, 
                 Experience_Level, Training_Goals, Injuries, Medical_Condition, Created_at, Updated_at) 
                VALUES (?, 0, '', 0, 0, 0, 0, 'Beginner', 'General fitness', '', '', ?, ?)");
            
            if (!$createMemberStmt) {
                throw new Exception("Failed to prepare member profile statement: " . $conn->error);
            }
            
            $createMemberStmt->bind_param('iss', $user_id, $now, $now);
            
            if (!$createMemberStmt->execute()) {
                $createMemberStmt->close();
                throw new Exception("Failed to create member profile: " . $conn->error);
            }
            
            $createMemberStmt->close();
            
            $needsProfileCompletion = true;
        } else {
            // Check if profile is incomplete (has default/empty values)
            $profileCheck = $conn->prepare("SELECT Height, Weight, Em_Contact_Num, EM_Contact_Name 
                                           FROM MemberProfile WHERE Member_Id = ?");
            $profileCheck->bind_param('i', $user_id);
            $profileCheck->execute();
            $profileData = $profileCheck->get_result()->fetch_assoc();
            $profileCheck->close();
            
            // If height/weight are 0 or emergency contact is empty, profile needs completion
            if (($profileData['Height'] == 0 && $profileData['Weight'] == 0) || 
                empty($profileData['EM_Contact_Name'])) {
                $needsProfileCompletion = true;
            }
        }

        // Deactivate any existing active subscriptions for this member
        $deactivateStmt = $conn->prepare("UPDATE MembershipSubscription SET Status = 0, Updated_at = NOW() WHERE Member_Id = ? AND Status = 1");
        if (!$deactivateStmt) {
            throw new Exception("Failed to prepare deactivate statement: " . $conn->error);
        }
        $deactivateStmt->bind_param('i', $user_id);
        if (!$deactivateStmt->execute()) {
            $deactivateStmt->close();
            throw new Exception("Failed to deactivate existing subscriptions: " . $conn->error);
        }
        $deactivateStmt->close();

        // Create new subscription
        $startDate = date('Y-m-d');
        $endDate = date('Y-m-d', strtotime("+{$durationDays} days"));

        // Check if Subscription_ID is AUTO_INCREMENT or needs to be provided
        $checkAuto = $conn->query("SHOW COLUMNS FROM MembershipSubscription WHERE Field = 'Subscription_ID'");
        $isAutoIncrement = false;
        if ($checkAuto && $row = $checkAuto->fetch_assoc()) {
            $isAutoIncrement = strpos(strtolower($row['Extra']), 'auto_increment') !== false;
        }

        // Use valid default values for required fields
        $cancelDate = '1970-01-01'; // Use a valid date instead of '0000-00-00'
        $cancelReason = '';
        
        // For Cancelled_by_User_ID and Cancelled_by_Admin_ID, use the current user_id
        // (they're required NOT NULL foreign keys, so we use the user's own ID)
        $cancelledByUserId = $user_id;
        $cancelledByAdminId = $user_id; // In a real system, this would be an admin ID
        
        if ($isAutoIncrement) {
            $insertStmt = $conn->prepare("INSERT INTO MembershipSubscription 
                (Member_Id, Plan_ID, Start_Date, End_Date, Status, Cancel_Date, Cancel_Reason, Is_Frozen, Total_Frozen_Days, Created_at, Updated_at, Cancelled_by_User_ID, Cancelled_by_Admin_ID) 
                VALUES (?, ?, ?, ?, 1, ?, ?, 0, 0, NOW(), NOW(), ?, ?)");
            if (!$insertStmt) {
                throw new Exception("Failed to prepare statement: " . $conn->error);
            }
            // Parameters: user_id(i), planId(i), startDate(s), endDate(s), cancelDate(s), cancelReason(s), cancelledByUserId(i), cancelledByAdminId(i)
            $insertStmt->bind_param('iissssii', $user_id, $planId, $startDate, $endDate, $cancelDate, $cancelReason, $cancelledByUserId, $cancelledByAdminId);
        } else {
            // Get next Subscription_ID
            $maxStmt = $conn->query("SELECT COALESCE(MAX(Subscription_ID), 0) + 1 AS next_id FROM MembershipSubscription");
            if (!$maxStmt) {
                throw new Exception("Failed to get next Subscription_ID: " . $conn->error);
            }
            $maxRow = $maxStmt->fetch_assoc();
            $nextId = $maxRow['next_id'];
            
            $insertStmt = $conn->prepare("INSERT INTO MembershipSubscription 
                (Subscription_ID, Member_Id, Plan_ID, Start_Date, End_Date, Status, Cancel_Date, Cancel_Reason, Is_Frozen, Total_Frozen_Days, Created_at, Updated_at, Cancelled_by_User_ID, Cancelled_by_Admin_ID) 
                VALUES (?, ?, ?, ?, 1, ?, ?, 0, 0, NOW(), NOW(), ?, ?)");
            if (!$insertStmt) {
                throw new Exception("Failed to prepare statement: " . $conn->error);
            }
            // Parameters: nextId(i), user_id(i), planId(i), startDate(s), endDate(s), cancelDate(s), cancelReason(s), cancelledByUserId(i), cancelledByAdminId(i)
            $insertStmt->bind_param('iiissssii', $nextId, $user_id, $planId, $startDate, $endDate, $cancelDate, $cancelReason, $cancelledByUserId, $cancelledByAdminId);
        }
        
        if (!$insertStmt->execute()) {
            $errorMsg = $insertStmt->error ?: $conn->error;
            throw new Exception("Failed to create subscription for {$planName}: " . $errorMsg);
        } else {
            $processedMemberships[] = [
                'plan' => $planName,
                'duration' => $duration,
                'start_date' => $startDate,
                'end_date' => $endDate
            ];
        }
        
        $insertStmt->close();
    }

    // Process products (for now, just log them - you can add order tracking later)
    // Products are already "purchased" since they're in the cart
    // You could create an Order table and OrderItems table here if needed

    if (!empty($errors)) {
        $conn->rollback();
        ob_clean();
        echo json_encode(['success' => false, 'error' => implode(', ', $errors)]);
        exit;
    }

    $conn->commit();

    // Update session to recognize user as a member
    // First, ensure the role in database is 'Member' (capitalized)
    $updateRoleStmt = $conn->prepare("UPDATE UserProfile SET Role = 'Member' WHERE User_ID = ?");
    $updateRoleStmt->bind_param('i', $user_id);
    $updateRoleStmt->execute();
    $updateRoleStmt->close();
    
    // Update session role (normalized to lowercase to match login system)
    $_SESSION['role'] = 'member';
    
    ob_clean();
    echo json_encode([
        'success' => true,
        'message' => 'Purchase completed successfully',
        'memberships' => $processedMemberships,
        'products_count' => count($products),
        'needs_profile_completion' => $needsProfileCompletion,
        'member_id' => $user_id
    ]);

} catch (Exception $e) {
    if (isset($conn) && $conn->in_transaction) {
        $conn->rollback();
    }
    $errorMsg = $e->getMessage();
    error_log("Purchase processing error: " . $errorMsg . " | Trace: " . $e->getTraceAsString());
    
    // Clear any output and return JSON error
    ob_clean();
    echo json_encode([
        'success' => false, 
        'error' => $errorMsg,
        'debug_info' => [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]
    ]);
} catch (Error $e) {
    // Catch PHP 7+ errors (TypeError, ParseError, etc.)
    if (isset($conn) && $conn->in_transaction) {
        $conn->rollback();
    }
    $errorMsg = $e->getMessage();
    error_log("Purchase processing fatal error: " . $errorMsg . " | Trace: " . $e->getTraceAsString());
    
    ob_clean();
    echo json_encode([
        'success' => false, 
        'error' => 'Fatal error: ' . $errorMsg,
        'debug_info' => [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]
    ]);
}
?>

