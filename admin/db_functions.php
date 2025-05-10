<?php
require_once '../auth/db_connect.php';
require_once '../auth/config.php';
require_once '../auth/auth_functions.php';

// User-related functions
// Register user using stored procedure
function registerUser($name, $username, $email, $password, $user_type, $organization = null) {
    global $conn;
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("CALL sp_register_user(?, ?, ?, ?, ?, ?)");
    if (!$stmt) {
        error_log("Prepare failed: (" . $conn->errno . ") " . $conn->error);
        return false;
    }
    $stmt->bind_param("ssssss", $name, $username, $email, $hashed_password, $user_type, $organization);
    $result = $stmt->execute();
    $stmt->close();
    $conn->next_result();
    return $result;
}

// Login user using stored procedure
function loginUser($email, $password) {
    global $conn;
    $stmt = $conn->prepare("CALL sp_login_user(?)");
    if (!$stmt) {
        error_log("Prepare failed: (" . $conn->errno . ") " . $conn->error);
        return false;
    }
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $stmt->close();
            $conn->next_result();
            return $user;
        }
    }
    $stmt->close();
    $conn->next_result();
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

function updateUser($id, $name, $username, $email, $user_type, $organization = null) {
    global $conn;
    $stmt = $conn->prepare("CALL sp_update_user(?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssss", $id, $name, $username, $email, $user_type, $organization);
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $row = $result ? $result->fetch_assoc() : null;
        $stmt->close();
        $conn->next_result();
        return $row && isset($row['affected_rows']) ? $row['affected_rows'] > 0 : true;
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
        return $row && isset($row['affected_rows']) ? $row['affected_rows'] > 0 : true;
    }
    return false;
}

function deleteUser($id) {
    global $conn;
    
    $stmt = $conn->prepare("CALL sp_delete_user(?)");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $affected = $stmt->affected_rows;
        $stmt->close();
        
        if ($conn->more_results()) {
            $conn->next_result();
        }
        return $affected > 0;
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
    $stmt = $conn->prepare("CALL sp_get_users_by_type(?)");
    if (!$stmt) {
        error_log("Prepare failed in getUsersByType: (" . $conn->errno . ") " . $conn->error);
        return [];
    }
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
    
    $valid_levels = ['primary', 'secondary', 'tertiary'];
    if (!in_array($level, $valid_levels)) {
        error_log("Invalid school level value: " . $level);
        return false;
    }
    $stmt = $conn->prepare("CALL sp_add_school(?, ?, ?, ?, ?, ?, ?)");
    if (!$stmt) {
        error_log("Prepare failed in addSchool: (" . $conn->errno . ") " . $conn->error);
        return false;
    }
    $stmt->bind_param("sssssss", $name, $level, $region, $address, $contact_person, $email, $phone);
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
            $conn->next_result();
            return $newId > 0 ? $newId : false;
        }
    } else {
        error_log("Execute failed in addSchool: (" . $stmt->errno . ") " . $stmt->error);
        $stmt->close();
        $conn->next_result();
        return false;
    }
}

function updateSchool($id, $name, $level, $region, $address, $contact_person, $email, $phone) {
    global $conn;
    // Ensure level is one of the valid ENUM values
    $valid_levels = ['primary', 'secondary', 'tertiary'];
    if (!in_array($level, $valid_levels)) {
        error_log("Invalid school level value for update: " . $level);
        return false;
    }
    $stmt = $conn->prepare("CALL sp_update_school(?, ?, ?, ?, ?, ?, ?, ?)");
    if (!$stmt) {
        error_log("Prepare failed in updateSchool: (" . $conn->errno . ") " . $conn->error);
        return false;
    }
    $stmt->bind_param("isssssss", $id, $name, $level, $region, $address, $contact_person, $email, $phone);
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $row = $result ? $result->fetch_assoc() : null;
        $stmt->close();
        $conn->next_result();
        return $row && isset($row['affected_rows']) ? $row['affected_rows'] > 0 : true;
    } else {
        error_log("Execute failed in updateSchool: (" . $stmt->errno . ") " . $stmt->error);
        $stmt->close();
        $conn->next_result();
        return false;
    }
}

function deleteSchool($id) {
    global $conn;
    if (empty($id)) {
        error_log("Empty school ID provided for deletion");
        return false;
    }
    $stmt = $conn->prepare("CALL sp_delete_school(?)");
    if (!$stmt) {
        error_log("Prepare failed in deleteSchool: (" . $conn->errno . ") " . $conn->error);
        return false;
    }
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $row = $result ? $result->fetch_assoc() : null;
        $stmt->close();
        $conn->next_result();
        return $row && isset($row['affected_rows']) ? $row['affected_rows'] > 0 : true;
    } else {
        error_log("Execute failed in deleteSchool: (" . $stmt->errno . ") " . $stmt->error);
        $stmt->close();
        $conn->next_result();
        return false;
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

// Donation-related functions
function getAllDonations() {
    global $conn;
    $result = $conn->query("CALL sp_get_all_donations()");
    
    $donations = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $donations[] = $row;
        }
    }
    if ($result) $result->free();
    if ($conn->more_results()) $conn->next_result();
    return $donations;
}

function addDonation($title, $description, $resource_type, $quantity, $donor_name, $school_id) {
    global $conn;
    
    
    $valid_types = ['book', 'equipment', 'software', 'materials', 'other'];
    if (!in_array($resource_type, $valid_types)) {
        error_log("Invalid resource type: " . $resource_type);
        return false;
    }
    
    $stmt = $conn->prepare("CALL sp_add_donation(?, ?, ?, ?, ?, ?)");
    if (!$stmt) {
        error_log("Prepare failed: (" . $conn->errno . ") " . $conn->error);
        return false;
    }
    
    $stmt->bind_param("sssisi", $title, $description, $resource_type, $quantity, $donor_name, $school_id);
    
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $row = $result ? $result->fetch_assoc() : null;
        $stmt->close();
        if ($conn->more_results()) $conn->next_result();
        return $row && isset($row['id']) ? $row['id'] : true;
    } else {
        error_log("Execute failed: (" . $stmt->errno . ") " . $stmt->error);
        $stmt->close();
        return false;
    }
}

function updateDonation($id, $title, $description, $resource_type, $quantity, $donor_name) {
    global $conn;
    
    $valid_types = ['book', 'equipment', 'software', 'materials', 'other'];
    if (!in_array($resource_type, $valid_types)) {
        error_log("Invalid resource type: " . $resource_type);
        return false;
    }
    
    try {
        $stmt = $conn->prepare("CALL sp_update_donation(?, ?, ?, ?, ?, ?)");
        if (!$stmt) {
            error_log("Prepare failed in updateDonation: (" . $conn->errno . ") " . $conn->error);
            return false;
        }
        
        $stmt->bind_param("isssis", $id, $title, $description, $resource_type, $quantity, $donor_name);
        
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            if ($result) {
                $row = $result->fetch_assoc();
                $affected = isset($row['affected_rows']) ? $row['affected_rows'] : 0;
                $result->free();
            } else {
                 
                $affected = $stmt->affected_rows;
            }
            $stmt->close();
            if ($conn->more_results()) {
                $conn->next_result();
            }
             
            return true;
        } else {
            error_log("Execute failed in updateDonation: (" . $stmt->errno . ") " . $stmt->error);
            $stmt->close();
            return false;
        }
    } catch (Exception $e) {
        error_log("Exception in updateDonation: " . $e->getMessage());
        return false;
    }
}

function deleteDonation($id) {
    global $conn;
    
    $stmt = $conn->prepare("CALL sp_delete_donation(?)");
    if (!$stmt) {
        error_log("Prepare failed: (" . $conn->errno . ") " . $conn->error);
        return false;
    }
    
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $affected = $stmt->affected_rows;
        $stmt->close();
        if ($conn->more_results()) $conn->next_result();
        return $affected > 0;
    } else {
        error_log("Execute failed: (" . $stmt->errno . ") " . $stmt->error);
        $stmt->close();
        return false;
    }
}

function searchDonations($search_term) {
    global $conn;
    
    try {
        $stmt = $conn->prepare("CALL sp_search_donations(?)");
        if (!$stmt) {
            error_log("Prepare failed in searchDonations: (" . $conn->errno . ") " . $conn->error);
            return [];
        }
        
        $stmt->bind_param("s", $search_term);
        
        $donations = [];
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $donations[] = $row;
                }
                $result->free();
            }
        } else {
            error_log("Execute failed in searchDonations: (" . $stmt->errno . ") " . $stmt->error);
        }
        
        $stmt->close();
        if ($conn->more_results()) {
            $conn->next_result();
        }
        return $donations;
    } catch (Exception $e) {
        error_log("Exception in searchDonations: " . $e->getMessage());
        return [];
    }
    
    return $donations;
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
    if (empty($id)) {
        error_log("Empty resource ID provided for deletion");
        return false;
    }
    $stmt = $conn->prepare("CALL sp_delete_resource(?)");
    if (!$stmt) {
        error_log("Prepare failed in deleteResource: (" . $conn->errno . ") " . $conn->error);
        return false;
    }
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $row = $result ? $result->fetch_assoc() : null;
        $stmt->close();
        $conn->next_result();
        return $row && isset($row['affected_rows']) ? $row['affected_rows'] > 0 : true;
    } else {
        error_log("Execute failed in deleteResource: (" . $stmt->errno . ") " . $stmt->error);
        $stmt->close();
        $conn->next_result();
        return false;
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


// Get schools for donation using stored procedure
function getSchoolsForDonation() {
    global $conn;
    $result = $conn->query("CALL sp_get_schools_for_donation()");
    $schools = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $schools[] = $row;
        }
    }
    $result->free();
    $conn->next_result();
    return $schools;
}
?> 