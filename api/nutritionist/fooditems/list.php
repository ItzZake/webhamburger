<?php
ob_start();
ini_set('display_errors', 0);
error_reporting(E_ALL);

require_once __DIR__ . '/../../../DB.php';
require_once __DIR__ . '/../../helpers/auth.php';
header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_role(['doctor','nutritionist','admin']);

// Get all food items
$sql = "SELECT Food_Item_ID, Name, Calories, Protein_Grams, Carbs_Grams, Fats_Grams, 
               Fiber_Grams, Sugar_Grams, Serving_Size
        FROM fooditem 
        WHERE is_deleted = 0
        ORDER BY Name";

$result = $conn->query($sql);
$foodItems = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $foodItems[] = $row;
    }
}

ob_clean();
echo json_encode($foodItems);
ob_end_flush();
?>

