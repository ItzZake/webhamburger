<?php
/**
 * Update script for Food Items - Add missing carb values
 * This script updates existing food items with correct carb values
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

// Food items with correct carb values
// Format: ['Name' => Carbs_Grams]
$carbUpdates = [
    // GRAINS & CARBOHYDRATES
    'Brown Rice' => 23,
    'White Rice' => 28,
    'Oats' => 66,
    'Whole Wheat Bread' => 41,
    'White Bread' => 49,
    'Whole Wheat Pasta' => 25,
    'White Pasta' => 25,
    'Barley' => 28,
    'Bulgur' => 19,
    'Couscous' => 23,
    'Quinoa' => 22,
    
    // POTATOES & STARCHES
    'Sweet Potato' => 20,
    'White Potato' => 17,
    'Red Potato' => 15,
    'Yam' => 28,
    'Corn' => 21,
    
    // LEGUMES & BEANS
    'Lentils' => 20,
    'Chickpeas' => 27,
    'Black Beans' => 23,
    'Kidney Beans' => 22,
    'Edamame' => 10,
    
    // FRUITS
    'Apple' => 14,
    'Banana' => 23,
    'Orange' => 12,
    'Strawberries' => 8,
    'Blueberries' => 14,
    'Raspberries' => 12,
    'Blackberries' => 10,
    'Grapes' => 18,
    'Watermelon' => 8,
    'Cantaloupe' => 8,
    'Pineapple' => 13,
    'Mango' => 15,
    'Peach' => 10,
    'Pear' => 15,
    'Kiwi' => 15,
    'Avocado' => 9,
    
    // VEGETABLES
    'Spinach' => 3,
    'Kale' => 9,
    'Lettuce' => 3,
    'Arugula' => 4,
    'Swiss Chard' => 4,
    'Collard Greens' => 6,
    'Cabbage' => 6,
    'Bok Choy' => 2,
    'Broccoli' => 7,
    'Cauliflower' => 5,
    'Brussels Sprouts' => 9,
    'Cabbage Red' => 7,
    'Carrots' => 10,
    'Bell Pepper' => 7,
    'Tomato' => 4,
    'Cucumber' => 4,
    'Zucchini' => 3,
    'Eggplant' => 6,
    'Mushrooms' => 3,
    'Onion' => 9,
    'Garlic' => 33,
    'Celery' => 3,
    'Asparagus' => 4,
    'Green Beans' => 7,
    'Peas' => 14,
    
    // DAIRY (some have carbs)
    'Greek Yogurt' => 4,
    'Cottage Cheese' => 3,
    'Mozzarella Cheese' => 1,
    'Cheddar Cheese' => 0,
    'Feta Cheese' => 1,
    'Parmesan Cheese' => 0,
    
    // BEVERAGES
    'Almond Milk' => 1,
    'Coconut Milk' => 6,
    'Oat Milk' => 10,
    'Soy Milk' => 6,
    
    // CONDIMENTS
    'Honey' => 82,
    'Maple Syrup' => 67,
    'Hot Sauce' => 1,
    'Soy Sauce' => 6,
    'Balsamic Vinegar' => 17,
    'Apple Cider Vinegar' => 1,
    
    // SNACKS
    'Rice Cakes' => 8,
    'Popcorn' => 78,
    'Dark Chocolate' => 45,
    'Protein Bar' => 30,
    
    // NUTS & SEEDS (low carb but not zero)
    'Almonds' => 21,
    'Walnuts' => 15,
    'Cashews' => 30,
    'Peanuts' => 26,
    'Pistachios' => 20,
    'Pecans' => 9,
    'Macadamia Nuts' => 14,
    'Hazelnuts' => 17,
    'Chia Seeds' => 17,
    'Flax Seeds' => 29,
    'Sunflower Seeds' => 20,
    'Pumpkin Seeds' => 10,
    'Sesame Seeds' => 18,
    
    // PROTEIN POWDERS
    'Whey Protein' => 1,
    'Casein Protein' => 1,
    'Plant Protein' => 2,
    'Pea Protein' => 1,
];

$updateSql = "UPDATE fooditem 
              SET Carbs_Grams = ?, Updated_at = ? 
              WHERE Name = ? AND is_deleted = 0";

$stmt = $conn->prepare($updateSql);
if (!$stmt) {
    die("Error preparing statement: " . $conn->error . "\n");
}

$updated = 0;
$notFound = 0;

foreach ($carbUpdates as $name => $carbs) {
    $stmt->bind_param('iss', $carbs, $now, $name);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            $updated++;
            echo "Updated: $name - Carbs: {$carbs}g\n";
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
echo "Total items to update: " . count($carbUpdates) . "\n";
?>

