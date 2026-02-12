<?php
require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../../DB.php';

// Simulate logged in nutritionist
if (session_status() === PHP_SESSION_NONE) session_start();
$_SESSION['logged_in']=true; $_SESSION['role']='nutritionist'; $_SESSION['Member_Id']=2; // sample id

$_POST = ['name'=>'CLI Test Meal','calories'=>450,'protein'=>30];
ob_start(); include __DIR__.'/../../api/doctor/meals/create.php'; $out = ob_get_clean();
echo "Create response: ". $out . "\n";

// verify list contains recent meal (simple search)
$res = select('meal'); foreach($res as $r) { if ($r['Name']=='CLI Test Meal') { echo "Found inserted meal id: ".$r['Meal_ID']."\n"; break; } }
