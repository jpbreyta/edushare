<?php
require_once '../auth/db_connect.php';
require_once '../auth/config.php';
require_once '../auth/auth_functions.php';

// User-related functions
function registerUser($name, $username, $email, $password, $user_type, $organization = null) {
    global $conn;
    
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (name, username, email, password, user_type, organization) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $name, $username, $email, $hashed_password, $user_type, $organization);
    
    return $stmt->execute();
}

function loginUser($email, $password) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            return $user;
        }
    }
    return false;
}

function getAllUsers() {
    global $conn;
    $result = $conn->query("CALL sp_get_all_users()");
    
    $users = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
    }
    $result->free();
    $conn->next_result();
    return $users;
}

function addUser($name, $username, $email, $password, $user_type, $organization = null) {
    global $conn;
    
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("CALL sp_add_user(?, ?, ?, ?, ?, ?)");
    if (!$stmt) {
        error_log("Prepare failed: (" . $conn->errno . ") " . $conn->error);
        return false;
    }

    $bind_result = $stmt->bind_param("ssssss", $name, $username, $email, $hashed_password, $user_type, $organization);
    if (!$bind_result) {
        error_log("Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error);
        $stmt->close();
        return false;
    }
    
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $newId = $row['id'];
            $stmt->close();
            $conn->next_result();
            return $newId;
        } else {
            $newId = $conn->insert_id;
            $stmt->close();
            while ($conn->more_results() && $conn->next_result()) {}
            return $newId > 0 ? $newId : false;
        }
    } else {
        error_log("Execute failed: (" . $stmt->errno . ") " . $stmt->error);
        $stmt->close();
        while ($conn->more_results() && $conn->next_result()) {}
        return false;
    }
}

function updateUser($id, $name, $email, $user_type, $organization = null, $phone = null, $address = null) {
    global $conn;
    
    $stmt = $conn->prepare("CALL sp_update_user(?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssss", $id, $name, $email, $user_type, $organization, $phone, $address);
    
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        $conn->next_result();
        return $row['affected_rows'] > 0;
    }
    return false;
}

function updateUserPassword($id, $password) {
    global $conn;
    
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("CALL sp_update_user_password(?, ?)");
    $stmt->bind_param("is", $id, $hashed_password);
    
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        $conn->next_result();
        return $row['affected_rows'] > 0;
    }
    return false;
}

function deleteUser($id) {
    global $conn;
    
    $stmt = $conn->prepare("CALL sp_delete_user(?)");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        $conn->next_result();
        return $row['affected_rows'] > 0;
    }
    return false;
}

function getUserById($id) {
    global $conn;
    
    $stmt = $conn->prepare("CALL sp_get_user_by_id(?)");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $stmt->close();
        $conn->next_result();
        return $user;
    }
    $stmt->close();
    $conn->next_result();
    return null;
}

function getUsersByType($user_type) {
    global $conn;
    $stmt = $conn->prepare("SELECT id, name, email FROM users WHERE user_type = ? ORDER BY name");
    $stmt->bind_param("s", $user_type);
    $stmt->execute();
    $result = $stmt->get_result();
    $users = [];
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
    $stmt->close();
    $conn->next_result();
    return $users;
}

// School-related functions
function getAllSchools() {
    global $conn;
    $result = $conn->query("CALL sp_get_all_schools()");
    
    $schools = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $schools[] = $row;
        }
    }
    $result->free();
    $conn->next_result();
    return $schools;
}

function getAllSchoolLevels() {
    return [
        ['id' => 'primary', 'name' => 'Primary'],
        ['id' => 'secondary', 'name' => 'Secondary'],
        ['id' => 'tertiary', 'name' => 'Tertiary']
    ];
}

function addSchool($name, $level, $region, $address, $contact_person, $email, $phone) {
    global $conn;
    
    // Ensure level is one of the valid ENUM values
    $valid_levels = ['primary', 'secondary', 'tertiary'];
    if (!in_array($level, $valid_levels)) {
        error_log("Invalid school level value: " . $level);
        return false;
    }
    
    try {
        // Check if email already exists
        $checkStmt = $conn->prepare("SELECT 1 FROM schools WHERE email = ?");
        $checkStmt->bind_param("s", $email);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        
        if ($checkResult->num_rows > 0) {
            // Email already exists
            $checkStmt->close();
            throw new mysqli_sql_exception("Email already exists", 1062); // 1062 is MySQL duplicate entry error code
        }
        $checkStmt->close();
        
        // Insert directly instead of using stored procedure
        $insertStmt = $conn->prepare("INSERT INTO schools (name, level, region, address, contact_person, email, phone) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $insertStmt->bind_param("sssssss", $name, $level, $region, $address, $contact_person, $email, $phone);
        
        if ($insertStmt->execute()) {
            $newId = $conn->insert_id;
            $insertStmt->close();
            return $newId;
        } else {
            $insertStmt->close();
            return false;
        }
    } catch (mysqli_sql_exception $e) {
        error_log("Error adding school: " . $e->getMessage());
        throw $e; // Re-throw to be caught in the controller
    }
}

function updateSchool($id, $name, $level, $region, $address, $contact_person, $email, $phone) {
    global $conn;

    // Ensure level is one of the valid ENUM values
    $valid_levels = ['primary', 'secondary', 'tertiary'];
    if (!in_array($level, $valid_levels)) {
        error_log("Invalid school level value for update: " . $level);
        return false; // Or throw an exception
    }

    try {
        // Check if email already exists for another school
        $checkStmt = $conn->prepare("SELECT id FROM schools WHERE email = ? AND id != ?");
        $checkStmt->bind_param("si", $email, $id);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();

        if ($checkResult->num_rows > 0) {
            // Email already exists for another school
            $checkStmt->close();
            throw new mysqli_sql_exception("Email already exists for another school.", 1062);
        }
        $checkStmt->close();

        // Update school record
        $updateStmt = $conn->prepare("UPDATE schools SET name = ?, level = ?, region = ?, address = ?, contact_person = ?, email = ?, phone = ? WHERE id = ?");
        $updateStmt->bind_param("sssssssi", $name, $level, $region, $address, $contact_person, $email, $phone, $id);

        if ($updateStmt->execute()) {
            $affected_rows = $updateStmt->affected_rows;
            $updateStmt->close();
            return $affected_rows > 0; // Return true if any row was actually updated
        } else {
            $updateStmt->close();
            error_log("Failed to execute updateSchool statement: " . $conn->error);
            return false;
        }
    } catch (mysqli_sql_exception $e) {
        error_log("Error updating school (ID: $id): " . $e->getMessage() . " (Code: " . $e->getCode() . ")");
        throw $e; // Re-throw to be caught in the controller
    }
}

function deleteSchool($id) {
    global $conn;

    // Validate id is not empty
    if (empty($id)) {
        error_log("Empty school ID provided for deletion");
        return false;
    }

    try {
        // Check if the school exists (optional, but good for confirming before delete)
        $checkStmt = $conn->prepare("SELECT 1 FROM schools WHERE id = ?");
        $checkStmt->bind_param("i", $id);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();

        if ($checkResult->num_rows === 0) {
            $checkStmt->close();
            error_log("School with ID $id not found for deletion.");
            return false; // School not found
        }
        $checkStmt->close();

        // Delete the school
        $deleteStmt = $conn->prepare("DELETE FROM schools WHERE id = ?");
        $deleteStmt->bind_param("i", $id);

        if ($deleteStmt->execute()) {
            $affected_rows = $deleteStmt->affected_rows;
            $deleteStmt->close();
            // Optionally, log deletion activity here
            // $admin_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0; // Get admin ID
            // $logStmt = $conn->prepare("INSERT INTO activity_logs (user_id, action, description) VALUES (?, 'delete_school', ?)");
            // $description = "Deleted school with ID: $id";
            // $logStmt->bind_param("is", $admin_id, $description);
            // $logStmt->execute();
            // $logStmt->close();
            return $affected_rows > 0;
        } else {
            $deleteStmt->close();
            error_log("Failed to execute deleteSchool statement: " . $conn->error);
            return false;
        }
    } catch (mysqli_sql_exception $e) {
        // Check for foreign key constraint violation (e.g., if school is referenced in other tables)
        if ($e->getCode() == 1451) { // Error code for foreign key constraint
             error_log("Cannot delete school (ID: $id) due to existing references: " . $e->getMessage());
             // It might be better to throw a custom exception or return a specific error code/message
             // that the calling code can interpret to inform the user.
             // For now, we'll re-throw the original exception.
        } else {
            error_log("Error deleting school (ID: $id): " . $e->getMessage() . " (Code: " . $e->getCode() . ")");
        }
        throw $e; // Re-throw to be caught in the controller
    }
}

function getSchoolById($id) {
    global $conn;
    
    $stmt = $conn->prepare("CALL sp_get_school_by_id(?)");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $school = $result->fetch_assoc();
        $stmt->close();
        $conn->next_result();
        return $school;
    }
    $stmt->close();
    $conn->next_result();
    return null;
}

// Resource-related functions
function getAllResources() {
    global $conn;
    $result = $conn->query("CALL sp_get_all_resources()");
    
    $resources = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $resources[] = $row;
        }
    }
    $result->free();
    $conn->next_result();
    return $resources;
}

function addResource($title, $description, $category, $resource_type, $file_path, $external_link, $target_audience, $uploaded_by, $school_id = null, $is_visible = true) {
    global $conn;
    
    // Ensure category is one of the valid ENUM values
    $valid_categories = ['textbook', 'ebook', 'presentation', 'worksheet'];
    if (!in_array($category, $valid_categories)) {
        error_log("Invalid category value: " . $category);
        return false;
    }
    
    $stmt = $conn->prepare("CALL sp_add_resource(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssssiii", $title, $description, $category, $resource_type, $file_path, $external_link, $target_audience, $uploaded_by, $school_id, $is_visible);
    
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        $conn->next_result();
        return $row['id'];
    }
    return false;
}

function updateResource($id, $title, $description, $category, $resource_type, $file_path, $external_link, $target_audience, $school_id = null, $is_visible = true) {
    global $conn;
    
    // Ensure category is one of the valid ENUM values
    $valid_categories = ['textbook', 'ebook', 'presentation', 'worksheet'];
    if (!in_array($category, $valid_categories)) {
        error_log("Invalid category value: " . $category);
        return false;
    }
    
    $stmt = $conn->prepare("CALL sp_update_resource(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssssssii", $id, $title, $description, $category, $resource_type, $file_path, $external_link, $target_audience, $school_id, $is_visible);
    
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        $conn->next_result();
        return $row['affected_rows'] > 0;
    }
    return false;
}

function deleteResource($id) {
    global $conn;
    
    // Get admin ID from session - avoid session_start() as it's already started
    $admin_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 1; // Default to 1 if no session
    
    // Validate id is not empty
    if (empty($id)) {
        error_log("Empty resource ID provided for deletion");
        return false;
    }
    
    try {
        // First check if the resource exists and get its title
        $checkStmt = $conn->prepare("SELECT title FROM resources WHERE id = ?");
        $checkStmt->bind_param("i", $id);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        
        if ($checkResult->num_rows === 0) {
            // Resource doesn't exist
            $checkStmt->close();
            error_log("Resource with ID $id not found");
            return false;
        }
        
        $resourceInfo = $checkResult->fetch_assoc();
        $resourceTitle = $resourceInfo['title'];
        $checkStmt->close();
        
        // Start transaction
        $conn->begin_transaction();
        
        // Delete the resource directly
        $deleteStmt = $conn->prepare("DELETE FROM resources WHERE id = ?");
        $deleteStmt->bind_param("i", $id);
        
        if ($deleteStmt->execute()) {
            $affected = $deleteStmt->affected_rows;
            $deleteStmt->close();
            
            if ($affected > 0) {
                // Log the deletion
                $logStmt = $conn->prepare("INSERT INTO activity_logs (user_id, action, description) VALUES (?, 'delete_resource', ?)");
                $description = "Deleted resource: $resourceTitle (ID: $id)";
                $logStmt->bind_param("is", $admin_id, $description);
                $logStmt->execute();
                $logStmt->close();
                
                $conn->commit();
                return true;
            } else {
                // No rows affected
                $conn->rollback();
                error_log("No rows affected when deleting resource $id");
                return false;
            }
        }
        
        // Execution failed
        $conn->rollback();
        return false;
    } catch (mysqli_sql_exception $e) {
        // Roll back on error
        if ($conn->inTransaction()) {
            $conn->rollback();
        }
        error_log("Error deleting resource: " . $e->getMessage());
        throw $e; // Re-throw to be caught in the controller
    }
}

function getResourceById($id) {
    global $conn;
    
    $stmt = $conn->prepare("CALL sp_get_resource_by_id(?)");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $resource = $result->fetch_assoc();
        $stmt->close();
        $conn->next_result();
        return $resource;
    }
    $stmt->close();
    $conn->next_result();
    return null;
}

function getAllResourceCategories() {
    return [
        ['id' => 'textbook', 'name' => 'Textbook', 'description' => 'Educational textbooks'],
        ['id' => 'ebook', 'name' => 'E-Book', 'description' => 'Electronic books'],
        ['id' => 'presentation', 'name' => 'Presentation', 'description' => 'Slide presentations'],
        ['id' => 'worksheet', 'name' => 'Worksheet', 'description' => 'Practice worksheets']
    ];
}

// Donation-related functions
function getAllDonations() {
    global $conn;
    
    try {
        // SP no longer joins users, directly returns donor_name
        $result = $conn->query("CALL sp_get_all_donations()");
        if (!$result) {
            error_log("Error in getAllDonations: " . $conn->error);
            return [];
        }
        
        $donations = [];
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $donations[] = $row;
            }
        }
        $result->free();
        $conn->next_result();
        return $donations;
    } catch (Exception $e) {
        error_log("Exception in getAllDonations: " . $e->getMessage());
        return [];
    }
}

function addDonation($title, $description, $resource_type, $quantity, $donor_name, $school_id) {
    global $conn;
    
    try {
        // SP expects: p_title, p_description, p_resource_type, p_quantity, p_donor_name, p_school_id
        $stmt = $conn->prepare("CALL sp_add_donation(?, ?, ?, ?, ?, ?)");
        if (!$stmt) {
            error_log("Error preparing statement in addDonation: " . $conn->error);
            return false;
        }
        
        // Bind parameters: s=string, s=string, s=string (enum), i=integer, s=string, i=integer
        $stmt->bind_param("sssisi", $title, $description, $resource_type, $quantity, $donor_name, $school_id);
        
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            if ($result && $result->num_rows > 0) {
                 $row = $result->fetch_assoc();
                 $newId = $row['id'];
                 $stmt->close();
                 $conn->next_result();
                 return $newId;
            }
            $newId = $conn->insert_id; 
            $stmt->close();
            $conn->next_result();
            return $newId > 0 ? $newId : false;
        } else {
            error_log("Error executing addDonation statement: (" . $stmt->errno . ") " . $stmt->error);
            $stmt->close();
            $conn->next_result(); // Ensure connection is ready for next query
            return false;
        }
    } catch (Exception $e) {
        error_log("Exception in addDonation: " . $e->getMessage());
        if (isset($stmt) && $stmt instanceof mysqli_stmt) {
            $stmt->close();
        }
        if ($conn->more_results()) {
             $conn->next_result();
        }
        return false;
    }
}

function updateDonation($id, $title, $description, $resource_type, $quantity, $donor_name) {
    global $conn;
    
    try {
        // SP expects: p_id, p_title, p_description, p_resource_type, p_quantity, p_donor_name
        $stmt = $conn->prepare("CALL sp_update_donation(?, ?, ?, ?, ?, ?)");
        if (!$stmt) {
            error_log("Error preparing statement in updateDonation: " . $conn->error);
            return false;
        }

        // Bind parameters: i=integer, s=string, s=string, s=string (enum), i=integer, s=string
        $stmt->bind_param("isssis", $id, $title, $description, $resource_type, $quantity, $donor_name);
        
        if ($stmt->execute()) {
            $result = $stmt->get_result();
             if ($result && $result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $affected_rows = isset($row['affected_rows']) ? $row['affected_rows'] : 0;
                $stmt->close();
                $conn->next_result();
                return $affected_rows >= 0; // Return true even if 0 rows changed
            }
            $affected_rows_direct = $stmt->affected_rows; 
            $stmt->close();
            $conn->next_result();
            return $affected_rows_direct >= 0; // Return true if update succeeded, even if 0 rows changed
        } else {
             error_log("Error executing updateDonation statement: (" . $stmt->errno . ") " . $stmt->error);
            $stmt->close();
            $conn->next_result(); // Ensure connection is ready for next query
            return false;
        }
    } catch (Exception $e) {
        error_log("Exception in updateDonation: " . $e->getMessage());
        if (isset($stmt) && $stmt instanceof mysqli_stmt) {
            $stmt->close();
        }
        if ($conn->more_results()) {
             $conn->next_result();
        }        
        return false;
    }
}

function deleteDonation($id) {
    global $conn;
    
    try {
        $stmt = $conn->prepare("CALL sp_delete_donation(?)");
        if (!$stmt) {
            error_log("Error preparing statement in deleteDonation: " . $conn->error);
            return false;
        }
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            if ($result && $result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $affected_rows = isset($row['affected_rows']) ? $row['affected_rows'] : 0;
                $stmt->close();
                $conn->next_result();
                return $affected_rows > 0;
            }
            $affected_rows_direct = $stmt->affected_rows;
            $stmt->close();
            $conn->next_result();
            return $affected_rows_direct > 0; // Return true if rows were affected
        }
        error_log("Error executing deleteDonation statement: (" . $stmt->errno . ") " . $stmt->error);
        $stmt->close();
        $conn->next_result();
        return false;
    } catch (Exception $e) {
        error_log("Exception in deleteDonation: " . $e->getMessage());
        if (isset($stmt) && $stmt instanceof mysqli_stmt) { $stmt->close(); }
        if ($conn->more_results()) { $conn->next_result(); }
        return false;
    }
}

function getDonationById($id) {
    global $conn;
    
    try {
        $stmt = $conn->prepare("CALL sp_get_donation_by_id(?)");
        if (!$stmt) {
            error_log("Error preparing statement in getDonationById: " . $conn->error);
            return null;
        }
        
        $stmt->bind_param("i", $id);
        $stmt->execute();
        
        $result = $stmt->get_result();
        if ($result && $result->num_rows > 0) {
            $donation = $result->fetch_assoc();
            $stmt->close();
            $conn->next_result();
            return $donation;
        }
        $stmt->close();
        $conn->next_result();
        return null;
    } catch (Exception $e) {
        error_log("Exception in getDonationById: " . $e->getMessage());
        if (isset($stmt) && $stmt instanceof mysqli_stmt) { $stmt->close(); }
        if ($conn->more_results()) { $conn->next_result(); }
        return null;
    }
}

function searchDonations($searchTerm = '') {
    global $conn;
    
    try {
        $stmt = $conn->prepare("CALL sp_search_donations(?)");
        if (!$stmt) {
            error_log("Error preparing statement in searchDonations: " . $conn->error);
            return [];
        }
        
        // Add wildcard characters for LIKE search
        $searchPattern = "%$searchTerm%";
        $stmt->bind_param("s", $searchPattern);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $donations = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $donations[] = $row;
            }
        }
        $stmt->close();
        $conn->next_result();
        return $donations;
    } catch (Exception $e) {
        error_log("Exception in searchDonations: " . $e->getMessage());
        if (isset($stmt) && $stmt instanceof mysqli_stmt) { $stmt->close(); }
        if ($conn->more_results()) { $conn->next_result(); }
        return [];
    }
}

function getSchoolsForDonation() {
    global $conn;
    $sql = "SELECT id, name FROM schools ORDER BY name ASC";
    $result = $conn->query($sql);
    
    $schools = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $schools[] = $row;
        }
    }
    return $schools;
}
?> 