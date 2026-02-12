<?php
require 'c:/wamp64/www/11/DB.php';
$res = $conn->query("SELECT u.User_ID,u.Email,u.First_Name,u.Last_Name,c.Specialization_Main,c.Bio FROM coachprofile c JOIN userprofile u ON u.User_ID = c.Coach_ID WHERE c.is_deleted = 0 ORDER BY u.User_ID DESC LIMIT 50");
if(!$res){ echo "ERROR: " . $conn->error . PHP_EOL; } else { echo json_encode($res->fetch_all(MYSQLI_ASSOC), JSON_PRETTY_PRINT); }
?>
