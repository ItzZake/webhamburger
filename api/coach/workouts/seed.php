<?php
// CLI-only seeder for sample workouts
if (php_sapi_name() !== 'cli') { echo "seed only CLI\n"; exit; }
require_once __DIR__ . '/../../../DB.php';
$samples = [
    ['Title'=>'Full Body Blast','Description'=>'45 min full body workout','Duration_Minutes'=>45,'Difficulty'=>'Intermediate','Coach_Id'=>1],
    ['Title'=>'Quick HIIT','Description'=>'20 min high-intensity interval training','Duration_Minutes'=>20,'Difficulty'=>'Advanced','Coach_Id'=>1],
];
$inserted=0;
foreach ($samples as $s) {
    $id = insert('workout', $s);
    if ($id) $inserted++;
}
echo json_encode(['inserted'=>$inserted]) . PHP_EOL;

?>
