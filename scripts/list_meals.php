<?php
require "c:/wamp64/www/11/DB.php";
$r=$conn->query("SELECT Meal_ID,Name,Calories,Doctor_Id FROM meal ORDER BY Meal_ID DESC LIMIT 20");
if(!$r){echo "ERROR: " . $conn->error . PHP_EOL; } else {echo json_encode($r->fetch_all(MYSQLI_ASSOC), JSON_PRETTY_PRINT);}
?>
