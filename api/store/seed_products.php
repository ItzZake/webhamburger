<?php
require_once __DIR__ . '/../../DB.php';

// Simple seeder that reads store_products.json and inserts categories and products
$jsonPath = __DIR__ . '/../../Store/Store/store_products_full.json';
if (!file_exists($jsonPath)) {
    header('Content-Type: application/json');
    echo json_encode(["error" => "products JSON not found"]);
    exit;
}

$products = json_decode(file_get_contents($jsonPath), true);
if (!$products) {
    header('Content-Type: application/json');
    echo json_encode(["error" => "invalid json"]);
    exit;
}

$inserted = 0;
$skipped = 0;
$now = date('Y-m-d H:i:s');

// Add missing columns to product table if not present
$cols = [
    "rating" => "FLOAT NOT NULL DEFAULT 0",
    "reviews" => "INT NOT NULL DEFAULT 0",
    "is_new" => "TINYINT(1) NOT NULL DEFAULT 0",
    "is_sale" => "TINYINT(1) NOT NULL DEFAULT 0",
];
foreach ($cols as $col => $definition) {
    $res = $conn->query("SHOW COLUMNS FROM product LIKE '" . $conn->real_escape_string($col) . "'");
    if ($res && $res->num_rows == 0) {
        $sql = "ALTER TABLE product ADD COLUMN `$col` $definition";
        $conn->query($sql);
    }
}

// Helper: get or create category
function getCategoryId($name) {
    global $conn, $now;
    $stmt = $conn->prepare("SELECT Category_ID FROM productcategory WHERE Name = ? LIMIT 1");
    $stmt->bind_param('s', $name);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $stmt->close();
        return (int)$row['Category_ID'];
    }
    $stmt->close();

    $desc = '';
    $stmt = $conn->prepare("INSERT INTO productcategory (Name, Description, Created_at, Updated_at) VALUES (?, ?, ?, ?)");
    $stmt->bind_param('ssss', $name, $desc, $now, $now);
    $stmt->execute();
    $id = $conn->insert_id;
    $stmt->close();
    return (int)$id;
}

foreach ($products as $p) {
    // check if product already exists with same Product_ID
    $exists = $conn->prepare("SELECT Product_ID FROM product WHERE Product_ID = ? LIMIT 1");
    $exists->bind_param('i', $p['id']);
    $exists->execute();
    $r = $exists->get_result();
    if ($r->fetch_assoc()) {
        $skipped++;
        $exists->close();
        continue;
    }
    $exists->close();

    $price = floatval(str_replace(['$', ',', ' '], ['', '', ''], $p['price']));
    $cost = round($price * 0.6, 2);
    $tax = 0.1;
    $active = 1;
    $brand = '';
    $sku = $p['id'];
    $thumbnail = $p['img'];
    $categoryId = getCategoryId($p['category']);

    $sql = "INSERT INTO product (Product_ID, Name, Description, Brand, Sku, Price, Cost_price, Tax_rate, is_active, thumbnail_url, created_at, updated_at, Category_ID, rating, reviews, is_new, is_sale) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die(json_encode(['error' => 'prepare failed: ' . $conn->error]));
    }
    $rating = isset($p['rating']) ? $p['rating'] : 0;
    $reviews = isset($p['reviews']) ? (int)$p['reviews'] : 0;
    $is_new = $p['is_new'] ? 1 : 0;
    $is_sale = $p['is_sale'] ? 1 : 0;
    $stmt->bind_param('isssiddiisssiddii', $p['id'], $p['name'], $p['desc'], $brand, $sku, $price, $cost, $tax, $active, $thumbnail, $now, $now, $categoryId, $rating, $reviews, $is_new, $is_sale);
    $stmt->execute();
    if ($stmt->error) {
        // log and continue
        error_log('Insert product failed: ' . $stmt->error);
        $stmt->close();
        continue;
    }
    $inserted++;
    $stmt->close();
}

header('Content-Type: application/json');
echo json_encode(['inserted' => $inserted, 'skipped' => $skipped]);

?>