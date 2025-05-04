<?php
require_once 'auth_functions.php';
require_once 'db_connect.php';

$_SESSION = array();

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

if (isset($_COOKIE['remember_token'])) {
    $token = $_COOKIE['remember_token'];

    $stmt = $conn->prepare("DELETE FROM remember_tokens WHERE token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();

    setcookie("remember_token", "", time() - 3600, "/", "", true, true);
}

session_destroy();

redirect("login.php");
?>