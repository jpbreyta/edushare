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

function getUserById($id) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        return $result->fetch_assoc();
    }
    return false;
}

// School-related functions
function getAllSchools() {
    global $conn;
    $sql = "SELECT * FROM schools ORDER BY name ASC";
    $result = $conn->query($sql);
    
    $schools = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $schools[] = $row;
        }
    }
    return $schools;
}

function addSchool($name, $level, $region, $address, $contact_person, $email, $phone) {
    global $conn;
    
    $stmt = $conn->prepare("INSERT INTO schools (name, level, region, address, contact_person, email, phone) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $name, $level, $region, $address, $contact_person, $email, $phone);
    
    return $stmt->execute();
}

function updateSchool($id, $name, $level, $region, $address, $contact_person, $email, $phone) {
    global $conn;
    
    $stmt = $conn->prepare("UPDATE schools SET name = ?, level = ?, region = ?, address = ?, contact_person = ?, email = ?, phone = ? WHERE id = ?");
    $stmt->bind_param("sssssssi", $name, $level, $region, $address, $contact_person, $email, $phone, $id);
    
    return $stmt->execute();
}

function deleteSchool($id) {
    global $conn;
    
    $stmt = $conn->prepare("DELETE FROM schools WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    return $stmt->execute();
}

function getSchoolById($id) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT * FROM schools WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    return null;
}

// Resource-related functions
function getAllResources() {
    global $conn;
    $sql = "SELECT r.*, u.name as uploader_name, u.user_type as uploader_type, s.name as school_name, rc.name as category_name
            FROM resources r 
            JOIN users u ON r.uploaded_by = u.id 
            LEFT JOIN schools s ON r.school_id = s.id
            JOIN resource_categories rc ON r.category_id = rc.id
            ORDER BY r.created_at DESC";
    $result = $conn->query($sql);
    
    $resources = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $resources[] = $row;
        }
    }
    return $resources;
}

function addResource($title, $description, $category_id, $resource_type, $file_path, $external_link, $target_audience, $uploaded_by, $school_id = null) {
    global $conn;
    
    $stmt = $conn->prepare("INSERT INTO resources (title, description, category_id, resource_type, file_path, external_link, target_audience, uploaded_by, school_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssissssii", $title, $description, $category_id, $resource_type, $file_path, $external_link, $target_audience, $uploaded_by, $school_id);
    
    return $stmt->execute();
}

function updateResource($id, $title, $description, $category_id, $resource_type, $file_path, $external_link, $target_audience, $school_id = null) {
    global $conn;
    
    $stmt = $conn->prepare("UPDATE resources SET title = ?, description = ?, category_id = ?, resource_type = ?, file_path = ?, external_link = ?, target_audience = ?, school_id = ? WHERE id = ?");
    $stmt->bind_param("ssissssii", $title, $description, $category_id, $resource_type, $file_path, $external_link, $target_audience, $school_id, $id);
    
    return $stmt->execute();
}

function deleteResource($id) {
    global $conn;
    
    $stmt = $conn->prepare("DELETE FROM resources WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    return $stmt->execute();
}

function getResourceById($id) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT r.*, u.name as uploader_name, u.user_type as uploader_type, s.name as school_name, rc.name as category_name
                           FROM resources r 
                           JOIN users u ON r.uploaded_by = u.id 
                           LEFT JOIN schools s ON r.school_id = s.id
                           JOIN resource_categories rc ON r.category_id = rc.id
                           WHERE r.id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    }
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
        $sql = "SELECT d.*, s.name as school_name 
                FROM donations d 
                LEFT JOIN schools s ON d.school_id = s.id 
                ORDER BY d.donation_date DESC";
        $result = $conn->query($sql);
        
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
        return $donations;
    } catch (Exception $e) {
        error_log("Exception in getAllDonations: " . $e->getMessage());
        return [];
    }
}

function addDonation($donor_name, $donor_email, $resource_type, $title, $description, $file_path, $external_link, $school_id, $donation_date, $purpose, $status, $notes = '') {
    global $conn;
    
    try {
        $stmt = $conn->prepare("INSERT INTO donations (donor_name, donor_email, resource_type, title, description, file_path, external_link, school_id, donation_date, purpose, status, notes) 
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        if (!$stmt) {
            error_log("Error preparing statement in addDonation: " . $conn->error);
            return false;
        }
        
        $stmt->bind_param("sssssssissss", $donor_name, $donor_email, $resource_type, $title, $description, $file_path, $external_link, $school_id, $donation_date, $purpose, $status, $notes);
        
        $result = $stmt->execute();
        if (!$result) {
            error_log("Error executing statement in addDonation: " . $stmt->error);
            return false;
        }
        
        return true;
    } catch (Exception $e) {
        error_log("Exception in addDonation: " . $e->getMessage());
        return false;
    }
}

function updateDonation($id, $donor_name, $donor_email, $resource_type, $title, $description, $file_path, $external_link, $school_id, $donation_date, $purpose, $status, $notes = '') {
    global $conn;
    
    $stmt = $conn->prepare("UPDATE donations SET 
                           donor_name = ?, 
                           donor_email = ?, 
                           resource_type = ?, 
                           title = ?,
                           description = ?,
                           file_path = ?,
                           external_link = ?,
                           school_id = ?, 
                           donation_date = ?, 
                           purpose = ?, 
                           status = ?, 
                           notes = ? 
                           WHERE id = ?");
    $stmt->bind_param("sssssssissssi", $donor_name, $donor_email, $resource_type, $title, $description, $file_path, $external_link, $school_id, $donation_date, $purpose, $status, $notes, $id);
    
    return $stmt->execute();
}

function deleteDonation($id) {
    global $conn;
    
    $stmt = $conn->prepare("DELETE FROM donations WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    return $stmt->execute();
}

function getDonationById($id) {
    global $conn;
    
    try {
        $stmt = $conn->prepare("SELECT d.*, s.name as school_name 
                               FROM donations d 
                               LEFT JOIN schools s ON d.school_id = s.id 
                               WHERE d.id = ?");
        if (!$stmt) {
            error_log("Error preparing statement in getDonationById: " . $conn->error);
            return null;
        }
        
        $stmt->bind_param("i", $id);
        $stmt->execute();
        
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        }
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