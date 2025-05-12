<?php
// Function to redirect with message
function redirect_with_message($url, $message, $type = 'success') {
    $param = $type === 'success' ? 'success' : ($type === 'warning' ? 'warning' : 'error');
    header("Location: $url?$param=" . urlencode($message));
    exit();
}

// Function to check if user is logged in
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

// Function to get user type
function get_user_type() {
    return isset($_SESSION['user_type']) ? $_SESSION['user_type'] : null;
}

// Function to get current page
function get_current_page() {
    $current_page = basename($_SERVER['PHP_SELF']);
    return $current_page;
}
?> 