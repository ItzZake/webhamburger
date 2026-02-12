<?php
// HTTP-based test runner using curl and a cookie jar to simulate logged-in roles
$base = 'http://localhost/webhamburger';
$category_id = null;
// Find or create a default product category to satisfy FK
$dbPath = __DIR__ . '/../../DB.php';
if (file_exists($dbPath)) require_once $dbPath; else { echo "DB.php not found\n"; exit(1); }
$res = $conn->query('SELECT Category_ID FROM productcategory LIMIT 1');
if ($res && $r = $res->fetch_assoc()) {
    $category_id = (int)$r['Category_ID'];
} else {
    $conn->query("INSERT INTO productcategory (Name) VALUES ('Default')");
    $category_id = $conn->insert_id;
}
$cj = __DIR__ . '/cookiejar.txt';
@unlink($cj);

function http($url, $data = null, $cj = null) {
    $cmd = 'curl -s -w "\nHTTP_STATUS:%{http_code}\n" ';
    if ($cj) $cmd .= ' -b ' . escapeshellarg($cj) . ' -c ' . escapeshellarg($cj) . ' ';
    $tmp = null;
    if ($data !== null) {
        $tmp = tempnam(sys_get_temp_dir(), 'httptest');
        file_put_contents($tmp, json_encode($data));
        $cmd .= ' -H "Content-Type: application/json" --data-binary @' . escapeshellarg($tmp) . ' ';
    }
    $cmd .= escapeshellarg($url);
    $out = shell_exec($cmd);
    if ($tmp) @unlink($tmp);
    return $out;
}

echo "Setting admin session...\n";
echo http($base . '/api/tests/set_session.php', ['role'=>'admin','Member_Id'=>9999], $cj);

echo "Create product as admin...\n";
echo http($base . '/api/admin/products/create.php', ['name'=>'HTTP Test','price'=>1.99,'category_id'=>$category_id], $cj);

echo "Set member session (should be forbidden)...\n";
echo http($base . '/api/tests/set_session.php', ['role'=>'member','Member_Id'=>2], $cj);
echo "Create product as member (expect forbidden)...\n";
echo http($base . '/api/admin/products/create.php', ['name'=>'Fail','price'=>0.99], $cj);

echo "Setting coach session...\n";
echo http($base . '/api/tests/set_session.php', ['role'=>'coach','Member_Id'=>1], $cj);
echo "Create workout as coach...\n";
echo http($base . '/api/coach/workouts/create.php', ['title'=>'HTTP Workout','duration'=>15,'difficulty'=>'Easy'], $cj);

echo "Setting doctor session...\n";
echo http($base . '/api/tests/set_session.php', ['role'=>'doctor','Member_Id'=>1], $cj);
echo "Create meal as doctor...\n";
echo http($base . '/api/doctor/meals/create.php', ['name'=>'HTTP Meal','calories'=>400], $cj);

echo "HTTP tests complete.\n";

?>
