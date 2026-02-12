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

// Load env from Admin/env or .env
$env_paths = [
    __DIR__ . '/../../../Admin/env',
    __DIR__ . '/../../../.env'
];

foreach ($env_paths as $env_path) {
    $env_content = @file_get_contents($env_path);
    if ($env_content !== false) {
        $lines = explode("\n", $env_content);
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line) || strpos($line, '#') === 0) continue;
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                putenv(trim($key) . '=' . trim($value));
                $_ENV[trim($key)] = trim($value);
            }
        }
        break; // Use first found env file
    }
}

// Define callGemini function if not already defined (from MealPlan.php)
if (!function_exists('callGemini')) {
    function callGemini($prompt, $model = 'gemini-2.5-flash', $jsonResponse = false) {
        $apiKey = getenv('GEMINI_API_KEY');
        if (!$apiKey) {
            return "Error: GEMINI_API_KEY not set in environment.";
        }

        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

        $data = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ]
        ];

        if ($jsonResponse) {
            $data['generationConfig'] = [
                'responseMimeType' => 'application/json'
            ];
        }

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            // Parse error response if it's JSON
            $errorData = json_decode($response, true);
            if ($errorData && isset($errorData['error'])) {
                $errorMsg = $errorData['error']['message'] ?? $errorData['error']['status'] ?? 'Unknown error';
                $errorCode = $errorData['error']['code'] ?? $httpCode;
                return "Error: API request failed with status {$errorCode}. {$errorMsg}";
            }
            return "Error: API request failed with status {$httpCode}. Response: {$response}";
        }

        $result = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return "Error: Invalid JSON response from API.";
        }

        $text = $result['candidates'][0]['content']['parts'][0]['text'] ?? 'Error: No response text found.';

        if ($jsonResponse) {
            $parsed = json_decode($text, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return "Error: Invalid JSON from LLM.";
            }
            return $parsed;
        }

        return $text;
    }
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ob_clean();
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!$data || empty($data['member_id'])) {
    ob_clean();
    echo json_encode(['success' => false, 'error' => 'Member ID is required']);
    exit;
}

$member_id = (int)$data['member_id'];

// Fetch user data
$user_query = "SELECT u.First_Name, u.Last_Name, u.Email, u.DOB, u.Gender, m.Body_fat, m.Height, m.Weight, m.BMI, m.Experience_Level, m.Training_Goals, m.Injuries, m.Medical_Condition 
               FROM userprofile u 
               JOIN memberprofile m ON u.User_ID = m.Member_Id 
               WHERE u.User_ID = ? AND m.is_deleted = 0 AND u.is_deleted = 0";
$stmt = $conn->prepare($user_query);
if (!$stmt) {
    ob_clean();
    echo json_encode(['success' => false, 'error' => 'Database prepare failed: ' . $conn->error]);
    exit;
}

$stmt->bind_param('i', $member_id);
if (!$stmt->execute()) {
    ob_clean();
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $stmt->error]);
    $stmt->close();
    exit;
}

$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    ob_clean();
    echo json_encode(['success' => false, 'error' => 'Member not found']);
    exit;
}

// Calculate age
$age = $user['DOB'] ? (date('Y') - date('Y', strtotime($user['DOB']))) : 30;

// Get all available food items from database FIRST to include in prompt
$foodItemsSql = "SELECT Name, Calories, Protein_Grams, Carbs_Grams, Fats_Grams 
                 FROM fooditem 
                 WHERE is_deleted = 0
                 ORDER BY Name
                 LIMIT 200"; // Limit to 200 to avoid prompt being too long
$foodItemsResult = $conn->query($foodItemsSql);
$availableFoodItems = [];
while ($row = $foodItemsResult->fetch_assoc()) {
    $availableFoodItems[] = $row['Name'];
}
$foodItemsList = implode(', ', array_slice($availableFoodItems, 0, 150)); // Show first 150 items

// Prepare prompt
$days = isset($data['days']) ? (int)$data['days'] : 7;
$goal = isset($data['goal']) ? $data['goal'] : "eat healthy based on goals: {$user['Training_Goals']}, considering medical: {$user['Medical_Condition']}, injuries: {$user['Injuries']}";

// Extract calorie and macro targets from goal if provided
$targetCalories = 2000; // Default
$targetCarbs = 250;
$targetProtein = 150;
$targetFats = 70;

// Try to extract from goal string (format: "achieve X calories per day with Yg carbs, Zg protein, and Wg fats")
if (preg_match('/achieve\s+(\d+)\s+calories/i', $goal, $calMatches)) {
    $targetCalories = (int)$calMatches[1];
}
if (preg_match('/(\d+)g\s+carbs/i', $goal, $carbMatches)) {
    $targetCarbs = (int)$carbMatches[1];
}
if (preg_match('/(\d+)g\s+protein/i', $goal, $proteinMatches)) {
    $targetProtein = (int)$proteinMatches[1];
}
if (preg_match('/(\d+)g\s+fats/i', $goal, $fatMatches)) {
    $targetFats = (int)$fatMatches[1];
}

$remainingItemsText = count($availableFoodItems) > 150 ? "\n\nAdditional available items: " . implode(', ', array_slice($availableFoodItems, 150)) : '';

$prompt = "You are a helpful nutritionist. Provide a {$days}-day meal plan for a user who wants to {$goal}. User details: Age {$age}, Gender {$user['Gender']}, Height {$user['Height']}cm, Weight {$user['Weight']}kg, BMI {$user['BMI']}, Body Fat {$user['Body_fat']}%, Experience {$user['Experience_Level']}.

CRITICAL CALORIE LIMIT: The TOTAL daily calories for ALL meals combined MUST NOT exceed {$targetCalories} calories. The target macros are: {$targetCarbs}g carbs, {$targetProtein}g protein, and {$targetFats}g fats per day. Ensure the sum of all meals (breakfast + lunch + dinner + snacks + pre-workout + post-workout) equals approximately {$targetCalories} calories per day, NOT more. Distribute calories appropriately across meals.

IMPORTANT - LOW CALORIE MEALS: You MUST create LOW-CALORIE meals. Each individual meal should be calorie-conscious:
- Prioritize lean proteins (chicken breast, turkey, fish, egg whites, tofu)
- Use plenty of vegetables (they are low in calories and high in nutrients)
- Choose whole grains in moderate portions
- Minimize high-calorie items like oils, nuts, and high-fat foods
- Keep each meal's calorie count as LOW as possible while meeting nutritional needs
- Breakfast should be 200-400 calories, Lunch 300-500 calories, Dinner 300-500 calories
- Pre-Workout and Post-Workout should be 100-300 calories each
- Snacks should be 50-200 calories each
- DO NOT create high-calorie meals. Keep them lean and nutritious.

CRITICAL: You MUST use ONLY the following food items that are available in our database. Use the EXACT names as listed (case-sensitive):
{$foodItemsList}{$remainingItemsText}

When listing ingredients in your response, you MUST use the EXACT names from the list above. Do NOT use variations, synonyms, or alternative names - use the exact names provided. For example, if the list says 'Chicken Breast', use 'Chicken Breast' exactly, not 'chicken' or 'chicken breast'. 

Respond only with a valid JSON object in the following format: 
{
  \"program\": {
    \"title\": \"{$days}-Day Meal Plan\",
    \"description\": \"Balanced meal plan generated by AI for {$goal}\",
    \"totalCalories\": {$targetCalories},
    \"carbs\": {$targetCarbs},
    \"protein\": {$targetProtein},
    \"fats\": {$targetFats},
    \"days\": {$days}
  },
  \"days\": {
    \"day1\": [
      {\"meal\": \"breakfast\", \"name\": \"Greek Yogurt with Berries\", \"description\": \"A light and nutritious start to the day...\", \"calories\": 250, \"protein\": 20, \"carbs\": 30, \"fats\": 5, \"ingredients\": [\"Greek Yogurt\", \"Strawberries\", \"Blueberries\"], \"instructions\": \"Mix yogurt with fresh berries.\"},
      {\"meal\": \"lunch\", \"name\": \"Grilled Chicken Salad\", \"description\": \"Lean protein with fresh vegetables...\", \"calories\": 350, \"protein\": 35, \"carbs\": 20, \"fats\": 12, \"ingredients\": [\"Chicken Breast\", \"Lettuce\", \"Tomato\", \"Cucumber\", \"Bell Pepper\"], \"instructions\": \"Grill chicken and serve over mixed greens.\"},
      {\"meal\": \"dinner\", \"name\": \"Baked Salmon with Vegetables\", \"description\": \"Omega-3 rich fish with steamed vegetables...\", \"calories\": 400, \"protein\": 30, \"carbs\": 25, \"fats\": 18, \"ingredients\": [\"Salmon\", \"Broccoli\", \"Asparagus\", \"Brown Rice\"], \"instructions\": \"Bake salmon and steam vegetables.\"},
      {\"meal\": \"pre-workout\", \"name\": \"Banana and Almonds\", \"description\": \"Quick energy boost...\", \"calories\": 200, \"protein\": 5, \"carbs\": 25, \"fats\": 10, \"ingredients\": [\"Banana\", \"Almonds\"], \"instructions\": \"Eat 30 minutes before workout.\"},
      {\"meal\": \"post-workout\", \"name\": \"Protein Smoothie\", \"description\": \"Recovery nutrition...\", \"calories\": 250, \"protein\": 25, \"carbs\": 30, \"fats\": 3, \"ingredients\": [\"Whey Protein\", \"Almond Milk\", \"Banana\"], \"instructions\": \"Blend ingredients together.\"},
      {\"meal\": \"snacks\", \"name\": \"Apple with Peanut Butter\", \"description\": \"Healthy snack option...\", \"calories\": 150, \"protein\": 5, \"carbs\": 20, \"fats\": 6, \"ingredients\": [\"Apple\", \"Peanuts\"], \"instructions\": \"Slice apple and serve with peanut butter.\"}
    ],
    \"day2\": [...],
    ... up to day{$days}
  }
}

CRITICAL REQUIREMENTS - READ CAREFULLY:
1. You MUST include at least ONE meal for EACH of these REQUIRED categories in EVERY SINGLE DAY:
   - \"breakfast\" (REQUIRED - must appear in every day)
   - \"lunch\" (REQUIRED - must appear in every day)
   - \"dinner\" (REQUIRED - must appear in every day)
   - \"snacks\" (REQUIRED - must appear in every day)
   - \"pre-workout\" (REQUIRED - must appear in every day)
   - \"post-workout\" (REQUIRED - must appear in every day)

2. Each day MUST have at least 6 meals: breakfast, lunch, dinner, snacks, pre-workout, and post-workout. Do NOT skip any of these categories.

3. CALORIE DISTRIBUTION - KEEP MEALS LOW CALORIE: Distribute the {$targetCalories} daily calories across all 6 meals. Keep each meal LOW in calories:
   - Breakfast: 200-400 calories (aim for the lower end - use lean proteins, vegetables, and whole grains)
   - Lunch: 300-500 calories (prioritize vegetables, lean proteins, and moderate carbs)
   - Dinner: 300-500 calories (focus on lean proteins and vegetables, minimal high-calorie items)
   - Pre-Workout: 100-300 calories (light, easily digestible foods)
   - Post-Workout: 150-350 calories (protein-rich recovery meal)
   - Snacks: 50-200 calories (light, nutrient-dense options)
   
   IMPORTANT: Keep individual meal calories LOW. Use lean proteins, plenty of vegetables, and moderate portions of grains. Avoid high-calorie items like excessive oils, nuts, cheese, and processed foods. The TOTAL of all meals per day MUST equal approximately {$targetCalories} calories, NOT more. Double-check your math before responding!

4. For ingredients, you MUST use ONLY the exact food item names from the list provided above. Do NOT invent new food names or use variations. Use the exact names as they appear in the list (e.g., if the list says 'Chicken Breast', use 'Chicken Breast' exactly, not 'chicken' or 'chicken breast').

5. Vary meals across days to ensure balanced nutrition and prevent monotony.

6. Ensure all meal and program details are provided in the response with accurate calorie counts. Include protein, carbs, and fats for each meal in the JSON.

7. Make sure the \"meal\" field in each meal object matches exactly: \"breakfast\", \"lunch\", \"dinner\", \"snacks\", \"pre-workout\", or \"post-workout\".

8. LOW-CALORIE PRIORITY: When selecting food items, prioritize:
   - Lean proteins: Chicken Breast, Turkey Breast, Tuna, Cod, Egg White, Greek Yogurt, Cottage Cheese
   - Low-calorie vegetables: Spinach, Kale, Lettuce, Broccoli, Cauliflower, Cucumber, Celery, Asparagus, Bell Pepper, Tomato
   - Moderate whole grains: Brown Rice, Quinoa, Oats (in small portions)
   - Avoid or minimize: High-fat items like oils, butter, nuts, cheese (use sparingly)
   - Choose water-rich, fiber-rich foods that are naturally low in calories

9. Each meal should be nutritionally dense but calorie-light. Focus on volume (lots of vegetables) with moderate protein and carbs.";

// Call Gemini API with retry logic
$maxRetries = 3;
$retryDelay = 2; // seconds
$response = null;
$lastError = null;

for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
    $response = callGemini($prompt, 'gemini-2.5-flash', true);
    
    // Check if response is an error
    if (is_string($response) && (strpos($response, 'Error:') === 0)) {
        $lastError = $response;
        
        // Check if it's a retryable error (503, overloaded, UNAVAILABLE)
        $isRetryable = (strpos($response, '503') !== false || 
                       strpos($response, 'overloaded') !== false || 
                       strpos($response, 'UNAVAILABLE') !== false);
        
        // If it's a retryable error and we have retries left, wait and retry
        if (($attempt < $maxRetries) && $isRetryable) {
            sleep($retryDelay * $attempt); // Exponential backoff: 2s, 4s, 6s
            continue;
        }
        
        // If no more retries or non-retryable error, return error
        ob_clean();
        $isRetryable = (strpos($response, '503') !== false || 
                       strpos($response, 'overloaded') !== false || 
                       strpos($response, 'UNAVAILABLE') !== false);
        echo json_encode([
            'success' => false, 
            'error' => 'Failed to generate plan: ' . $response,
            'attempt' => $attempt,
            'retryable' => $isRetryable
        ]);
        exit;
    }
    
    // If we got a valid array response, break out of retry loop
    if (is_array($response)) {
        break;
    }
    
    // If it's the last attempt and still an error
    if ($attempt === $maxRetries) {
        ob_clean();
        echo json_encode([
            'success' => false, 
            'error' => 'Failed to generate plan after ' . $maxRetries . ' attempts: ' . ($lastError ?: 'Unknown error')
        ]);
        exit;
    }
}

if (!is_array($response)) {
    ob_clean();
    echo json_encode(['success' => false, 'error' => 'Failed to generate plan: Invalid response from AI service']);
    exit;
}

// Parse the JSON response
$program = $response['program'] ?? [];
$days_data = $response['days'] ?? [];

// Debug: log what we received
error_log("Program data: " . json_encode($program));
error_log("Days data keys: " . implode(', ', array_keys($days_data)));
error_log("Days data structure: " . json_encode(array_slice($days_data, 0, 1, true)));

// Transform the response to match our meal plan structure
// We'll consolidate all days into a single day's meal plan (taking meals from day1, or averaging)
$mealsByCategory = [
    'Breakfast' => [],
    'Lunch' => [],
    'Dinner' => [],
    'Pre-Workout' => [],
    'Post-Workout' => [],
    'Snacks' => []
];

// Process all days and collect unique meals by category
error_log("Processing " . count($days_data) . " days of meal data");
$mealsProcessed = 0;

foreach ($days_data as $day => $meals) {
    if (!is_array($meals)) {
        error_log("Day {$day} meals is not an array: " . gettype($meals));
        continue;
    }
    
    error_log("Processing day {$day} with " . count($meals) . " meals");
    
    foreach ($meals as $meal) {
        if (!is_array($meal)) {
            error_log("Meal is not an array: " . gettype($meal));
            continue;
        }
        
        $mealType = strtolower($meal['meal'] ?? 'breakfast');
        $name = $meal['name'] ?? 'Unknown Meal';
        $ingredients = is_array($meal['ingredients'] ?? []) ? $meal['ingredients'] : [];
        
        if (empty($name) || $name === 'Unknown Meal') {
            error_log("Skipping meal with invalid name");
            continue;
        }
        
        // Map meal types to our categories
        $category = 'Breakfast';
        if ($mealType === 'lunch') $category = 'Lunch';
        elseif ($mealType === 'dinner') $category = 'Dinner';
        elseif ($mealType === 'pre-workout' || $mealType === 'preworkout') $category = 'Pre-Workout';
        elseif ($mealType === 'post-workout' || $mealType === 'postworkout') $category = 'Post-Workout';
        elseif ($mealType === 'snack' || $mealType === 'snacks') $category = 'Snacks';
        
        // Check if this meal already exists (avoid duplicates)
        $exists = false;
        foreach ($mealsByCategory[$category] as $existingMeal) {
            if (strtolower($existingMeal['name']) === strtolower($name)) {
                $exists = true;
                break;
            }
        }
        
        if (!$exists) {
            $mealsByCategory[$category][] = [
                'name' => $name,
                'ingredients' => $ingredients,
                'description' => $meal['description'] ?? '',
                'calories' => $meal['calories'] ?? 0
            ];
            $mealsProcessed++;
            error_log("Added meal '{$name}' to category '{$category}' with " . count($ingredients) . " ingredients");
        }
    }
}

error_log("Total meals processed: {$mealsProcessed}");
error_log("Meals by category: " . json_encode(array_map('count', $mealsByCategory)));

// Ensure all categories have at least one meal
// If a category is empty, try to find meals from other days or create a placeholder
$requiredCategories = ['Breakfast', 'Lunch', 'Dinner', 'Snacks', 'Pre-Workout', 'Post-Workout'];

foreach ($requiredCategories as $category) {
    if (count($mealsByCategory[$category]) === 0) {
        error_log("Category '{$category}' is empty, trying to find meals from other days");
        
        // Try to find a meal from other days for this category
        foreach ($days_data as $day => $meals) {
            if (!is_array($meals)) continue;
            
            foreach ($meals as $meal) {
                if (!is_array($meal)) continue;
                
                $mealType = strtolower($meal['meal'] ?? '');
                $name = $meal['name'] ?? '';
                
                // Check if this meal matches the category we need
                $matchesCategory = false;
                if ($category === 'Breakfast' && ($mealType === 'breakfast' || $mealType === '')) $matchesCategory = true;
                elseif ($category === 'Lunch' && $mealType === 'lunch') $matchesCategory = true;
                elseif ($category === 'Dinner' && $mealType === 'dinner') $matchesCategory = true;
                elseif ($category === 'Snacks' && ($mealType === 'snack' || $mealType === 'snacks')) $matchesCategory = true;
                elseif ($category === 'Pre-Workout' && ($mealType === 'pre-workout' || $mealType === 'preworkout')) $matchesCategory = true;
                elseif ($category === 'Post-Workout' && ($mealType === 'post-workout' || $mealType === 'postworkout')) $matchesCategory = true;
                
                if ($matchesCategory && !empty($name) && $name !== 'Unknown Meal') {
                    $ingredients = is_array($meal['ingredients'] ?? []) ? $meal['ingredients'] : [];
                    
                    $mealsByCategory[$category][] = [
                        'name' => $name,
                        'ingredients' => $ingredients,
                        'description' => $meal['description'] ?? '',
                        'calories' => $meal['calories'] ?? 0
                    ];
                    error_log("Added meal '{$name}' to empty category '{$category}' from day {$day}");
                    break 2; // Break out of both loops
                }
            }
        }
        
        // If still empty, create a placeholder meal with proper defaults
        if (count($mealsByCategory[$category]) === 0) {
            $placeholderMeals = [
                'Breakfast' => [
                    'name' => 'Healthy Breakfast', 
                    'ingredients' => ['eggs', 'whole grain bread', 'fruit'],
                    'calories' => 400,
                    'description' => 'A balanced breakfast to start your day. Please customize with specific food items.'
                ],
                'Lunch' => [
                    'name' => 'Balanced Lunch', 
                    'ingredients' => ['chicken', 'rice', 'vegetables'],
                    'calories' => 600,
                    'description' => 'A nutritious lunch to fuel your afternoon. Please customize with specific food items.'
                ],
                'Dinner' => [
                    'name' => 'Nutritious Dinner', 
                    'ingredients' => ['fish', 'quinoa', 'salad'],
                    'calories' => 600,
                    'description' => 'A healthy dinner to end your day. Please customize with specific food items.'
                ],
                'Snacks' => [
                    'name' => 'Healthy Snack', 
                    'ingredients' => ['nuts', 'yogurt'],
                    'calories' => 200,
                    'description' => 'A nutritious snack option. Please customize with specific food items.'
                ],
                'Pre-Workout' => [
                    'name' => 'Pre-Workout Fuel', 
                    'ingredients' => ['banana', 'oatmeal', 'honey'],
                    'calories' => 300,
                    'description' => 'Energy-boosting meal before your workout. Please customize with specific food items.'
                ],
                'Post-Workout' => [
                    'name' => 'Post-Workout Recovery', 
                    'ingredients' => ['protein shake', 'banana', 'almonds'],
                    'calories' => 400,
                    'description' => 'Recovery meal to refuel after your workout. Please customize with specific food items.'
                ]
            ];
            
            if (isset($placeholderMeals[$category])) {
                $placeholder = $placeholderMeals[$category];
                $mealsByCategory[$category][] = [
                    'name' => $placeholder['name'],
                    'ingredients' => $placeholder['ingredients'],
                    'description' => $placeholder['description'],
                    'calories' => $placeholder['calories']
                ];
                error_log("Created placeholder meal for category '{$category}' with {$placeholder['calories']} calories");
            }
        }
    }
}

// Get all food items from database to match ingredients
$foodItemsSql = "SELECT Food_Item_ID, Name, Calories, Protein_Grams, Carbs_Grams, Fats_Grams, Fiber_Grams, Sugar_Grams, Serving_Size
                FROM fooditem 
                WHERE is_deleted = 0
                ORDER BY Name";
$foodItemsResult = $conn->query($foodItemsSql);
$allFoodItems = [];
$allFoodItemsByName = [];
while ($row = $foodItemsResult->fetch_assoc()) {
    $nameLower = strtolower(trim($row['Name']));
    $allFoodItems[$nameLower] = $row;
    $allFoodItemsByName[] = $row; // Keep array for fallback
}

// Build the response structure
$formattedMeals = [];
$totalFoodItems = count($allFoodItemsByName);
error_log("Total food items in database: {$totalFoodItems}");

foreach ($mealsByCategory as $category => $meals) {
    if (count($meals) > 0) {
        $formattedMeals[$category] = [];
        foreach ($meals as $meal) {
            $foodItems = [];
            $mealCalories = 0;
            $mealProtein = 0;
            $mealCarbs = 0;
            $mealFats = 0;
            
            $ingredients = is_array($meal['ingredients']) ? $meal['ingredients'] : [];
            error_log("Processing meal '{$meal['name']}' in category '{$category}' with " . count($ingredients) . " ingredients");
            
            foreach ($ingredients as $ingredient) {
                if (empty(trim($ingredient))) continue;
                $ingredientLower = strtolower(trim($ingredient));
                $matchedFood = null;
                
                // First try exact match
                if (isset($allFoodItems[$ingredientLower])) {
                    $matchedFood = $allFoodItems[$ingredientLower];
                } else {
                    // Try partial match
                    foreach ($allFoodItems as $foodName => $foodData) {
                        // Check if ingredient is in food name or vice versa
                        if (strpos($foodName, $ingredientLower) !== false || 
                            strpos($ingredientLower, $foodName) !== false ||
                            strpos($foodName, str_replace(' ', '', $ingredientLower)) !== false) {
                            $matchedFood = $foodData;
                            break;
                        }
                    }
                }
                
                // If still not found, try word-by-word matching
                if (!$matchedFood) {
                    $ingredientWords = explode(' ', $ingredientLower);
                    foreach ($allFoodItems as $foodName => $foodData) {
                        $matchCount = 0;
                        foreach ($ingredientWords as $word) {
                            if (strlen($word) > 2 && strpos($foodName, $word) !== false) {
                                $matchCount++;
                            }
                        }
                        if ($matchCount >= min(2, count($ingredientWords))) {
                            $matchedFood = $foodData;
                            break;
                        }
                    }
                }
                
                // Last resort: use a generic food item if available
                if (!$matchedFood && count($allFoodItemsByName) > 0) {
                    // Try to find a similar category (e.g., chicken, rice, etc.)
                    $genericMatches = ['chicken' => ['chicken', 'poultry'], 'rice' => ['rice'], 
                                      'egg' => ['egg'], 'milk' => ['milk'], 'bread' => ['bread']];
                    foreach ($genericMatches as $key => $terms) {
                        foreach ($terms as $term) {
                            if (strpos($ingredientLower, $term) !== false) {
                                foreach ($allFoodItemsByName as $food) {
                                    if (stripos($food['Name'], $key) !== false) {
                                        $matchedFood = $food;
                                        break 2;
                                    }
                                }
                            }
                        }
                    }
                    
                    // If still no match, use first item
                    if (!$matchedFood) {
                        $matchedFood = $allFoodItemsByName[0];
                    }
                }
                
                if ($matchedFood) {
                    $servings = 1; // Default serving
                    $foodItems[] = [
                        'food_item_id' => (int)$matchedFood['Food_Item_ID'],
                        'quantity_servings' => $servings,
                        'name' => $matchedFood['Name']
                    ];
                    
                    // Add to meal totals
                    $mealCalories += $matchedFood['Calories'] * $servings;
                    $mealProtein += $matchedFood['Protein_Grams'] * $servings;
                    $mealCarbs += $matchedFood['Carbs_Grams'] * $servings;
                    $mealFats += $matchedFood['Fats_Grams'] * $servings;
                }
            }
            
            // ALWAYS include the meal, even if no food items matched
            // Doctor can manually add food items later
            
            // Priority: Use AI-provided macros if available, otherwise use calculated from food items
            $aiCalories = isset($meal['calories']) && $meal['calories'] > 0 ? (int)$meal['calories'] : 0;
            $aiProtein = isset($meal['protein']) && $meal['protein'] > 0 ? (float)$meal['protein'] : 0;
            $aiCarbs = isset($meal['carbs']) && $meal['carbs'] > 0 ? (float)$meal['carbs'] : 0;
            $aiFats = isset($meal['fats']) && $meal['fats'] > 0 ? (float)$meal['fats'] : 0;
            
            // If AI provided macros, use them (they're more accurate)
            if ($aiCalories > 0 && ($aiProtein > 0 || $aiCarbs > 0 || $aiFats > 0)) {
                $finalCalories = $aiCalories;
                $finalProtein = $aiProtein > 0 ? $aiProtein : round(($finalCalories * 0.3) / 4);
                $finalCarbs = $aiCarbs > 0 ? $aiCarbs : round(($finalCalories * 0.5) / 4);
                $finalFats = $aiFats > 0 ? $aiFats : round(($finalCalories * 0.2) / 9);
            } else {
                // Use calculated values from food items if available
                $finalCalories = $mealCalories > 0 ? $mealCalories : ($aiCalories > 0 ? $aiCalories : 0);
                $finalProtein = $mealProtein > 0 ? $mealProtein : 0;
                $finalCarbs = $mealCarbs > 0 ? $mealCarbs : 0;
                $finalFats = $mealFats > 0 ? $mealFats : 0;
                
                // If still no calories, set defaults based on meal type
                if ($finalCalories <= 0) {
                    if ($category === 'Breakfast') $finalCalories = 400;
                    elseif ($category === 'Lunch') $finalCalories = 600;
                    elseif ($category === 'Dinner') $finalCalories = 600;
                    elseif ($category === 'Snacks') $finalCalories = 200;
                    else $finalCalories = 300;
                }
                
                // If macros are zero but we have calories, estimate from calories
                // Use more balanced macro distribution: 30% protein, 50% carbs, 20% fats (typical for active individuals)
                if ($finalProtein <= 0 && $finalCalories > 0) $finalProtein = round(($finalCalories * 0.3) / 4);
                if ($finalCarbs <= 0 && $finalCalories > 0) $finalCarbs = round(($finalCalories * 0.5) / 4);
                if ($finalFats <= 0 && $finalCalories > 0) $finalFats = round(($finalCalories * 0.2) / 9);
            }
            
            // Verify macro accuracy: calories should equal (protein*4 + carbs*4 + fats*9)
            $calculatedCalories = ($finalProtein * 4) + ($finalCarbs * 4) + ($finalFats * 9);
            if (abs($finalCalories - $calculatedCalories) > 50) {
                // If there's a significant discrepancy, adjust macros to match calories
                $totalMacroCalories = $calculatedCalories;
                if ($totalMacroCalories > 0) {
                    $ratio = $finalCalories / $totalMacroCalories;
                    $finalProtein = round($finalProtein * $ratio);
                    $finalCarbs = round($finalCarbs * $ratio);
                    $finalFats = round($finalFats * $ratio);
                }
            }
            
            $formattedMeals[$category][] = [
                'name' => $meal['name'],
                'calories' => (int)$finalCalories,
                'protein' => (int)$finalProtein,
                'carbs' => (int)$finalCarbs,
                'fats' => (int)$finalFats,
                'food_items' => $foodItems,
                'ingredients' => $ingredients // Include original ingredients for reference
            ];
        }
    }
}

ob_clean();
echo json_encode([
    'success' => true,
    'program' => $program,
    'meals' => $formattedMeals,
    'message' => 'Meal plan generated successfully'
]);
ob_end_flush();
?>

