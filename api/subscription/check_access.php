<?php
require_once __DIR__ . '/../../DB.php';
require_once __DIR__ . '/../helpers/auth.php';
header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$user_id = current_user_id();
if (!$user_id) {
    echo json_encode([
        'has_access' => false,
        'coach_access' => false,
        'nutritionist_access' => false,
        'subscription' => null,
        'message' => 'Not logged in'
    ]);
    exit;
}

// Get current active subscription
$sql = "SELECT ms.Subscription_ID, ms.Plan_ID, ms.Status, ms.Start_Date, ms.End_Date,
               mp.Name, mp.Tier, mp.Coach_Access, mp.Nutritionist_Access
        FROM MembershipSubscription ms
        JOIN MembershipPlan mp ON ms.Plan_ID = mp.Plan_ID
        WHERE ms.Member_Id = ? AND ms.Status = 1 AND ms.is_deleted = 0
        ORDER BY ms.Created_at DESC
        LIMIT 1";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode([
        'has_access' => false,
        'coach_access' => false,
        'nutritionist_access' => false,
        'subscription' => null,
        'error' => 'Database error: ' . $conn->error
    ]);
    exit;
}

$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
$subscription = $result->fetch_assoc();
$stmt->close();

if (!$subscription) {
    echo json_encode([
        'has_access' => false,
        'coach_access' => false,
        'nutritionist_access' => false,
        'subscription' => null,
        'message' => 'No active subscription found'
    ]);
    exit;
}

$coach_access = (int)$subscription['Coach_Access'] === 1;
$nutritionist_access = (int)$subscription['Nutritionist_Access'] === 1;

echo json_encode([
    'has_access' => true,
    'coach_access' => $coach_access,
    'nutritionist_access' => $nutritionist_access,
    'subscription' => [
        'plan_id' => (int)$subscription['Plan_ID'],
        'name' => $subscription['Name'],
        'tier' => $subscription['Tier'],
        'start_date' => $subscription['Start_Date'],
        'end_date' => $subscription['End_Date']
    ]
]);
?>

