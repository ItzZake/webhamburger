<?php
$raw = "{\" product_id\\:1,\\cart_id\\:5,\\quantity\\:1,\\unit_price\\:25}";
echo "RAW: [" . $raw . "]\n";
$clean = str_replace('\\', '', $raw);
echo "CLEAN: [" . $clean . "]\n";
$clean2 = preg_replace('/"\s+/', '', $clean);
$clean2 = str_replace('"', '', $clean2);
echo "CLEAN2: [" . $clean2 . "]\n";
$fixed = preg_replace('/([\{,\s])(\w+)\s*:/', '$1"$2":', $clean2);
echo "FIXED: [" . $fixed . "]\n";
$decoded = json_decode($fixed, true);
echo "DECODED: "; var_export($decoded); echo "\n";

?>
