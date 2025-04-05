<?php
session_start();

// Function to sanitize user input
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Function to check if user is logged in
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

// Function to check if user is admin
function is_admin() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin';
}

// Function to check user type
function get_user_type() {
    return isset($_SESSION['user_type']) ? $_SESSION['user_type'] : null;
}

// Function to redirect user
function redirect($url) {
    header("Location: $url");
    exit();
}

// Function to hash password
function hash_password($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

// Function to verify password
function verify_password($password, $hash) {
    return password_verify($password, $hash);
}

// Function to check if user has permission to access a page
function check_permission($required_type) {
    if (!is_logged_in()) {
        redirect("../auth/login.php");
    }
    
    if ($required_type === 'admin' && !is_admin()) {
        redirect("../users/index.php");
    }
    
    if ($required_type !== 'any' && $required_type !== 'admin' && 
        get_user_type() !== $required_type && !is_admin()) {
        redirect("../users/index.php");
    }
}

// Function to get file extension
function get_file_extension($filename) {
    return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
}

// Function to generate a unique filename
function generate_unique_filename($filename) {
    $extension = get_file_extension($filename);
    return uniqid() . '.' . $extension;
}

// Function to check if file type is allowed
function is_allowed_file_type($filename) {
    $allowed_extensions = array('pdf', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png', 'mp4', 'mp3');
    $extension = get_file_extension($filename);
    return in_array($extension, $allowed_extensions);
}
?>