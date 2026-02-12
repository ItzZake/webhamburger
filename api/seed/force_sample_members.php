<?php
require_once __DIR__ . '/../../DB.php';
header('Content-Type: application/json');

$created = [];
$now = date('Y-m-d H:i:s');
$coachId = 3; // default coach owner for sample workouts

for ($i=1;$i<=5;$i++){
    $token = time() . '_' . $i;
    $email = "test_member_{$token}@local";
    $first = "Test{$i}";
    $last = "Member";
    $ins = $conn->prepare("INSERT INTO userprofile (Email, Password, Last_Login, First_Name, Last_Name, Phone_Number, DOB, Role, Gender, Is_Active, Profile_pic_url, Created_at, Updated_at) VALUES (?, '', '0000-00-00', ?, ?, 0, '0000-00-00', 'member', '', 1, '', ?, ?)");
    if (!$ins) { echo json_encode(['error' => $conn->error]); exit; }
    $ins->bind_param('sssss', $email, $first, $last, $now, $now);
    $ins->execute();
    $uid = $ins->insert_id;
    $ins->close();

    // create memberprofile
    $mp = [
      'Member_Id' => $uid,
      'Em_Contact_Num' => '',
      'EM_Contact_Name' => '',
      'Body_fat' => 0,
      'Height' => 0,
      'Weight' => 0,
      'BMI' => 0,
      'Experience_Level' => 'Beginner',
      'Training_Goals' => 'General fitness',
      'Injuries' => '',
      'Medical_Condition' => '',
      'Created_at' => $now,
      'Updated_at' => $now
    ];
    insert('memberprofile', $mp);

    // create a sample workout program
    $wp = [
      'Title' => "Sample plan for $first",
      'Description' => 'Auto-generated sample plan',
      'Goal' => 'Maintain fitness',
      'Weeks_Duration' => 4,
      'Start_Date' => date('Y-m-d'),
      'End_Date' => date('Y-m-d', strtotime('+28 days')),
      'Status' => 'Active',
      'Created_at' => $now,
      'Updated_at' => $now,
      'Member_Id' => $uid,
      'Coach_ID' => $coachId
    ];
    insert('workoutprogram', $wp);

    $created[] = ['user_id'=>$uid,'email'=>$email,'name'=>"$first $last"];
}

echo json_encode(['created'=>$created]);
