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

function addUser($name, $email, $password, $user_type, $organization = null, $phone = null, $address = null) {
    global $conn;
    
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("CALL sp_add_user(?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $name, $email, $hashed_password, $user_type, $organization, $phone, $address);
    
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        $conn->next_result();
        return $row['id'];
    }
    return false;
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
    
    $stmt = $conn->prepare("CALL sp_get_users_by_type(?)");
    $stmt->bind_param("s", $user_type);
    $stmt->execute();
    
    $result = $stmt->get_result();
    $users = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
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

function addSchool($name, $level, $region, $address, $contact_person, $email, $phone) {
    global $conn;
    
    $stmt = $conn->prepare("CALL sp_add_school(?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $name, $level, $region, $address, $contact_person, $email, $phone);
    
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        $conn->next_result();
        return $row['id'];
    }
    return false;
}

function updateSchool($id, $name, $level, $region, $address, $contact_person, $email, $phone) {
    global $conn;
    
    $stmt = $conn->prepare("CALL sp_update_school(?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssssss", $id, $name, $level, $region, $address, $contact_person, $email, $phone);
    
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        $conn->next_result();
        return $row['affected_rows'] > 0;
    }
    return false;
}

function deleteSchool($id) {
    global $conn;
    
    $stmt = $conn->prepare("CALL sp_delete_school(?)");
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

function addResource($title, $description, $category_id, $resource_type, $file_path, $external_link, $target_audience, $uploaded_by, $school_id = null) {
    global $conn;
    
    $stmt = $conn->prepare("CALL sp_add_resource(?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssissssii", $title, $description, $category_id, $resource_type, $file_path, $external_link, $target_audience, $uploaded_by, $school_id);
    
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        $conn->next_result();
        return $row['id'];
    }
    return false;
}

function updateResource($id, $title, $description, $category_id, $resource_type, $file_path, $external_link, $target_audience, $school_id = null) {
    global $conn;
    
    $stmt = $conn->prepare("CALL sp_update_resource(?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ississssi", $id, $title, $description, $category_id, $resource_type, $file_path, $external_link, $target_audience, $school_id);
    
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
    
    $stmt = $conn->prepare("CALL sp_delete_resource(?)");
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
    global $conn;
    $sql = "SELECT * FROM resource_categories ORDER BY name ASC";
    $result = $conn->query($sql);
    
    $categories = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $categories[] = $row;
        }
    }
    return $categories;
}

// Donation-related functions
function getAllDonations() {
    global $conn;
    
    try {
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

function addDonation($donor_name, $donor_email, $resource_type, $title, $description, $file_path, $external_link, $school_id, $donation_date, $purpose, $notes = '') {
    global $conn;
    
    try {
        $stmt = $conn->prepare("CALL sp_add_donation(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        if (!$stmt) {
            error_log("Error preparing statement in addDonation: " . $conn->error);
            return false;
        }
        
        $stmt->bind_param("sssssssisss", $donor_name, $donor_email, $resource_type, $title, $description, $file_path, $external_link, $school_id, $donation_date, $purpose, $notes);
        
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();
            $conn->next_result();
            return $row['id'];
        }
        return false;
    } catch (Exception $e) {
        error_log("Exception in addDonation: " . $e->getMessage());
        return false;
    }
}

function updateDonation($id, $donor_name, $donor_email, $resource_type, $title, $description, $file_path, $external_link, $school_id, $donation_date, $purpose, $notes = '') {
    global $conn;
    
    $stmt = $conn->prepare("CALL sp_update_donation(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssssissss", $id, $donor_name, $donor_email, $resource_type, $title, $description, $file_path, $external_link, $school_id, $donation_date, $purpose, $notes);
    
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        $conn->next_result();
        return $row['affected_rows'] > 0;
    }
    return false;
}

function deleteDonation($id) {
    global $conn;
    
    $stmt = $conn->prepare("CALL sp_delete_donation(?)");
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
        if ($result->num_rows > 0) {
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
        return null;
    }
}

function getSchoolsForDonation() {
    global $conn;
    $sql = "SELECT id, name FROM schools ORDER BY name ASC";
    $result = $conn->query($sql);
    
    $schools = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $schools[] = $row;
        }
    }
    return $schools;
}

function getDonors() {
    global $conn;
    $sql = "SELECT id, name FROM users WHERE user_type = 'donor' ORDER BY name ASC";
    $result = $conn->query($sql);
    
    $donors = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $donors[] = $row;
        }
    }
    return $donors;
}
?> 