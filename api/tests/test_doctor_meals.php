<?php
// CLI test: doctor ownership enforcement for meals
require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../../DB.php';

function run_endpoint($file) { global $conn; ob_start(); include $file; $out = ob_get_clean(); $j=json_decode($out,true); return $j?:['raw'=>$out]; }

echo "== Doctor meal permission tests ==\n";

$mid1 = insert('meal',['Name'=>'Meal A','Description'=>'','Calories'=>200,'Protein'=>20,'Carbs'=>20,'Fat'=>5,'Doctor_Id'=>1]);
$mid2 = insert('meal',['Name'=>'Meal B','Description'=>'','Calories'=>300,'Protein'=>30,'Carbs'=>30,'Fat'=>10,'Doctor_Id'=>2]);
echo "Inserted meals $mid1, $mid2\n";

// Doctor 1 updates their meal -> success
$_SESSION=[]; session_start(); $_SESSION['role']='doctor'; $_SESSION['Member_Id']=1; $_POST=['id'=>$mid1,'name'=>'Updated Meal'];
$r1 = run_endpoint(__DIR__.'/../../api/doctor/meals/update.php'); echo "doctor1 update own -> "; var_export($r1); echo "\n";

// Doctor 1 tries to update doctor2 meal -> forbidden
$_POST=['id'=>$mid2,'name'=>'Should fail']; $r2=run_endpoint(__DIR__.'/../../api/doctor/meals/update.php'); echo "doctor1 update other -> "; var_export($r2); echo "\n";

// Cleanup
$_SESSION=[]; session_start(); $_SESSION['role']='admin'; $_POST=['id'=>$mid1]; run_endpoint(__DIR__.'/../../api/doctor/meals/delete.php'); $_POST=['id'=>$mid2]; run_endpoint(__DIR__.'/../../api/doctor/meals/delete.php');
echo "== Doctor tests done ==\n";

?>
