<?php
$c=new mysqli('localhost','root','','power gym');
foreach(['memberprofile','nutritionistprofile'] as $t){
  $res=$c->query("SELECT COUNT(*) AS c FROM $t");
  $r=$res->fetch_assoc();
  echo "$t: " . $r['c'] . "\n";
}
?>
