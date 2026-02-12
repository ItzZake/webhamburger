<?php
$path = __DIR__ . '/../Store/Store/store_products.json';
if (!file_exists($path)) { echo "not found\n"; exit(1); }
$s = file_get_contents($path);
$j = json_decode($s, true);
echo "json_last_error: " . json_last_error() . "\n";
echo "json_last_error_msg: " . json_last_error_msg() . "\n";
$len = strlen($s);
echo "bytes: $len\n";
// print tail
$tail = substr($s, max(0, $len-200));
echo "TAIL:\n" . $tail . "\n";
?>