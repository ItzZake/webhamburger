<?php
$src = __DIR__ . '/../Store/Store/data.js';
$dst = __DIR__ . '/../Store/Store/store_products_full.json';
if (!file_exists($src)) {
    echo "data.js not found\n";
    exit(1);
}
$s = file_get_contents($src);
$pos = strpos($s, 'const storeProducts');
if ($pos === false) {
    echo "const storeProducts not found\n";
    exit(1);
}
$start = strpos($s, '[', $pos);
$end = strrpos($s, '];');
if ($start === false || $end === false) {
    echo "array brackets not found\n";
    exit(1);
}
$arra = substr($s, $start, $end - $start + 1);
// Remove trailing commas before } or ]
$clean = preg_replace('/,\s*([\}\]])/', '$1', $arra);
// Also remove JS comments (// ...)
$clean = preg_replace('!//.*!', '', $clean);
// Try to decode JSON
$json = json_decode($clean, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    // Attempt a more forgiving cleanup: convert single quotes to double quotes where appropriate
    $alt = preg_replace("/([\w\"]+)\s*:\s*'([^']*)'/", '"$1": "$2"', $clean);
    $alt = preg_replace('/,(\s*[}\]])/', '$1', $alt);
    $json = json_decode($alt, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        file_put_contents($dst, $clean);
        echo "JSON decode failed: " . json_last_error_msg() . "\nSaved raw to $dst\n";
        exit(1);
    }
    $clean = $alt;
}
file_put_contents($dst, json_encode($json, JSON_PRETTY_PRINT));
echo "Wrote " . count($json) . " products to $dst\n";
?>