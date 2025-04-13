<?php
require_once '../auth/db_connect.php';
require_once '../auth/auth_functions.php';

// Database operation handler
function handle_database_operation($operation, $table, $data = [], $id = null) {
    global $conn;
    
    try {
        if (!$conn) {
            throw new Exception("Database connection failed");
        }

        $conn->begin_transaction();
        
        switch ($operation) {
            case 'add':
                $result = add_record($table, $data);
                break;
            case 'edit':
                $result = edit_record($table, $id, $data);
                break;
            case 'delete':
                $result = delete_record($table, $id);
                break;
            default:
                throw new Exception("Invalid operation");
        }
        
        $conn->commit();
        
        return [
            'success' => true,
            'message' => ucfirst($operation) . ' operation completed successfully',
            'data' => $result
        ];
    } catch (Exception $e) {
        if ($conn) {
            $conn->rollback();
        }
        return [
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ];
    }
}

// Get all records from a table
function get_all_records($table) {
    global $conn;
    
    if (!$conn) {
        throw new Exception("Database connection failed");
    }
    
    $query = "SELECT * FROM $table";
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception("Failed to prepare statement: " . $conn->error);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Get a single record by ID
function get_record($table, $id) {
    global $conn;
    
    if (!$conn) {
        throw new Exception("Database connection failed");
    }
    
    $query = "SELECT * FROM $table WHERE id = ?";
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception("Failed to prepare statement: " . $conn->error);
    }
    
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

// Add a new record
function add_record($table, $data) {
    global $conn;
    
    if (!$conn) {
        throw new Exception("Database connection failed");
    }
    
    $columns = implode(", ", array_keys($data));
    $values = implode(", ", array_fill(0, count($data), "?"));
    $types = str_repeat("s", count($data));
    
    $query = "INSERT INTO $table ($columns) VALUES ($values)";
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception("Failed to prepare statement: " . $conn->error);
    }
    
    $stmt->bind_param($types, ...array_values($data));
    $stmt->execute();
    
    if ($stmt->affected_rows === 0) {
        throw new Exception("Failed to add record");
    }
    
    return $conn->insert_id;
}

// Edit an existing record
function edit_record($table, $id, $data) {
    global $conn;
    
    if (!$conn) {
        throw new Exception("Database connection failed");
    }
    
    $set_clause = implode(" = ?, ", array_keys($data)) . " = ?";
    $types = str_repeat("s", count($data)) . "i";
    $values = array_merge(array_values($data), [$id]);
    
    $query = "UPDATE $table SET $set_clause WHERE id = ?";
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception("Failed to prepare statement: " . $conn->error);
    }
    
    $stmt->bind_param($types, ...$values);
    $stmt->execute();
    
    if ($stmt->affected_rows === 0) {
        throw new Exception("No records were updated");
    }
    
    return $stmt->affected_rows;
}

// Delete a record
function delete_record($table, $id) {
    global $conn;
    
    if (!$conn) {
        throw new Exception("Database connection failed");
    }
    
    $query = "DELETE FROM $table WHERE id = ?";
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception("Failed to prepare statement: " . $conn->error);
    }
    
    $stmt->bind_param("i", $id);
    $stmt->execute();
    
    if ($stmt->affected_rows === 0) {
        throw new Exception("No records were deleted");
    }
    
    return $stmt->affected_rows;
}

// Validate user data
function validate_user_data($data) {
    $errors = [];
    
    if (empty($data['username'])) {
        $errors[] = "Username is required";
    }
    if (empty($data['email'])) {
        $errors[] = "Email is required";
    } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    if (empty($data['user_type'])) {
        $errors[] = "User type is required";
    }
    
    return $errors;
}

// Validate resource data
function validate_resource_data($data) {
    $errors = [];
    
    if (empty($data['title'])) {
        $errors[] = "Title is required";
    }
    if (empty($data['description'])) {
        $errors[] = "Description is required";
    }
    if (empty($data['category'])) {
        $errors[] = "Category is required";
    }
    
    return $errors;
}

// Sanitize input data
function sanitize_input($data) {
    if (is_array($data)) {
        return array_map('sanitize_input', $data);
    }
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
} 