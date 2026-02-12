<?php
// api.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require 'DB.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = trim($_GET['action'] ?? '');
$input = json_decode(file_get_contents('php://input'), true);
$adminId = $input['adminId'] ?? $_GET['adminId'] ?? 1;
$response = ['success' => false, 'message' => 'Invalid request'];

try {
    switch ($action) {
        case 'getMembers':
            if ($method === 'GET') {
                $members = getAllMembers();
                $response = ['success' => true, 'data' => $members];
            }
            break;

        case 'getMember':
            if ($method === 'GET') {
                $id = (int)($_GET['id'] ?? 0);
                if ($id > 0) {
                    $member = getMemberById($id);
                    if ($member) {
                        $response = ['success' => true, 'data' => $member];
                    } else {
                        $response = ['success' => false, 'message' => 'Member not found'];
                    }
                }
            }
            break;

        case 'addMember':
            if ($method === 'POST') {
                if (empty($input['Email']) || empty($input['First_Name']) || empty($input['Last_Name'])) {
                    $response = ['success' => false, 'message' => 'Required fields are missing'];
                    break;
                }
                
                // Add user
                $userData = [
                    'Email' => trim($input['Email']),
                    'Password' => password_hash('password123', PASSWORD_DEFAULT), // Default password
                    'First_Name' => trim($input['First_Name']),
                    'Last_Name' => trim($input['Last_Name']),
                    'Phone_Number' => (int)($input['Phone'] ?? 0),
                    'DOB' => $input['DOB'] ?? '2000-01-01',
                    'Role' => 'member',
                    'Gender' => $input['Gender'] ?? 'Male'
                ];
                
                $userId = addUser($userData);
                
                if ($userId) {
                    // Add member profile
                    $memberData = [
                        'Body_fat' => $input['Body_fat'] ?? 0,
                        'Height' => $input['Height'] ?? 0,
                        'Weight' => $input['Weight'] ?? 0,
                        'Training_Goals' => $input['Training_Goals'] ?? '',
                        'Experience_Level' => $input['Experience_Level'] ?? 'Beginner'
                    ];
                    
                    addMemberProfile($userId, $memberData);
                    
                    $response = [
                        'success' => true, 
                        'message' => 'Member added successfully',
                        'id' => $userId
                    ];
                    
                    logAction('add', 'member', 'added member ' . trim($input['First_Name']) . ' ' . trim($input['Last_Name']), $adminId);
                } else {
                    $response = ['success' => false, 'message' => 'Failed to add member'];
                }
            }
            break;

        case 'updateMember':
            if ($method === 'PUT') {
                $id = (int)($input['User_ID'] ?? 0);
                
                if ($id > 0) {
                    // Update user data
                    $userData = [];
                    if (isset($input['First_Name'])) $userData['First_Name'] = $input['First_Name'];
                    if (isset($input['Last_Name'])) $userData['Last_Name'] = $input['Last_Name'];
                    if (isset($input['Email'])) $userData['Email'] = $input['Email'];
                    if (isset($input['Phone_Number'])) $userData['Phone_Number'] = $input['Phone_Number'];
                    if (isset($input['Is_Active'])) $userData['Is_Active'] = $input['Is_Active'];
                    $userData['Updated_at'] = date('Y-m-d H:i:s');
                    
                    if (!empty($userData)) {
                        update('userprofile', $userData, ['User_ID' => $id]);
                    }
                    
                    // Update member profile if data exists
                    if (isset($input['Height']) || isset($input['Weight']) || isset($input['Training_Goals'])) {
                        $memberData = [];
                        if (isset($input['Height'])) $memberData['Height'] = $input['Height'];
                        if (isset($input['Weight'])) $memberData['Weight'] = $input['Weight'];
                        if (isset($input['Body_fat'])) $memberData['Body_fat'] = $input['Body_fat'];
                        if (isset($input['Training_Goals'])) $memberData['Training_Goals'] = $input['Training_Goals'];
                        if (isset($input['Experience_Level'])) $memberData['Experience_Level'] = $input['Experience_Level'];
                        $memberData['Updated_at'] = date('Y-m-d H:i:s');
                        
                        if (!empty($memberData)) {
                            update('memberprofile', $memberData, ['Member_Id' => $id]);
                        }
                    }
                    
                    $response = ['success' => true, 'message' => 'Member updated successfully'];
                    
                    logAction('update', 'member', 'updated member with ID ' . $id, $adminId);
                }
            }
            break;

        case 'deleteMember':
            if ($method === 'DELETE') {
                $id = (int)($_GET['id'] ?? 0);
                
                if ($id > 0) {
                    $user = select('userprofile', ['Role'], ['User_ID' => $id]);
                    if ($user) {
                        $role = $user[0]['Role'];
                        $result = delete('userprofile', ['User_ID' => $id]);
                        if ($result > 0) {
                            if ($role == 'member') {
                                update('memberprofile', ['is_deleted' => 1], ['Member_Id' => $id]);
                            } elseif ($role == 'coach') {
                                update('coachprofile', ['is_deleted' => 1], ['Coach_Id' => $id]);
                            } elseif ($role == 'nutritionist') {
                                update('nutritionistprofile', ['is_deleted' => 1], ['Nutritionist_Id' => $id]);
                            }
                            logAction('delete', $role, 'deleted ' . $role . ' with ID ' . $id, $adminId);
                        }
                        $response = [
                            'success' => $result > 0,
                            'message' => $result > 0 ? ucfirst($role) . ' deleted successfully' : ucfirst($role) . ' not found'
                        ];
                    } else {
                        $response = ['success' => false, 'message' => 'User not found'];
                    }
                }
            }
            break;

        case 'getStaff':
            if ($method === 'GET') {
                $role = $_GET['role'] ?? '';
                $staff = getStaffByRole($role);
                $response = ['success' => true, 'data' => $staff];
            }
            break;

        case 'addStaff':
            if ($method === 'POST') {
                if (empty($input['Email']) || empty($input['First_Name']) || empty($input['Last_Name']) || empty($input['Role'])) {
                    $response = ['success' => false, 'message' => 'Required fields are missing'];
                    break;
                }
                
                // Add user
                $userData = [
                    'Email' => trim($input['Email']),
                    'Password' => password_hash('password123', PASSWORD_DEFAULT),
                    'First_Name' => trim($input['First_Name']),
                    'Last_Name' => trim($input['Last_Name']),
                    'Phone_Number' => $input['Phone'] ?? '',
                    'DOB' => $input['DOB'] ?? '2000-01-01',
                    'Role' => $input['Role'],
                    'Gender' => $input['Gender'] ?? 'Male'
                ];
                
                $userId = addUser($userData);
                
                if ($userId) {
                    // Add specific profile based on role
                    if ($input['Role'] === 'coach') {
                        $profileData = [
                            'Bio' => $input['Bio'] ?? '',
                            'Certifications' => $input['Certifications'] ?? '',
                            'Specialization_Main' => $input['Specialization'] ?? ''
                        ];
                        addCoachProfile($userId, $profileData);
                    } elseif ($input['Role'] === 'nutritionist') {
                        $profileData = [
                            'Bio' => $input['Bio'] ?? '',
                            'Certifications' => $input['Certifications'] ?? '',
                            'Specialization_Main' => $input['Specialization'] ?? ''
                        ];
                        addNutritionistProfile($userId, $profileData);
                    } elseif ($input['Role'] === 'admin') {
                        $profileData = [
                            'Job_Title' => $input['Job_Title'] ?? 'Administrator',
                            'Can_Manage_Users' => 1,
                            'Can_Manage_Memberships' => 1,
                            'Can_Manage_Store' => 1,
                            'Can_Manage_Nutritionists' => 1,
                            'Can_Manage_Coaches' => 1,
                            'Can_Manage_Appointments' => 1,
                            'Can_View_Reports' => 1,
                            'Created_at' => date('Y-m-d H:i:s')
                        ];
                        insert('adminprofile', $profileData);
                    }
                    
                    $response = [
                        'success' => true, 
                        'message' => ucfirst($input['Role']) . ' added successfully',
                        'id' => $userId
                    ];
                    
                    logAction('add', $input['Role'], 'added ' . $input['Role'] . ' ' . trim($input['First_Name']) . ' ' . trim($input['Last_Name']), $adminId);
                } else {
                    $response = ['success' => false, 'message' => 'Failed to add staff member'];
                }
            }
            break;

        case 'getWorkouts':
            if ($method === 'GET') {
                $workouts = getAllWorkouts();
                $response = ['success' => true, 'data' => $workouts];
            }
            break;

        case 'getWorkoutPrograms':
            if ($method === 'GET') {
                $programs = getAllWorkoutPrograms();
                $response = ['success' => true, 'data' => $programs];
            }
            break;

        case 'getWorkoutDetails':
            if ($method === 'GET') {
                $id = (int)($_GET['id'] ?? 0);
                if ($id > 0) {
                    $workout = getWorkoutProgramDetails($id);
                    if ($workout) {
                        $response = ['success' => true, 'data' => $workout];
                    } else {
                        $response = ['success' => false, 'message' => 'Workout program not found'];
                    }
                }
            }
            break;

        case 'addExercise':
            if ($method === 'POST') {
                if (empty($input['Name']) || empty($input['Description']) || empty($input['Target_Muscle_Group'])) {
                    $response = ['success' => false, 'message' => 'Required fields are missing'];
                    break;
                }
                
                $result = addExercise($input);
                if ($result) {
                    $response = ['success' => true, 'message' => 'Exercise added successfully', 'id' => $result];
                    
                    logAction('add', 'exercise', 'added exercise ' . $input['Name'], $adminId);
                } else {
                    $response = ['success' => false, 'message' => 'Failed to add exercise'];
                }
            }
            break;

        case 'getDashboardStats':
            if ($method === 'GET') {
                $stats = getDashboardStats();
                $response = ['success' => true, 'data' => $stats];
            }
            break;

        case 'getRecentActivity':
            if ($method === 'GET') {
                $activity = getRecentActivity();
                $response = ['success' => true, 'data' => $activity];
            }
            break;

        case 'searchMembers':
            if ($method === 'GET') {
                $searchTerm = $_GET['q'] ?? '';
                if (!empty($searchTerm)) {
                    $members = searchMembers($searchTerm);
                    $response = ['success' => true, 'data' => $members];
                } else {
                    $response = ['success' => false, 'message' => 'Search term is required'];
                }
            }
            break;

        default:
            $response = ['success' => false, 'message' => 'Unknown action: ' . $action];
    }
} catch (Exception $e) {
    $response = ['success' => false, 'message' => 'Server error: ' . $e->getMessage()];
}

echo json_encode($response);
?>