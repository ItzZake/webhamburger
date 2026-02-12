<?php
require_once __DIR__ . '/../../../DB.php';
header('Content-Type: application/json');

$now = date('Y-m-d H:i:s');
$inserted = 0;

// Try to pick a nutritionist to assign these meals to
$nuts = select('nutritionistprofile');
if (empty($nuts)) {
    // create a default nutritionist
    $uid = insert('userprofile', [
        'Email' => 'seed_nutri@example.local',
        'Password' => '',
        'Last_Login' => '0000-00-00',
        'First_Name' => 'Seed',
        'Last_Name' => 'Nutritionist',
        'Phone_Number' => 0,
        'DOB' => '0000-00-00',
        'Role' => 'nutritionist',
        'Gender' => '',
        'Is_Active' => 1,
        'Profile_pic_url' => '',
        'Created_at' => $now,
        'Updated_at' => $now,
        'is_deleted' => 0,
    ]);
    if ($uid !== false) {
        insert('nutritionistprofile', [
            'Nutritionist_ID' => $uid,
            'Bio' => 'Auto-created seeder nutritionist',
            'Certifications' => '',
            'rating_count' => 0,
            'Avg_rating' => 0,
            'Is_accepting_new' => 1,
            'Years_Experience' => 1,
            'Specialization_Main' => 'General',
            'Clinic_Location' => '',
            'Created_at' => $now,
            'Updated_at' => $now,
            'is_deleted' => 0,
        ]);
        $nuts = select('nutritionistprofile');
    }
}

$nutritionist_id = (int)$nuts[0]['Nutritionist_ID'];

// Static meals list (from MealPlans.js)
$staticMeals = [
    ['name'=>'Oatmeal with Berries','calories'=>350,'protein'=>'12g','description'=>'Whole grain oats topped with blueberries and almonds for sustained energy'],
    ['name'=>'Scrambled Eggs & Toast','calories'=>400,'protein'=>'18g','description'=>'3 eggs with whole wheat toast and avocado'],
    ['name'=>'Protein Pancakes','calories'=>380,'protein'=>'25g','description'=>'Fluffy pancakes made with protein powder, topped with Greek yogurt'],
    ['name'=>'Greek Yogurt Parfait','calories'=>320,'protein'=>'15g','description'=>'Greek yogurt layered with granola, honey, and fresh fruit'],

    ['name'=>'Grilled Chicken Breast','calories'=>450,'protein'=>'45g','description'=>'6oz grilled chicken with brown rice and steamed broccoli'],
    ['name'=>'Salmon & Quinoa Bowl','calories'=>480,'protein'=>'38g','description'=>'Fresh salmon with quinoa, roasted vegetables, and olive oil dressing'],
    ['name'=>'Turkey Wrap','calories'=>420,'protein'=>'32g','description'=>'Whole wheat wrap with lean turkey, lettuce, tomato, and hummus'],
    ['name'=>'Tuna Salad','calories'=>360,'protein'=>'35g','description'=>'Canned tuna mixed with Greek yogurt, served with mixed greens'],

    ['name'=>'Lean Beef Steak','calories'=>520,'protein'=>'50g','description'=>'8oz sirloin steak with sweet potato and green beans'],
    ['name'=>'Grilled Tilapia','calories'=>420,'protein'=>'42g','description'=>'White fish fillet with jasmine rice and asparagus'],
    ['name'=>'Chicken Stir-Fry','calories'=>480,'protein'=>'40g','description'=>'Chicken breast with mixed vegetables and brown rice'],
    ['name'=>'Baked Cod','calories'=>390,'protein'=>'48g','description'=>'Herb-baked cod with roasted root vegetables'],

    ['name'=>'Banana & Peanut Butter','calories'=>280,'protein'=>'8g','description'=>'Medium banana with 2 tbsp natural peanut butter'],
    ['name'=>'Rice Cakes & Honey','calories'=>250,'protein'=>'4g','description'=>'2 rice cakes with honey for quick carbs'],
    ['name'=>'Energy Bar','calories'=>200,'protein'=>'10g','description'=>'Protein-rich energy bar for sustained energy'],
    ['name'=>'Apple & Almond Butter','calories'=>260,'protein'=>'7g','description'=>'Medium apple with 1.5 tbsp almond butter'],

    ['name'=>'Protein Shake','calories'=>300,'protein'=>'30g','description'=>'Whey protein with banana and whole milk'],
    ['name'=>'Chicken & White Rice','calories'=>420,'protein'=>'40g','description'=>'4oz chicken breast with white rice for fast carbs'],
    ['name'=>'Greek Yogurt Smoothie','calories'=>280,'protein'=>'25g','description'=>'Greek yogurt with berries and honey'],
    ['name'=>'Tuna Sandwich','calories'=>380,'protein'=>'35g','description'=>'Canned tuna on white bread with lettuce'],

    ['name'=>'Mixed Nuts','calories'=>160,'protein'=>'6g','description'=>'Handful of almonds, cashews, and walnuts'],
    ['name'=>'Protein Bar','calories'=>200,'protein'=>'20g','description'=>'High-protein, low-sugar nutrition bar'],
    ['name'=>'Cheese & Crackers','calories'=>180,'protein'=>'8g','description'=>'String cheese with whole grain crackers'],
    ['name'=>'Cottage Cheese','calories'=>110,'protein'=>'14g','description'=>'1/2 cup low-fat cottage cheese with berries'],
];

foreach ($staticMeals as $m) {
    // avoid duplicates by name + doctor
    $found = $conn->prepare('SELECT Meal_ID FROM meal WHERE Name = ? AND Doctor_Id = ? LIMIT 1');
    $found->bind_param('si', $m['name'], $nutritionist_id);
    $found->execute();
    $res = $found->get_result();
    if ($row = $res->fetch_assoc()) { $found->close(); continue; }
    $found->close();

    $data = [
        'Name' => $m['name'],
        'Description' => $m['description'],
        'Calories' => isset($m['calories']) ? (int)$m['calories'] : 0,
        'Protein' => isset($m['protein']) ? (string)$m['protein'] : '',
        'Carbs' => 0,
        'Fat' => 0,
        'Doctor_Id' => $nutritionist_id,
        'Created_at' => $now,
        'Updated_at' => $now,
        'is_deleted' => 0,
    ];
    $id = insert('meal', $data);
    if ($id !== false) $inserted++;
}

echo json_encode(['inserted' => $inserted]);
