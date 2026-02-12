<?php
// CLI test: coach ownership enforcement for workouts
require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../../DB.php';

function run_endpoint($file) {
    ob_start(); include $file; $out = ob_get_clean(); $j = json_decode($out, true); return $j ?: ['raw'=>$out];
}

echo "== Coach workout permission tests ==\n";

// Insert two workouts by different coaches
$id1 = insert('workout', ['Title'=>'Owned by 1','Description'=>'','Duration_Minutes'=>10,'Difficulty'=>'Easy','Coach_Id'=>1]);
$id2 = insert('workout', ['Title'=>'Owned by 2','Description'=>'','Duration_Minutes'=>20,'Difficulty'=>'Hard','Coach_Id'=>2]);
echo "Inserted $id1 and $id2\n";

// Coach 1 updates their workout -> should succeed
$_SESSION = []; session_start(); $_SESSION['role']='coach'; $_SESSION['Member_Id']=1; $_POST=['id'=>$id1,'title'=>'Updated by coach1'];
$r1 = run_endpoint(__DIR__.'/../../api/coach/workouts/update.php'); echo "coach1 update owned -> "; var_export($r1); echo "\n";

// Coach 1 tries to update workout owned by coach 2 -> forbidden
$_POST=['id'=>$id2,'title'=>'Should fail']; $r2 = run_endpoint(__DIR__.'/../../api/coach/workouts/update.php'); echo "coach1 update other -> "; var_export($r2); echo "\n";

// Cleanup (mark deleted)
$_SESSION = []; session_start(); $_SESSION['role']='admin'; $_POST=['id'=>$id1]; run_endpoint(__DIR__.'/../../api/coach/workouts/delete.php'); $_POST=['id'=>$id2]; run_endpoint(__DIR__.'/../../api/coach/workouts/delete.php');
echo "== Coach tests done ==\n";

?>
