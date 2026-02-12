<?php
// CLI test: admin product create/update/delete and non-admin forbidden
require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../../DB.php';

function run_endpoint($file) {
    ob_start();
    include $file;
    $out = ob_get_clean();
    $j = json_decode($out, true);
    return $j ?: ['raw' => $out];
}

echo "== Admin product CRUD tests ==\n";

// Admin create
$_SESSION = [];
session_start();
$_SESSION['role'] = 'admin';
$_SESSION['Member_Id'] = 9999;
$_POST = ['name' => 'Test product CLI', 'price' => '19.99'];
$res = run_endpoint(__DIR__.'/../../api/admin/products/create.php');
echo "create -> "; var_export($res); echo "\n";
$createdId = isset($res['id']) ? $res['id'] : null;

// Non-admin should be forbidden
$_SESSION = [];
session_start();
$_SESSION['role'] = 'member';
$_POST = ['name' => 'Should fail', 'price' => '1.00'];
$res2 = run_endpoint(__DIR__.'/../../api/admin/products/create.php');
echo "non-admin create -> "; var_export($res2); echo "\n";

// Cleanup created product (as admin)
if ($createdId) {
    $_SESSION = [];
    session_start();
    $_SESSION['role'] = 'admin';
    $_POST = ['id' => $createdId];
    $del = run_endpoint(__DIR__.'/../../api/admin/products/delete.php');
    echo "cleanup delete -> "; var_export($del); echo "\n";
}

echo "== Admin product tests done ==\n";

?>
