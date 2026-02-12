<?php
$host = "localhost";
$user = "root";
$pass = ""; 
$dbname = "power gym"; // your database name

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
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
    $stmt->close();
    return $affected > 0 ? $conn->insert_id : false;
}

// READ: Select records
function select($table, $columns = '*', $where = null, $order = '') {
    global $conn;
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
    // Add soft-delete filter only if column exists in the table
    $res = $conn->query("SHOW COLUMNS FROM `$table` LIKE 'is_deleted'");
    if ($res && $res->num_rows > 0) {
        $conditions[] = "is_deleted = 0";
    }
    if (!empty($conditions)) {
        $sql .= " WHERE " . implode(' AND ', $conditions);
    }
    if ($order) {
        $sql .= " ORDER BY $order";
    }
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
    $conditions = [];
    $params = [];
    foreach ($where as $col => $val) {
        $conditions[] = "$col = ?";
        $params[] = $val;
    }
    $whereStr = implode(' AND ', $conditions);
    // If table has is_deleted column, perform soft delete, otherwise perform hard delete
    $res = $conn->query("SHOW COLUMNS FROM `$table` LIKE 'is_deleted'");
    if ($res && $res->num_rows > 0) {
        $sql = "UPDATE $table SET is_deleted = 1 WHERE $whereStr";
    } else {
        $sql = "DELETE FROM $table WHERE $whereStr";
    }
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