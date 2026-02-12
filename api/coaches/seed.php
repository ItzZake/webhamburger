<?php
require_once __DIR__ . '/../../DB.php';
header('Content-Type: application/json');

$now = date('Y-m-d H:i:s');
$created = 0;

// Sample coaches to insert (expanded)
$coaches = [
    ['email' => 'coach_jane@example.local', 'first' => 'Jane', 'last' => 'Doe', 'bio' => 'Strength coach and nutrition advisor', 'special' => 'Strength & Conditioning'],
    ['email' => 'coach_ali@example.local', 'first' => 'Ali', 'last' => 'Khan', 'bio' => 'Expert in hypertrophy and bodybuilding', 'special' => 'Bodybuilding & Hypertrophy'],
    ['email' => 'coach_olga@example.local', 'first' => 'Olga', 'last' => 'Ivanova', 'bio' => 'Functional fitness and mobility specialist', 'special' => 'Functional Fitness'],
    // new coaches
    ['email' => 'coach_marcus@example.local', 'first' => 'Marcus', 'last' => 'Reed', 'bio' => 'Endurance and marathon coach', 'special' => 'Endurance'],
    ['email' => 'coach_lena@example.local', 'first' => 'Lena', 'last' => 'Martinez', 'bio' => 'HIIT and metabolic conditioning expert', 'special' => 'HIIT & Conditioning'],
    ['email' => 'coach_diego@example.local', 'first' => 'Diego', 'last' => 'Ramos', 'bio' => 'CrossFit and Olympic lifting coach', 'special' => 'CrossFit & Olympic Lifting'],
    ['email' => 'coach_priya@example.local', 'first' => 'Priya', 'last' => 'Shah', 'bio' => 'Sports performance and recovery specialist', 'special' => 'Sports Performance'],
    ['email' => 'coach_tomas@example.local', 'first' => 'Tomas', 'last' => 'Nguyen', 'bio' => 'Youth development and skill coach', 'special' => 'Youth Development'],
];

foreach ($coaches as $c) {
    // find existing user by email using helper
    $found = select('userprofile', ['User_ID'], ['Email' => $c['email']]);
    if (!empty($found)) {
        $userId = (int)$found[0]['User_ID'];
    } else {
        $userData = [
            'Email' => $c['email'],
            'Password' => '',
            'Last_Login' => '0000-00-00',
            'First_Name' => $c['first'],
            'Last_Name' => $c['last'],
            'Phone_Number' => 0,
            'DOB' => '0000-00-00',
            'Role' => 'coach',
            'Gender' => '',
            'Is_Active' => 1,
            'Profile_pic_url' => '',
            'Created_at' => $now,
            'Updated_at' => $now,
            'is_deleted' => 0,
        ];
        $userId = insert('userprofile', $userData);
    }

    // insert coachprofile if not exists
    $foundCoach = select('coachprofile', ['Coach_ID'], ['Coach_ID' => $userId]);
    if (empty($foundCoach)) {
        $coachData = [
            'Coach_ID' => $userId,
            'Bio' => $c['bio'],
            'Certifications' => '',
            'rating_count' => 0,
            'Avg_rating' => 0,
            'Is_Accepting_new' => 1,
            'Max_Clients' => 20,
            'Specialization_Main' => $c['special'],
            'Specialization_Other' => '',
            'Youtube_Url' => '',
            'Instagram_Url' => '',
            'Created_At' => $now,
            'Updated_At' => $now,
            'is_deleted' => 0,
        ];
        $res = insert('coachprofile', $coachData);
        if ($res !== false) $created++;
    }
}

echo json_encode(['inserted' => $created]);
