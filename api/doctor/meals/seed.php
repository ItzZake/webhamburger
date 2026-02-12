<?php
require_once __DIR__ . '/../../../DB.php';
header('Content-Type: application/json');

$now = date('Y-m-d H:i:s');
$created = 0;

// pick a nutritionist (create one if none)
$nuts = select('nutritionistprofile');
if (empty($nuts)) {
    $u = insert('userprofile', [
        'Email' => 'auto_nutri@example.local',
        'Password' => '',
        'Last_Login' => '0000-00-00',
        'First_Name' => 'Auto',
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
    if ($u !== false) {
        insert('nutritionistprofile', [
            'Nutritionist_ID' => $u,
            'Bio' => 'Auto-created for meal seeds',
            'Certifications' => '',
            'Nutritionist_Available_Slots' => '',
            'Max_Nutritionist_Session' => 10,
            'Specialization_Main' => 'General',
            'Specialization_Other' => '',
            'Youtube_Url' => '',
            'Instagram_Url' => '',
            'Created_At' => $now,
            'Updated_At' => $now,
            'is_deleted' => 0,
        ]);
        $nuts = select('nutritionistprofile');
    }
}

$nutritionist_id = (int)$nuts[0]['Nutritionist_ID'];

$samples = [
    ['Name'=>'Protein Pancakes','Description'=>'High protein pancakes','Calories'=>400,'Protein'=>30,'Carbs'=>40,'Fat'=>10],
    ['Name'=>'Chicken Salad','Description'=>'Lean protein salad','Calories'=>350,'Protein'=>35,'Carbs'=>20,'Fat'=>12],
    ['Name'=>'Quinoa Bowl','Description'=>'Quinoa, roasted veggies and tuna','Calories'=>520,'Protein'=>38,'Carbs'=>62,'Fat'=>14],
    ['Name'=>'Tofu Stir-Fry','Description'=>'Stir-fried tofu with mixed vegetables','Calories'=>430,'Protein'=>28,'Carbs'=>48,'Fat'=>10],
    ['Name'=>'Steak & Sweet Potato','Description'=>'Grilled steak with sweet potato mash','Calories'=>700,'Protein'=>55,'Carbs'=>60,'Fat'=>28],
    ['Name'=>'Green Smoothie','Description'=>'Spinach, apple and protein powder','Calories'=>230,'Protein'=>18,'Carbs'=>30,'Fat'=>2],
];

foreach ($samples as $s) {
    // avoid duplicates per nutritionist
    $found = $conn->prepare('SELECT Meal_ID FROM meal WHERE Name = ? AND Doctor_Id = ? LIMIT 1');
    $found->bind_param('si', $s['Name'], $nutritionist_id);
    $found->execute();
    $res = $found->get_result();
    if (!$res->fetch_assoc()) {
        $data = [
            'Name' => $s['Name'],
            'Description' => $s['Description'],
            'Calories' => $s['Calories'],
            'Protein' => $s['Protein'],
            'Carbs' => $s['Carbs'],
            'Fat' => $s['Fat'],
            'Doctor_Id' => $nutritionist_id,
            'Created_at' => $now,
            'Updated_at' => $now,
            'is_deleted' => 0,
        ];
        $id = insert('meal', $data);
        if ($id !== false) $created++;
    }
    $found->close();
}

echo json_encode(['inserted'=>$created]);

?>
