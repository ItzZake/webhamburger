<?php
/**
 * Seed file for Food Items
 * This script populates the fooditem table with a comprehensive list of food items
 * Run this once to seed the database with food items
 */

require_once __DIR__ . '/../../DB.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Only allow admins or run from command line
if (php_sapi_name() !== 'cli' && (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin')) {
    die("Access denied. This script can only be run by admins or from command line.");
}

$now = date('Y-m-d H:i:s');

// Comprehensive food items list
$foodItems = [
    // PROTEINS - Chicken & Poultry
    ['Chicken Breast', 0, 100, 165, 0, 3.6, 0, 31, 0, $now, $now],
    ['Chicken Thigh', 0, 100, 209, 0, 10, 0, 26, 0, $now, $now],
    ['Chicken Wing', 0, 100, 203, 0, 12, 0, 19, 0, $now, $now],
    ['Ground Turkey', 0, 100, 189, 0, 10, 0, 23, 0, $now, $now],
    ['Turkey Breast', 0, 100, 135, 0, 1, 0, 30, 0, $now, $now],
    ['Duck Breast', 0, 100, 337, 0, 28, 0, 19, 0, $now, $now],
    
    // PROTEINS - Beef
    ['Lean Ground Beef', 0, 100, 250, 0, 17, 0, 26, 0, $now, $now],
    ['Beef Steak', 0, 100, 271, 0, 19, 0, 25, 0, $now, $now],
    ['Beef Sirloin', 0, 100, 180, 0, 8, 0, 26, 0, $now, $now],
    ['Beef Ribeye', 0, 100, 291, 0, 22, 0, 23, 0, $now, $now],
    ['Beef Tenderloin', 0, 100, 250, 0, 18, 0, 22, 0, $now, $now],
    
    // PROTEINS - Pork
    ['Pork Chop', 0, 100, 242, 0, 14, 0, 27, 0, $now, $now],
    ['Pork Tenderloin', 0, 100, 143, 0, 3, 0, 26, 0, $now, $now],
    ['Bacon', 0, 100, 541, 0, 42, 0, 37, 0, $now, $now],
    ['Pork Shoulder', 0, 100, 242, 0, 16, 0, 20, 0, $now, $now],
    
    // PROTEINS - Fish & Seafood
    ['Salmon', 0, 100, 208, 0, 12, 0, 20, 0, $now, $now],
    ['Tuna', 0, 100, 144, 0, 1, 0, 30, 0, $now, $now],
    ['Cod', 0, 100, 82, 0, 0.5, 0, 18, 0, $now, $now],
    ['Tilapia', 0, 100, 128, 0, 3, 0, 26, 0, $now, $now],
    ['Shrimp', 0, 100, 99, 0, 0.3, 0, 24, 0, $now, $now],
    ['Crab', 0, 100, 87, 0, 1, 0, 18, 0, $now, $now],
    ['Lobster', 0, 100, 89, 0, 1, 0, 19, 0, $now, $now],
    ['Mackerel', 0, 100, 262, 0, 18, 0, 24, 0, $now, $now],
    ['Sardines', 0, 100, 208, 0, 12, 0, 25, 0, $now, $now],
    ['Anchovies', 0, 100, 131, 0, 4, 0, 20, 0, $now, $now],
    
    // PROTEINS - Eggs & Dairy
    ['Whole Egg', 0, 1, 70, 0, 5, 0, 6, 0, $now, $now],
    ['Egg White', 0, 1, 17, 0, 0, 0, 4, 0, $now, $now],
    ['Greek Yogurt', 0, 100, 59, 4, 0.4, 0, 10, 4, $now, $now],
    ['Cottage Cheese', 0, 100, 98, 3, 4, 0, 11, 3, $now, $now],
    ['Mozzarella Cheese', 0, 100, 280, 1, 17, 0, 28, 1, $now, $now],
    ['Cheddar Cheese', 0, 100, 402, 0, 33, 0, 25, 0, $now, $now],
    ['Feta Cheese', 0, 100, 264, 1, 21, 0, 14, 1, $now, $now],
    ['Parmesan Cheese', 0, 100, 431, 0, 29, 0, 38, 0, $now, $now],
    
    // PROTEINS - Plant-Based
    ['Tofu', 0, 100, 76, 1, 5, 1, 8, 2, $now, $now],
    ['Tempeh', 0, 100, 193, 0, 11, 0, 19, 9, $now, $now],
    ['Edamame', 0, 100, 122, 3, 5, 5, 11, 10, $now, $now],
    ['Lentils', 0, 100, 116, 0, 0.4, 8, 9, 20, $now, $now],
    ['Chickpeas', 0, 100, 164, 3, 3, 8, 9, 27, $now, $now],
    ['Black Beans', 0, 100, 132, 0, 1, 8, 9, 23, $now, $now],
    ['Kidney Beans', 0, 100, 127, 1, 1, 7, 9, 22, $now, $now],
    ['Quinoa', 0, 100, 120, 0, 2, 3, 4, 22, $now, $now],
    
    // CARBOHYDRATES - Grains
    ['Brown Rice', 0, 100, 111, 0, 1, 2, 3, 23, $now, $now],
    ['White Rice', 0, 100, 130, 0, 0.3, 0, 3, 28, $now, $now],
    ['Oats', 0, 100, 389, 1, 7, 11, 17, 66, $now, $now],
    ['Whole Wheat Bread', 0, 100, 247, 4, 3, 7, 13, 41, $now, $now],
    ['White Bread', 0, 100, 265, 3, 3, 3, 9, 49, $now, $now],
    ['Whole Wheat Pasta', 0, 100, 124, 1, 1, 3, 5, 25, $now, $now],
    ['White Pasta', 0, 100, 131, 0, 1, 2, 5, 25, $now, $now],
    ['Barley', 0, 100, 123, 0, 1, 4, 3, 28, $now, $now],
    ['Bulgur', 0, 100, 83, 0, 0.2, 5, 3, 19, $now, $now],
    ['Couscous', 0, 100, 112, 0, 0.2, 1, 2, 23, $now, $now],
    
    // CARBOHYDRATES - Potatoes & Starches
    ['Sweet Potato', 0, 100, 86, 4, 0.2, 3, 20, 20, $now, $now],
    ['White Potato', 0, 100, 77, 1, 0.1, 2, 17, 17, $now, $now],
    ['Red Potato', 0, 100, 70, 1, 0.1, 2, 15, 15, $now, $now],
    ['Yam', 0, 100, 118, 0, 0.2, 4, 28, 28, $now, $now],
    ['Corn', 0, 100, 96, 3, 1, 3, 21, 21, $now, $now],
    
    // VEGETABLES - Leafy Greens
    ['Spinach', 0, 100, 23, 0, 0.4, 2, 3, 3, $now, $now],
    ['Kale', 0, 100, 49, 0, 1, 2, 9, 9, $now, $now],
    ['Lettuce', 0, 100, 15, 0, 0.2, 1, 3, 3, $now, $now],
    ['Arugula', 0, 100, 25, 0, 1, 2, 4, 4, $now, $now],
    ['Swiss Chard', 0, 100, 19, 0, 0.2, 2, 4, 4, $now, $now],
    ['Collard Greens', 0, 100, 32, 0, 1, 4, 6, 6, $now, $now],
    ['Cabbage', 0, 100, 25, 0, 0.1, 2, 6, 6, $now, $now],
    ['Bok Choy', 0, 100, 13, 0, 0.2, 1, 2, 2, $now, $now],
    
    // VEGETABLES - Cruciferous
    ['Broccoli', 0, 100, 34, 2, 0.4, 3, 7, 7, $now, $now],
    ['Cauliflower', 0, 100, 25, 2, 0.3, 2, 5, 5, $now, $now],
    ['Brussels Sprouts', 0, 100, 43, 2, 0.3, 4, 9, 9, $now, $now],
    ['Cabbage Red', 0, 100, 31, 0, 0.1, 2, 7, 7, $now, $now],
    
    // VEGETABLES - Other
    ['Carrots', 0, 100, 41, 5, 0.2, 3, 10, 10, $now, $now],
    ['Bell Pepper', 0, 100, 31, 5, 0.3, 3, 7, 7, $now, $now],
    ['Tomato', 0, 100, 18, 3, 0.2, 1, 4, 4, $now, $now],
    ['Cucumber', 0, 100, 16, 2, 0.1, 1, 4, 4, $now, $now],
    ['Zucchini', 0, 100, 17, 3, 0.3, 1, 3, 3, $now, $now],
    ['Eggplant', 0, 100, 25, 4, 0.2, 3, 6, 6, $now, $now],
    ['Mushrooms', 0, 100, 22, 2, 0.3, 1, 3, 3, $now, $now],
    ['Onion', 0, 100, 40, 4, 0.1, 2, 9, 9, $now, $now],
    ['Garlic', 0, 100, 149, 1, 0.5, 2, 33, 33, $now, $now],
    ['Celery', 0, 100, 16, 1, 0.2, 2, 3, 3, $now, $now],
    ['Asparagus', 0, 100, 20, 1, 0.1, 2, 4, 4, $now, $now],
    ['Green Beans', 0, 100, 31, 4, 0.2, 3, 7, 7, $now, $now],
    ['Peas', 0, 100, 81, 4, 0.4, 5, 14, 14, $now, $now],
    
    // FRUITS
    ['Apple', 0, 100, 52, 10, 0.2, 2, 14, 14, $now, $now],
    ['Banana', 0, 100, 89, 12, 0.3, 3, 23, 23, $now, $now],
    ['Orange', 0, 100, 47, 9, 0.1, 2, 12, 12, $now, $now],
    ['Strawberries', 0, 100, 32, 5, 0.3, 2, 8, 8, $now, $now],
    ['Blueberries', 0, 100, 57, 10, 0.3, 2, 14, 14, $now, $now],
    ['Raspberries', 0, 100, 52, 4, 1, 6, 12, 12, $now, $now],
    ['Blackberries', 0, 100, 43, 5, 0.5, 5, 10, 10, $now, $now],
    ['Grapes', 0, 100, 69, 16, 0.2, 1, 18, 18, $now, $now],
    ['Watermelon', 0, 100, 30, 6, 0.2, 0, 8, 8, $now, $now],
    ['Cantaloupe', 0, 100, 34, 8, 0.2, 1, 8, 8, $now, $now],
    ['Pineapple', 0, 100, 50, 10, 0.1, 1, 13, 13, $now, $now],
    ['Mango', 0, 100, 60, 14, 0.4, 2, 15, 15, $now, $now],
    ['Peach', 0, 100, 39, 8, 0.3, 2, 10, 10, $now, $now],
    ['Pear', 0, 100, 57, 10, 0.1, 3, 15, 15, $now, $now],
    ['Kiwi', 0, 100, 61, 9, 0.5, 3, 15, 15, $now, $now],
    ['Avocado', 0, 100, 160, 0, 15, 7, 9, 9, $now, $now],
    
    // NUTS & SEEDS
    ['Almonds', 0, 100, 579, 4, 50, 12, 21, 21, $now, $now],
    ['Walnuts', 0, 100, 654, 3, 65, 7, 15, 15, $now, $now],
    ['Cashews', 0, 100, 553, 6, 44, 3, 30, 30, $now, $now],
    ['Peanuts', 0, 100, 567, 4, 49, 8, 26, 26, $now, $now],
    ['Pistachios', 0, 100, 560, 7, 45, 10, 20, 20, $now, $now],
    ['Pecans', 0, 100, 691, 4, 72, 10, 9, 9, $now, $now],
    ['Macadamia Nuts', 0, 100, 718, 4, 76, 9, 14, 14, $now, $now],
    ['Hazelnuts', 0, 100, 628, 4, 61, 10, 17, 17, $now, $now],
    ['Chia Seeds', 0, 100, 486, 0, 31, 34, 17, 17, $now, $now],
    ['Flax Seeds', 0, 100, 534, 0, 42, 27, 29, 29, $now, $now],
    ['Sunflower Seeds', 0, 100, 584, 3, 51, 9, 20, 20, $now, $now],
    ['Pumpkin Seeds', 0, 100, 559, 1, 49, 6, 10, 10, $now, $now],
    ['Sesame Seeds', 0, 100, 573, 0, 50, 12, 18, 18, $now, $now],
    
    // HEALTHY FATS
    ['Olive Oil', 0, 100, 884, 0, 100, 0, 0, 0, $now, $now],
    ['Coconut Oil', 0, 100, 862, 0, 100, 0, 0, 0, $now, $now],
    ['Avocado Oil', 0, 100, 884, 0, 100, 0, 0, 0, $now, $now],
    ['Butter', 0, 100, 717, 0, 81, 0, 1, 0, $now, $now],
    ['Ghee', 0, 100, 900, 0, 100, 0, 0, 0, $now, $now],
    
    // BEVERAGES
    ['Water', 0, 100, 0, 0, 0, 0, 0, 0, $now, $now],
    ['Green Tea', 0, 100, 2, 0, 0, 0, 0, 0, $now, $now],
    ['Black Coffee', 0, 100, 2, 0, 0, 0, 0, 0, $now, $now],
    ['Almond Milk', 0, 100, 17, 0, 1, 1, 1, 1, $now, $now],
    ['Coconut Milk', 0, 100, 230, 3, 24, 2, 6, 6, $now, $now],
    ['Oat Milk', 0, 100, 54, 0, 1, 1, 10, 10, $now, $now],
    ['Soy Milk', 0, 100, 54, 1, 2, 1, 6, 6, $now, $now],
    
    // PROTEIN POWDERS & SUPPLEMENTS
    ['Whey Protein', 0, 30, 120, 1, 1, 0, 24, 1, $now, $now],
    ['Casein Protein', 0, 30, 120, 1, 1, 0, 24, 1, $now, $now],
    ['Plant Protein', 0, 30, 110, 1, 2, 2, 20, 2, $now, $now],
    ['Pea Protein', 0, 30, 100, 1, 1, 1, 21, 1, $now, $now],
    
    // CONDIMENTS & SAUCES
    ['Honey', 0, 100, 304, 82, 0, 0, 0, 82, $now, $now],
    ['Maple Syrup', 0, 100, 260, 67, 0, 0, 0, 67, $now, $now],
    ['Hot Sauce', 0, 100, 6, 1, 0.1, 0, 1, 1, $now, $now],
    ['Soy Sauce', 0, 100, 53, 5, 0.1, 0, 6, 6, $now, $now],
    ['Balsamic Vinegar', 0, 100, 88, 17, 0, 0, 17, 17, $now, $now],
    ['Apple Cider Vinegar', 0, 100, 22, 1, 0, 0, 1, 1, $now, $now],
    
    // SNACKS
    ['Rice Cakes', 0, 100, 387, 0, 3, 2, 8, 8, $now, $now],
    ['Popcorn', 0, 100, 387, 0, 4, 15, 78, 78, $now, $now],
    ['Dark Chocolate', 0, 100, 546, 24, 31, 11, 45, 45, $now, $now],
    ['Protein Bar', 0, 100, 400, 20, 10, 5, 30, 30, $now, $now],
];

$insertSql = "INSERT INTO fooditem 
              (Name, Brand, Serving_Size, Calories, Sugar_Grams, Fats_Grams, Fiber_Grams, Protein_Grams, Carbs_Grams, Created_at, Updated_at, is_deleted) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($insertSql);
if (!$stmt) {
    die("Error preparing statement: " . $conn->error . "\n");
}

$inserted = 0;
$skipped = 0;

foreach ($foodItems as $item) {
    // Check if item already exists
    $checkSql = "SELECT Food_Item_ID FROM fooditem WHERE Name = ? AND is_deleted = 0";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param('s', $item[0]);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    
    if ($result->num_rows > 0) {
        $skipped++;
        $checkStmt->close();
        continue;
    }
    $checkStmt->close();
    
    // Insert the item
    // Extract values to variables (bind_param requires variables by reference)
    $name = $item[0];
    $brand = $item[1];
    $servingSize = $item[2];
    $calories = $item[3];
    $sugarGrams = $item[4];
    $fatsGrams = $item[5];
    $fiberGrams = $item[6];
    $proteinGrams = $item[7];
    $carbsGrams = $item[8];
    $createdAt = $item[9];
    $updatedAt = $item[10];
    $isDeleted = 0; // Must be a variable, not a literal
    
    // Format: s (string), i (integer) - 12 parameters total
    // Name(s), Brand(i), Serving_Size(i), Calories(i), Sugar_Grams(i), Fats_Grams(i), 
    // Fiber_Grams(i), Protein_Grams(i), Carbs_Grams(i), Created_at(s), Updated_at(s), is_deleted(i)
    $stmt->bind_param('siiiiiiiissi', 
        $name,
        $brand,
        $servingSize,
        $calories,
        $sugarGrams,
        $fatsGrams,
        $fiberGrams,
        $proteinGrams,
        $carbsGrams,
        $createdAt,
        $updatedAt,
        $isDeleted
    );
    
    if ($stmt->execute()) {
        $inserted++;
    } else {
        echo "Error inserting {$item[0]}: " . $stmt->error . "\n";
    }
}

$stmt->close();

echo "Food items seeding completed!\n";
echo "Inserted: $inserted items\n";
echo "Skipped (already exist): $skipped items\n";
echo "Total items in database: " . count($foodItems) . "\n";
?>

