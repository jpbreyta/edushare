<?php
session_start();

// Function to sanitize user input
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Function to check if user is admin
function isAdmin() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin';
}

// Function to check if user is school admin
function isSchoolAdmin() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'school_admin';
}

// Function to check if user is donor
function isDonor() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'donor';
}

// Function to check if user is teacher
function isTeacher() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'teacher';
}

// Function to check user type
function getUserType() {
    return isset($_SESSION['user_type']) ? $_SESSION['user_type'] : null;
}

// Function to redirect user
function redirect($url) {
    header("Location: $url");
    exit();
}

// Function to hash password
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

// Function to verify password
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

// Function to check if user has permission to access a page
function checkPermission($required_type) {
    if (!isLoggedIn()) {
        redirect("../login.php");
    }
    
    if ($required_type === 'admin' && !isAdmin()) {
        redirect("../unauthorized.php");
    }
    
    if ($required_type !== 'any' && $required_type !== 'admin' && 
        getUserType() !== $required_type && !isAdmin()) {
        redirect("../unauthorized.php");
    }
}

// Function to get file extension
function getFileExtension($filename) {
    return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
}

// Function to generate a unique filename
function generateUniqueFilename($filename) {
    $extension = getFileExtension($filename);
    return uniqid() . '.' . $extension;
}

// Function to check if file type is allowed
function isAllowedFileType($filename) {
    $allowed_extensions = array('pdf', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png', 'mp4', 'mp3');
    $extension = getFileExtension($filename);
    return in_array($extension, $allowed_extensions);
}

function requireLogin() {
    if (!isLoggedIn()) {
        redirect("../login.php");
    }
}

function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        redirect("../unauthorized.php");
    }
}

function requireSchoolAdmin() {
    requireLogin();
    if (!isSchoolAdmin()) {
        redirect("../unauthorized.php");
    }
}

function requireDonor() {
    requireLogin();
    if (!isDonor()) {
        redirect("../unauthorized.php");
    }
}

function requireTeacher() {
    requireLogin();
    if (!isTeacher()) {
        redirect("../unauthorized.php");
    }
}

function login($user) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_type'] = $user['user_type'];
    $_SESSION['organization'] = $user['organization'] ?? null;
}

function logout() {
    session_unset();
    session_destroy();
    redirect("../login.php");
}

function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    global $conn;
    $stmt = $conn->prepare("CALL sp_get_user_by_id(?)");
    $stmt->bind_param("i", $_SESSION['user_id']);
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

function validatePassword($password) {
    if (strlen($password) < PASSWORD_MIN_LENGTH) {
        return false;
    }
    
    if (PASSWORD_REQUIRES_UPPERCASE && !preg_match('@[A-Z]@', $password)) {
        return false;
    }
    
    if (PASSWORD_REQUIRES_LOWERCASE && !preg_match('@[a-z]@', $password)) {
        return false;
    }
    
    if (PASSWORD_REQUIRES_NUMBER && !preg_match('@[0-9]@', $password)) {
        return false;
    }
    
    if (PASSWORD_REQUIRES_SPECIAL && !preg_match('@[^\w]@', $password)) {
        return false;
    }
    
    return true;
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function generatePasswordResetToken() {
    return bin2hex(random_bytes(32));
}
?>