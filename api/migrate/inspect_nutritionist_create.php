<?php
$c=new mysqli('localhost','root','','power gym');
$res=$c->query('SHOW CREATE TABLE nutritionistprofile');
echo json_encode($res->fetch_assoc(), JSON_PRETTY_PRINT);
?>
