<?php
require_once __DIR__ . '/config.php';

if (isset($_GET['code'])) {
    $token = $google_client->fetchAccessTokenWithAuthCode($_GET['code']);
    if (!isset($token["error"])) {
        $google_client->setAccessToken($token['access_token']);
        
        $google_oauth = new Google\Service\Oauth2($google_client);
        $user_info = $google_oauth->userinfo->get();
        
        session_start();
        $_SESSION['google_id'] = $user_info->id;
        $_SESSION['name'] = $user_info->name;
        $_SESSION['email'] = $user_info->email;
        $_SESSION['profile_pic'] = $user_info->picture;

        if (!str_ends_with($_SESSION['email'], '@batstate-u.edu.ph')) {
            echo "Access denied: Only Batangas State University users are allowed.";
            session_destroy();
            exit();
        }
        header("Location: ../users/index.php");
        exit();
    }
}
?>
