<?php
require "c:/wamp64/www/11/DB.php";
$r=$conn->query("SHOW COLUMNS FROM nutritionistprofile");
if(!$r){echo 'ERROR: '.$conn->error.PHP_EOL; } else {while($row=$r->fetch_assoc()){echo $row['Field'].PHP_EOL;}}
?>
