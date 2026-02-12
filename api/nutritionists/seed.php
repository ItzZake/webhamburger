<?php
require_once __DIR__ . '/../../DB.php';
header('Content-Type: application/json');

$now = date('Y-m-d H:i:s');
$created = 0;

$nutritionists = [
    ['email' => 'nutri_sarah@example.local', 'first' => 'Sarah', 'last' => 'Johnson', 'bio' => 'Certified nutritionist specializing in diabetes management and metabolic health. Expert in creating personalized meal plans for optimal blood sugar control.', 'special' => 'Diabetes & Metabolic Health'],
    ['email' => 'nutri_david@example.local', 'first' => 'David', 'last' => 'Martinez', 'bio' => 'Registered dietitian with expertise in gut health and digestive wellness. Helps clients optimize their nutrition for better digestion and overall wellness.', 'special' => 'Gut Health & Digestive Wellness'],
    ['email' => 'nutri_emily@example.local', 'first' => 'Emily', 'last' => 'Chen', 'bio' => 'Holistic nutritionist focusing on anti-inflammatory diets and immune system support. Creates comprehensive meal plans for long-term health and vitality.', 'special' => 'Anti-Inflammatory & Immune Support'],
];

foreach ($nutritionists as $n) {
    // find existing user
    $found = select('userprofile', ['User_ID'], ['Email' => $n['email']]);
    if (!empty($found)) {
        $userId = (int)$found[0]['User_ID'];
    } else {
        $userData = [
            'Email' => $n['email'],
            'Password' => '',
            'Last_Login' => '0000-00-00',
            'First_Name' => $n['first'],
            'Last_Name' => $n['last'],
            'Phone_Number' => 0,
            'DOB' => '0000-00-00',
            'Role' => 'nutritionist',
            'Gender' => '',
            'Is_Active' => 1,
            'Profile_pic_url' => '',
            'Created_at' => $now,
            'Updated_at' => $now,
            'is_deleted' => 0,
        ];
        $userId = insert('userprofile', $userData);
    }

    // insert nutritionist profile if missing
    $foundNut = select('nutritionistprofile', ['Nutritionist_ID'], ['Nutritionist_ID' => $userId]);
    if (empty($foundNut)) {
        $nutData = [
            'Nutritionist_ID' => $userId,
            'Licence_Number' => '',
            'Bio' => $n['bio'],
            'Certifications' => '',
            'rating_count' => 0,
            'Avg_rating' => 0,
            'Is_accepting_new' => 1,
            'Years_Experience' => 0,
            'Specialization_Main' => $n['special'],
            'Clinic_Location' => '',
            'Created_at' => $now,
            'Updated_at' => $now,
            'is_deleted' => 0,
        ];
        $res = insert('nutritionistprofile', $nutData);
        if ($res !== false) $created++;
    }
}

echo json_encode(['inserted' => $created]);
