<?php
require_once 'DB.php';
ini_set('display_errors',1);
error_reporting(E_ALL);

$queries = [
    "SET FOREIGN_KEY_CHECKS=0",
    "ALTER TABLE MealPlan MODIFY Meal_Plan_ID INT NOT NULL AUTO_INCREMENT",
    "ALTER TABLE MealPlan MODIFY Title VARCHAR(255) NOT NULL",
    "ALTER TABLE MealPlan MODIFY Description TEXT NOT NULL",
    "ALTER TABLE Meal MODIFY Meal_ID INT NOT NULL AUTO_INCREMENT",
    "ALTER TABLE FoodItem MODIFY Food_Item_ID INT NOT NULL AUTO_INCREMENT",
    "ALTER TABLE MealPlanItem MODIFY Meal_Plan_Item_id INT NOT NULL AUTO_INCREMENT",
    "SET FOREIGN_KEY_CHECKS=1"
];

foreach ($queries as $q) {
    if ($conn->query($q) === TRUE) {
        echo "OK: $q\n";
    } else {
        echo "ERR: $q -- " . $conn->error . "\n";
    }
}

echo "Done\n";