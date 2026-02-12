<?php
$c = new mysqli('localhost','root','','power gym');
$res = $c->query('SELECT Meal_Plan_ID FROM mealplan LIMIT 1');
if ($res && $r = $res->fetch_assoc()) echo $r['Meal_Plan_ID'] . PHP_EOL; else echo "none\n";
?>
