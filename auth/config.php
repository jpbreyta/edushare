<?php
// Session configuration - only set if session hasn't started
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS
}

require_once 'C:/xampp/htdocs/ADBMS/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

$google_client = new Google\Client();
$google_client->setClientId($_ENV['GOOGLE_CLIENT_ID']);
$google_client->setClientSecret($_ENV['GOOGLE_CLIENT_SECRET']);
$google_client->setRedirectUri($_ENV['GOOGLE_REDIRECT_URI'] ?? 'http://localhost/ADBMS/auth/google_callback.php');
$google_client->addScope("email");
$google_client->addScope("profile");

// Application configuration
define('APP_NAME', 'EduShare');
define('APP_URL', 'http://localhost/ADBMS');
define('UPLOAD_DIR', '../uploads/');

// File upload settings
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_FILE_TYPES', [
    'jpg', 'jpeg', 'png', 'gif',
    'pdf', 'doc', 'docx',
    'xls', 'xlsx',
    'ppt', 'pptx'
]);

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Timezone
date_default_timezone_set('Asia/Manila');

// User types
define('USER_TYPES', [
    'admin' => 'Administrator',
    'school_admin' => 'School Administrator',
    'donor' => 'Donor',
    'teacher' => 'Teacher'
]);

// Status types
define('STATUS_TYPES', [
    'active' => 'Active',
    'inactive' => 'Inactive'
]);

// School levels
define('SCHOOL_LEVELS', [
    'elementary' => 'Elementary',
    'middle' => 'Middle School',
    'high' => 'High School',
    'college' => 'College'
]);

// Regions
define('REGIONS', [
    'north' => 'North',
    'south' => 'South',
    'east' => 'East',
    'west' => 'West',
    'central' => 'Central'
]);

// Resource categories
define('RESOURCE_TYPES', [
    'book' => 'Book',
    'document' => 'Document',
    'presentation' => 'Presentation',
    'video' => 'Video',
    'audio' => 'Audio',
    'software' => 'Software',
    'other' => 'Other'
]);

// Donation status
define('DONATION_STATUS', [
    'pending' => 'Pending',
    'approved' => 'Approved',
    'rejected' => 'Rejected',
    'delivered' => 'Delivered'
]);

// Password requirements
define('PASSWORD_MIN_LENGTH', 8);
define('PASSWORD_REQUIRES_SPECIAL', true);
define('PASSWORD_REQUIRES_NUMBER', true);
define('PASSWORD_REQUIRES_UPPERCASE', true);
define('PASSWORD_REQUIRES_LOWERCASE', true);

// Create upload directory if it doesn't exist
if (!file_exists(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0777, true);
}
?>
