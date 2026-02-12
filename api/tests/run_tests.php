<?php
chdir(__DIR__);
echo "Running API permission tests...\n";
foreach (['test_admin_products.php','test_coach_workouts.php','test_doctor_meals.php'] as $t) {
    echo "-- $t --\n";
    passthru(PHP_BINARY . ' ' . escapeshellarg(__DIR__ . '/' . $t));
}
echo "All tests executed. Review output above for failures.\n";

?>
