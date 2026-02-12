<?php
require_once __DIR__ . '/../../DB.php';
header('Content-Type: application/json');

// Lightweight seeder for dev testing: creates sample members, workoutprograms and meal plans
$results = ['created'=>[]];
$now = date('Y-m-d H:i:s');

// create sample members/users if not exists
$members = [
  ['email'=>'member1@example.local','first'=>'Alice','last'=>'Smith'],
  ['email'=>'member2@example.local','first'=>'Bob','last'=>'Johnson']
];
foreach ($members as $m) {
  $row = $conn->query("SELECT User_ID FROM userprofile WHERE Email = '".$conn->real_escape_string($m['email'])."'")->fetch_assoc();
  if (!$row) {
    $ins = $conn->prepare("INSERT INTO userprofile (Email, Password, Last_Login, First_Name, Last_Name, Phone_Number, DOB, Role, Gender, Is_Active, Profile_pic_url, Created_at, Updated_at) VALUES (?, '', '0000-00-00', ?, ?, 0, '0000-00-00', 'member', '', 1, '', ?, ?)");
    $ins->bind_param('sssss', $m['email'], $m['first'], $m['last'], $now, $now);
    $ins->execute();
    $uid = $ins->insert_id;
    $ins->close();
    // create memberprofile
    insert('memberprofile', ['Member_Id'=>$uid,'Em_Contact_Num'=>'','EM_Contact_Name'=>'','Body_fat'=>0,'Height'=>0,'Weight'=>0,'BMI'=>0,'Experience_Level'=>'Beginner','Training_Goals'=>'General fitness','Injuries'=>'','Medical_Condition'=>'','Created_at'=>$now,'Updated_at'=>$now]);
    $results['created'][] = "user_member:$uid";
  }
}

// create a sample workoutprogram for member1 assigned to existing Coach (User_ID 3 exists in DB)
$member = $conn->query("SELECT User_ID FROM userprofile WHERE Email='member1@example.local'")->fetch_assoc();
if ($member) {
  $mid = (int)$member['User_ID'];
  $exists = $conn->query("SELECT Workout_ID FROM workoutprogram WHERE Member_Id = $mid LIMIT 1")->fetch_assoc();
  if (!$exists) {
    $ins = $conn->prepare("INSERT INTO workoutprogram (Title, Description, Goal, Weeks_Duration, Start_Date, End_Date, Status, Created_at, Updated_at, Member_Id, Coach_ID) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $title = "Sample 4-week Strength"; $desc='Basic 4-week program'; $goal='Build Strength'; $weeks=4; $start=date('Y-m-d'); $end=date('Y-m-d', strtotime('+28 days')); $status='Active'; $coachId = 3;
    $ins->bind_param('sssissssiii', $title,$desc,$goal,$weeks,$start,$end,$status,$now,$now,$mid,$coachId);
    $ins->execute();
    $results['created'][] = 'workoutprogram:'.$ins->insert_id;
    $ins->close();
  }
}

// create a sample mealplan for member1 assigned to Nutritionist (if exists)
$nut = $conn->query("SELECT Nutritionist_ID FROM nutritionistprofile LIMIT 1")->fetch_assoc();
if ($member && $nut) {
  $mid = (int)$member['User_ID']; $nid = (int)$nut['Nutritionist_ID'];
  $exists = $conn->query("SELECT Meal_Plan_ID FROM mealplan WHERE Member_Id=$mid LIMIT 1")->fetch_assoc();
  if (!$exists) {
    $ins = $conn->prepare("INSERT INTO mealplan (Title, Description, Nutritionist_ID, Member_Id, Start_Date, End_Date, Created_at, Updated_at, is_deleted) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0)");
    $title='Sample Meal Plan'; $desc='Balanced 7-day plan'; $start=date('Y-m-d'); $end=date('Y-m-d', strtotime('+7 days'));
    $ins->bind_param('ssisssss', $title,$desc,$nid,$mid,$start,$end,$now,$now);
    $ins->execute();
    $pid = $ins->insert_id;
    $results['created'][] = 'mealplan:'.$pid;
    $ins->close();
    // (mealplanitem table in this schema does not include Meal_Plan_ID relations)
    // add a note in results that a mealplan was created; you can add mealplan items via the UI later.
    $results['created'][] = 'mealplan:'.$pid;
  }
}

echo json_encode($results);
