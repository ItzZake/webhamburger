<?php
// Simulate GET request for getMembers
$_GET['action'] = 'getMembers';
$_SERVER['REQUEST_METHOD'] = 'GET';

include 'api.php';
?>