<?php
/**
 * Update script for Food Items - Fix inaccurate fat values
 * This script updates existing food items with correct fat values
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

// Food items with correct fat values (per 100g unless specified)
// Format: ['Name' => Fats_Grams]
$fatUpdates = [
    // PROTEINS - Chicken & Poultry
    'Chicken Breast' => 3.6,
    'Chicken Thigh' => 10,
    'Chicken Wing' => 12,
    'Ground Turkey' => 10,
    'Turkey Breast' => 1,
    'Duck Breast' => 28,
    
    // PROTEINS - Beef
    'Lean Ground Beef' => 17,
    'Beef Steak' => 19,
    'Beef Sirloin' => 8,
    'Beef Ribeye' => 22,
    'Beef Tenderloin' => 18,
    
    // PROTEINS - Pork
    'Pork Chop' => 14,
    'Pork Tenderloin' => 3,
    'Bacon' => 42,
    'Pork Shoulder' => 16,
    
    // PROTEINS - Fish & Seafood
    'Salmon' => 12,
    'Tuna' => 1,
    'Cod' => 0.5,
    'Tilapia' => 3,
    'Shrimp' => 0.3,
    'Crab' => 1,
    'Lobster' => 1,
    'Mackerel' => 18,
    'Sardines' => 12,
    'Anchovies' => 4,
    
    // PROTEINS - Eggs & Dairy
    'Whole Egg' => 5, // per egg
    'Egg White' => 0, // per egg
    'Greek Yogurt' => 0.4,
    'Cottage Cheese' => 4,
    'Mozzarella Cheese' => 17,
    'Cheddar Cheese' => 33,
    'Feta Cheese' => 21,
    'Parmesan Cheese' => 29,
    
    // PROTEINS - Plant-Based
    'Tofu' => 5,
    'Tempeh' => 11,
    'Edamame' => 5,
    'Lentils' => 0.4,
    'Chickpeas' => 3,
    'Black Beans' => 1,
    'Kidney Beans' => 1,
    'Quinoa' => 2,
    
    // CARBOHYDRATES - Grains
    'Brown Rice' => 1,
    'White Rice' => 0.3,
    'Oats' => 7,
    'Whole Wheat Bread' => 3,
    'White Bread' => 3,
    'Whole Wheat Pasta' => 1,
    'White Pasta' => 1,
    'Barley' => 1,
    'Bulgur' => 0.2,
    'Couscous' => 0.2,
    
    // CARBOHYDRATES - Potatoes & Starches
    'Sweet Potato' => 0.2,
    'White Potato' => 0.1,
    'Red Potato' => 0.1,
    'Yam' => 0.2,
    'Corn' => 1,
    
    // VEGETABLES - Leafy Greens
    'Spinach' => 0.4,
    'Kale' => 1,
    'Lettuce' => 0.2,
    'Arugula' => 1,
    'Swiss Chard' => 0.2,
    'Collard Greens' => 1,
    'Cabbage' => 0.1,
    'Bok Choy' => 0.2,
    
    // VEGETABLES - Cruciferous
    'Broccoli' => 0.4,
    'Cauliflower' => 0.3,
    'Brussels Sprouts' => 0.3,
    'Cabbage Red' => 0.1,
    
    // VEGETABLES - Other
    'Carrots' => 0.2,
    'Bell Pepper' => 0.3,
    'Tomato' => 0.2,
    'Cucumber' => 0.1,
    'Zucchini' => 0.3,
    'Eggplant' => 0.2,
    'Mushrooms' => 0.3,
    'Onion' => 0.1,
    'Garlic' => 0.5,
    'Celery' => 0.2,
    'Asparagus' => 0.1,
    'Green Beans' => 0.2,
    'Peas' => 0.4,
    
    // FRUITS
    'Apple' => 0.2,
    'Banana' => 0.3,
    'Orange' => 0.1,
    'Strawberries' => 0.3,
    'Blueberries' => 0.3,
    'Raspberries' => 1,
    'Blackberries' => 0.5,
    'Grapes' => 0.2,
    'Watermelon' => 0.2,
    'Cantaloupe' => 0.2,
    'Pineapple' => 0.1,
    'Mango' => 0.4,
    'Peach' => 0.3,
    'Pear' => 0.1,
    'Kiwi' => 0.5,
    'Avocado' => 15,
    
    // NUTS & SEEDS
    'Almonds' => 50,
    'Walnuts' => 65,
    'Cashews' => 44,
    'Peanuts' => 49,
    'Pistachios' => 45,
    'Pecans' => 72,
    'Macadamia Nuts' => 76,
    'Hazelnuts' => 61,
    'Chia Seeds' => 31,
    'Flax Seeds' => 42,
    'Sunflower Seeds' => 51,
    'Pumpkin Seeds' => 49,
    'Sesame Seeds' => 50,
    
    // HEALTHY FATS
    'Olive Oil' => 100,
    'Coconut Oil' => 100,
    'Avocado Oil' => 100,
    'Butter' => 81,
    'Ghee' => 100,
    
    // BEVERAGES
    'Water' => 0,
    'Green Tea' => 0,
    'Black Coffee' => 0,
    'Almond Milk' => 1,
    'Coconut Milk' => 24,
    'Oat Milk' => 1,
    'Soy Milk' => 2,
    
    // PROTEIN POWDERS
    'Whey Protein' => 1,
    'Casein Protein' => 1,
    'Plant Protein' => 2,
    'Pea Protein' => 1,
    
    // CONDIMENTS
    'Honey' => 0,
    'Maple Syrup' => 0,
    'Hot Sauce' => 0.1,
    'Soy Sauce' => 0.1,
    'Balsamic Vinegar' => 0,
    'Apple Cider Vinegar' => 0,
    
    // SNACKS
    'Rice Cakes' => 3,
    'Popcorn' => 4,
    'Dark Chocolate' => 31,
    'Protein Bar' => 10,
];

$updateSql = "UPDATE fooditem 
              SET Fats_Grams = ?, Updated_at = ? 
              WHERE Name = ? AND is_deleted = 0";

$stmt = $conn->prepare($updateSql);
if (!$stmt) {
    die("Error preparing statement: " . $conn->error . "\n");
}

$updated = 0;
$notFound = 0;

foreach ($fatUpdates as $name => $fats) {
    $stmt->bind_param('dss', $fats, $now, $name);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            $updated++;
            echo "Updated: $name - Fats: {$fats}g\n";
        } else {
            $notFound++;
            echo "Not found: $name\n";
        }
    } else {
        echo "Error updating $name: " . $stmt->error . "\n";
    }
}

$stmt->close();

echo "\n=== Update Summary ===\n";
echo "Updated: $updated items\n";
echo "Not found: $notFound items\n";
echo "Total items to update: " . count($fatUpdates) . "\n";
?>

