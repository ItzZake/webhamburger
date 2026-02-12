<?php
require_once __DIR__ . '/../../DB.php';
require_once __DIR__ . '/../cart/helpers.php';
header('Content-Type: application/json');

// Get all members (users who have a MemberProfile)
$query = "SELECT DISTINCT u.User_ID, u.First_Name, u.Last_Name, u.Email 
          FROM UserProfile u 
          INNER JOIN MemberProfile m ON m.Member_Id = u.User_ID 
          WHERE (u.is_deleted = 0 OR u.is_deleted IS NULL)
          ORDER BY u.User_ID";

$result = $conn->query($query);

if (!$result) {
    echo json_encode([
        'success' => false,
        'error' => 'Failed to fetch members: ' . $conn->error
    ]);
    exit;
}

$results = [
    'success' => true,
    'total_members' => 0,
    'carts_created' => 0,
    'carts_existed' => 0,
    'errors' => [],
    'details' => []
];

while ($row = $result->fetch_assoc()) {
    $memberId = (int)$row['User_ID'];
    $results['total_members']++;
    
    // Check if member already has an active cart
    $checkStmt = $conn->prepare("SELECT Cart_ID FROM cart WHERE Member_Id = ? AND Status = 0 ORDER BY Created_at DESC LIMIT 1");
    $checkStmt->bind_param('i', $memberId);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows > 0) {
        // Member already has an active cart
        $cartRow = $checkResult->fetch_assoc();
        $results['carts_existed']++;
        $results['details'][] = [
            'member_id' => $memberId,
            'name' => $row['First_Name'] . ' ' . $row['Last_Name'],
            'email' => $row['Email'],
            'action' => 'cart_already_exists',
            'cart_id' => (int)$cartRow['Cart_ID']
        ];
    } else {
        // Create cart for member
        $cartId = createCartForUser($memberId, $conn);
        
        if ($cartId) {
            $results['carts_created']++;
            $results['details'][] = [
                'member_id' => $memberId,
                'name' => $row['First_Name'] . ' ' . $row['Last_Name'],
                'email' => $row['Email'],
                'action' => 'cart_created',
                'cart_id' => $cartId
            ];
        } else {
            $results['errors'][] = [
                'member_id' => $memberId,
                'name' => $row['First_Name'] . ' ' . $row['Last_Name'],
                'email' => $row['Email'],
                'error' => 'Failed to create cart'
            ];
        }
    }
    
    $checkStmt->close();
}

echo json_encode($results, JSON_PRETTY_PRINT);
?>

