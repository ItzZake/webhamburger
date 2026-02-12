<?php
$c = new mysqli('localhost','root','','power gym');
$res = $c->query('SHOW COLUMNS FROM meal');
while ($r = $res->fetch_assoc()) {
    echo $r['Field'] . "\n";
}
?>
