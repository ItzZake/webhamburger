<?php
// Tests admin product create authorization
if (php_sapi_name() !== 'cli') { echo "CLI only\n"; exit; }
echo "Test: admin can create product...\n";
$_SESSION = [];
session_start();
$_SESSION['role'] = 'admin';
// simulate POST body
$data = json_encode(['name'=>'Test Product','price'=>9.99]);
file_put_contents(__DIR__.'/tmp_prod.json', $data);
// run the endpoint by including through a stream wrapper
ob_start();
// Make $_POST empty and php://input provide our json
stream_wrapper_unregister('php');
stream_wrapper_register('php', 'PHP_Shim');
define('PHP_SHIM_INPUT_FILE', __DIR__.'/tmp_prod.json');
include __DIR__.'/../../admin/products/create.php';
$out = ob_get_clean();
echo $out . "\n";

echo "Test: non-admin should be forbidden...\n";
$_SESSION = [];
session_start();
$_SESSION['role'] = 'member';
ob_start(); include __DIR__.'/../../admin/products/create.php'; $out=ob_get_clean(); echo $out . "\n";

// cleanup
@unlink(__DIR__.'/tmp_prod.json');

// PHP input shim class
class PHP_Shim {
    public $context;
    public function stream_open($path, $mode, $options, &$opened_path) {
        return true;
    }
    public function stream_read($count) {
        return '';
    }
    public function stream_eof() { return true; }
    public function stream_stat() { return []; }
}

?>
