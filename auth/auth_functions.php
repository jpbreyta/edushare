<?php
session_start();
require_once __DIR__ . '/../includes/functions.php';

// Function to sanitize user input
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Function to check if user is admin
function is_admin() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin';
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

/**
 * Authenticate a user with login credentials
 * 
 * @param string $login Username or email
 * @param string $password Password
 * @return array|false User data if authenticated, false otherwise
 */
function authenticate_user($login, $password) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT id, username, email, password, user_type, name FROM users WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $login, $login);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (verify_password($password, $user['password'])) {
            return $user;
        }
    }
    
    return false;
}

/**
 * Generate a secure remember token
 * 
 * @return string Secure random token
 */
function generate_remember_token() {
    return bin2hex(random_bytes(32));
}

/**
 * Set remember token for a user
 * 
 * @param int $user_id User ID
 * @param string $token Remember token
 * @return bool Success status
 */
function set_remember_token($user_id, $token) {
    global $conn;
    
    $expires = time() + (30 * 24 * 60 * 60); // 30 days
    
    $stmt = $conn->prepare("INSERT INTO remember_tokens (user_id, token, expires) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $user_id, $token, date('Y-m-d H:i:s', $expires));
    
    return $stmt->execute();
}
?>