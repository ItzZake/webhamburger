<?php
$host = "localhost";
$user = "root";
$pass = ""; 
$dbname = "power gym"; // your database name

$conn = new mysqli($host, $user, $pass);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$conn->select_db($dbname);

if ($conn->error) {
    die("Database selection failed: " . $conn->error);
}

// Allowed tables - whitelist to prevent SQL injection via table names
function validateTableName($table) {
    $allowed = [
        'address', 'adminactionlog', 'adminprofile', 'appointment', 'cart', 'cartitem', 'coachprofile', 'conversation', 'corder', 'exercise', 'fooditem', 'inventory', 'meal', 'meallog', 'mealplan', 'mealplanitem', 'medicalrecord', 'memberprofile', 'membershipfreeze', 'membershipplan', 'membershipsubscription', 'message', 'nutritionistprofile', 'orderitem', 'payment', 'product', 'productcategory', 'productvariant', 'staffavailability', 'userprofile', 'workoutexercise', 'workoutlog', 'workoutprogram'
    ];
    if (!in_array(strtolower($table), $allowed)) {
        die("Invalid table name: " . htmlspecialchars($table));
    }
}

// Helper function to determine parameter types for prepared statements
function getTypes($values) {
    $types = '';
    foreach ($values as $val) {
        if (is_int($val)) $types .= 'i';
        elseif (is_float($val)) $types .= 'd';
        else $types .= 's';
    }
    return $types;
}

// CREATE: Insert a new record
function insert($table, $data) {
    global $conn;
    validateTableName($table);
    $columns = implode(',', array_keys($data));
    $placeholders = str_repeat('?,', count($data) - 1) . '?';
    $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    $types = getTypes(array_values($data));
    $stmt->bind_param($types, ...array_values($data));
    $stmt->execute();
    if ($stmt->error) {
        die("Execute failed: " . $stmt->error);
    }
    $affected = $stmt->affected_rows;
    $insertId = $conn->insert_id;
    $stmt->close();
    return $affected > 0 ? $insertId : false;
}

// READ: Select records
function select($table, $columns = '*', $where = null) {
    global $conn;
    validateTableName($table);
    if (is_array($columns)) {
        $columns = implode(',', $columns);
    }
    $sql = "SELECT $columns FROM $table";
    $params = [];
    $types = '';
    $conditions = [];
    if ($where) {
        foreach ($where as $col => $val) {
            $conditions[] = "$col = ?";
            $params[] = $val;
        }
    }
    $conditions[] = "is_deleted = 0";
    $sql .= " WHERE " . implode(' AND ', $conditions);
    $types = getTypes($params);
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    if ($stmt->error) {
        die("Execute failed: " . $stmt->error);
    }
    $result = $stmt->get_result();
    $rows = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $rows;
}

// UPDATE: Update records
function update($table, $data, $where) {
    global $conn;
    validateTableName($table);
    $set = [];
    $params = [];
    foreach ($data as $col => $val) {
        $set[] = "$col = ?";
        $params[] = $val;
    }
    $setStr = implode(', ', $set);
    $conditions = [];
    foreach ($where as $col => $val) {
        $conditions[] = "$col = ?";
        $params[] = $val;
    }
    $whereStr = implode(' AND ', $conditions);
    $sql = "UPDATE $table SET $setStr WHERE $whereStr";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    $types = getTypes($params);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    if ($stmt->error) {
        die("Execute failed: " . $stmt->error);
    }
    $affected = $stmt->affected_rows;
    $stmt->close();
    return $affected;
}

// DELETE: Soft delete records
function delete($table, $where) {
    global $conn;
    validateTableName($table);
    $conditions = [];
    $params = [];
    foreach ($where as $col => $val) {
        $conditions[] = "$col = ?";
        $params[] = $val;
    }
    $whereStr = implode(' AND ', $conditions);
    $sql = "UPDATE $table SET is_deleted = 1 WHERE $whereStr";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    $types = getTypes($params);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    if ($stmt->error) {
        die("Execute failed: " . $stmt->error);
    }
    $affected = $stmt->affected_rows;
    $stmt->close();
    return $affected;
}

/* Helper high-level functions used by api.php */

function addUser($data) {
    // Ensure required fields
    if (empty($data['Email'])) return false;
    $data['Created_at'] = date('Y-m-d H:i:s');
    $data['Last_Login'] = date('Y-m-d');
    $data['Is_Active'] = 1;
    $data['Profile_pic_url'] = '';
    $data['Updated_at'] = $data['Created_at'];
    return insert('userprofile', $data);
}

function addMemberProfile($userId, $data) {
    if (!$userId) return false;
    $data['Member_Id'] = $userId;
    $data['Created_at'] = date('Y-m-d H:i:s');
    $data['Em_Contact_Num'] = 0;
    $data['EM_Contact_Name'] = '';
    $data['Injuries'] = '';
    $data['Medical_Condition'] = '';
    $data['BMI'] = 0.0;
    $data['Updated_at'] = $data['Created_at'];
    return insert('memberprofile', $data);
}

function getAllMembers() {
    global $conn;
    $sql = "SELECT u.User_ID, u.First_Name, u.Last_Name, u.Email, u.Phone_Number, u.Is_Active, u.Created_at, m.Experience_Level, m.Height, m.Weight, m.Body_fat, m.Training_Goals FROM userprofile u INNER JOIN memberprofile m ON m.Member_Id = u.User_ID AND m.is_deleted = 0 WHERE u.Role = 'member' AND u.is_deleted = 0 AND u.Is_Active = 1";
    $res = $conn->query($sql);
    return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
}

function getMemberById($id) {
    global $conn;
    $id = (int)$id;
    $sql = "SELECT u.User_ID, u.First_Name, u.Last_Name, u.Email, u.Phone_Number, u.Is_Active, u.Created_at, m.Experience_Level, m.Height, m.Weight, m.Body_fat, m.Training_Goals FROM userprofile u LEFT JOIN memberprofile m ON m.Member_Id = u.User_ID WHERE u.User_ID = ? AND u.is_deleted = 0";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result ? $result->fetch_assoc() : null;
    $stmt->close();
    return $row;
}

function getStaffByRole($role = '') {
    global $conn;
    if ($role) {
        if ($role == 'coach') {
            $sql = "SELECT u.User_ID, u.First_Name, u.Last_Name, u.Email, u.Phone_Number, u.Role, u.Is_Active, u.Created_at FROM userprofile u INNER JOIN coachprofile c ON c.Coach_Id = u.User_ID AND c.is_deleted = 0 WHERE u.Role = ? AND u.is_deleted = 0 AND u.Is_Active = 1";
        } elseif ($role == 'nutritionist') {
            $sql = "SELECT u.User_ID, u.First_Name, u.Last_Name, u.Email, u.Phone_Number, u.Role, u.Is_Active, u.Created_at FROM userprofile u INNER JOIN nutritionistprofile n ON n.Nutritionist_Id = u.User_ID AND n.is_deleted = 0 WHERE u.Role = ? AND u.is_deleted = 0 AND u.Is_Active = 1";
        } else {
            $sql = "SELECT u.User_ID, u.First_Name, u.Last_Name, u.Email, u.Phone_Number, u.Role, u.Is_Active, u.Created_at FROM userprofile u WHERE u.Role = ? AND u.is_deleted = 0 AND u.Is_Active = 1";
        }
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $role);
        $stmt->execute();
        $res = $stmt->get_result();
        $rows = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
        $stmt->close();
        return $rows;
    } else {
        $sql = "SELECT User_ID, First_Name, Last_Name, Email, Phone_Number, Role, Is_Active, Created_at FROM userprofile WHERE Role <> 'member' AND is_deleted = 0 AND Is_Active = 1";
        $res = $conn->query($sql);
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }
}

function addCoachProfile($userId, $data) {
    if (!$userId) return false;
    $data['Coach_Id'] = $userId;
    $data['Created_at'] = date('Y-m-d H:i:s');
    return insert('coachprofile', $data);
}

function addNutritionistProfile($userId, $data) {
    if (!$userId) return false;
    $data['Nutritionist_Id'] = $userId;
    $data['Created_at'] = date('Y-m-d H:i:s');
    return insert('nutritionistprofile', $data);
}

function getAllWorkouts() {
    global $conn;
    $sql = "SELECT * FROM exercise WHERE is_deleted = 0";
    $res = $conn->query($sql);
    return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
}

function getAllWorkoutPrograms() {
    global $conn;
    $sql = "SELECT * FROM workoutprogram WHERE is_deleted = 0";
    $res = $conn->query($sql);
    return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
}

function getWorkoutProgramDetails($id) {
    global $conn;
    $id = (int)$id;
    $sql = "SELECT * FROM workoutprogram WHERE Program_ID = ? AND is_deleted = 0";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res ? $res->fetch_assoc() : null;
    $stmt->close();
    return $row;
}

function addExercise($data) {
    $data['Created_at'] = date('Y-m-d H:i:s');
    return insert('exercise', $data);
}

function getDashboardStats() {
    global $conn;
    $stats = [
        'activeMembers' => 0,
        'totalRevenue' => 0,
        'totalCoaches' => 0,
        'activeWorkouts' => 0
    ];

    $res = $conn->query("SELECT COUNT(*) as c FROM userprofile WHERE Role = 'member' AND Is_Active = 1 AND is_deleted = 0");
    if ($res) {
        $row = $res->fetch_assoc();
        $stats['activeMembers'] = (int)$row['c'];
    }

    $res = $conn->query("SELECT IFNULL(SUM(Amount),0) as total FROM payment");
    if ($res) {
        $row = $res->fetch_assoc();
        $stats['totalRevenue'] = (float)$row['total'];
    }

    $res = $conn->query("SELECT COUNT(*) as c FROM userprofile WHERE Role = 'coach' AND is_deleted = 0 AND Is_Active = 1");
    if ($res) {
        $row = $res->fetch_assoc();
        $stats['totalCoaches'] = (int)$row['c'];
    }

    $res = $conn->query("SELECT COUNT(*) as c FROM workoutprogram WHERE is_deleted = 0");
    if ($res) {
        $row = $res->fetch_assoc();
        $stats['activeWorkouts'] = (int)$row['c'];
    }

    return $stats;
}

function getRecentActivity() {
    global $conn;
    $res = $conn->query("SELECT Action_Type as type, Target_Entity_Type as name, Description as details, Created_at as time, Admin_ID as adminId FROM adminactionlog WHERE is_deleted = 0 ORDER BY Created_at DESC LIMIT 10");
    return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
}

function searchMembers($term) {
    global $conn;
    $like = '%' . $conn->real_escape_string($term) . '%';
    $sql = "SELECT u.User_ID, u.First_Name, u.Last_Name, u.Email, u.Phone_Number, u.Is_Active, u.Created_at, m.Experience_Level FROM userprofile u INNER JOIN memberprofile m ON m.Member_Id = u.User_ID AND m.is_deleted = 0 WHERE (u.First_Name LIKE ? OR u.Last_Name LIKE ? OR u.Email LIKE ?) AND u.Role = 'member' AND u.is_deleted = 0 AND u.Is_Active = 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sss', $like, $like, $like);
    $stmt->execute();
    $res = $stmt->get_result();
    $rows = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    $stmt->close();
    return $rows;
}

function logAction($actionType, $targetType, $description, $adminId = 1) {
    $data = [
        'Action_Type' => $actionType,
        'Target_Entity_Type' => $targetType,
        'Description' => $description,
        'Created_at' => date('Y-m-d H:i:s'),
        'Admin_ID' => $adminId
    ];
    insert('adminactionlog', $data);
}

?>


