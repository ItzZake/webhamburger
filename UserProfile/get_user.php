<?php
include("../DB.php");
session_start();

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    echo json_encode(null);
    exit;
}

$stmt = $conn->prepare("SELECT User_ID, Email, First_Name, Last_Name, Phone_Number FROM UserProfile WHERE User_ID = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = null;
if ($row = $result->fetch_assoc()) {
    // Get membership subscription details
    $membership = 'None';
    $subStart = null;
    $subEnd = null;
    $isFrozen = false;
    $subscriptionId = null;
    
    $stmt_mem = $conn->prepare("SELECT ms.Subscription_ID, ms.Start_Date, ms.End_Date, ms.Is_Frozen, mp.Name 
                                 FROM membershipsubscription ms 
                                 JOIN membershipplan mp ON ms.Plan_ID = mp.Plan_ID 
                                 WHERE ms.Member_Id = ? AND ms.Status = 1 AND ms.is_deleted = 0 
                                 ORDER BY ms.Created_at DESC LIMIT 1");
    $stmt_mem->bind_param("i", $user_id);
    $stmt_mem->execute();
    $result_mem = $stmt_mem->get_result();
    if ($row_mem = $result_mem->fetch_assoc()) {
        $membership = $row_mem['Name'];
        $subStart = $row_mem['Start_Date'];
        $subEnd = $row_mem['End_Date'];
        $isFrozen = (int)$row_mem['Is_Frozen'] === 1;
        $subscriptionId = $row_mem['Subscription_ID'];
    }
    $stmt_mem->close();

    $user = [
        'gymCode' => $row['User_ID'],
        'name' => trim($row['First_Name'] . ' ' . $row['Last_Name']),
        'email' => $row['Email'],
        'phone' => $row['Phone_Number'],
        'addresses' => [],
        'subscription' => [
            'id' => $subscriptionId,
            'type' => $membership,
            'start' => $subStart ? date('c', strtotime($subStart)) : null,
            'end' => $subEnd ? date('c', strtotime($subEnd)) : null,
            'frozen' => $isFrozen
        ],
        'profileUrl' => 'http://localhost/NAV/UserProfile/userprofile.php'
    ];
}
$stmt->close();

// Fetch addresses
if ($user) {
    $stmt2 = $conn->prepare("SELECT Label, Full_Name, Address_line1, City, Governorate FROM Address WHERE Member_Id = ?");
    $stmt2->bind_param("i", $user_id);
    $stmt2->execute();
    $result2 = $stmt2->get_result();
    while ($row2 = $result2->fetch_assoc()) {
        $user['addresses'][] = $row2['Label'] . ' — ' . $row2['Full_Name'] . ', ' . $row2['Address_line1'] . ', ' . $row2['City'] . ', ' . $row2['Governorate'];
    }
    $stmt2->close();
}

echo json_encode($user);
?>