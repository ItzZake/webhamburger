<?php
header('Content-Type: application/json');
$raw = file_get_contents('php://input');
echo json_encode(['raw' => $raw, 'post' => $_POST]);
$result = ['raw' => $raw, 'post' => $_POST];

// Attempt parses like add_item.php
$parsed = json_decode($raw, true);
if (!$parsed) {
	$clean = str_replace('\\', '', $raw);
	$parsed = json_decode($clean, true);
	if (!$parsed) {
		$fixed = preg_replace('/([\{,\s])(\w+)\s*:/', '$1"$2":', $clean);
		$parsed = json_decode($fixed, true);
	}
}
$result['parsed'] = $parsed;

echo json_encode($result);

?>
