<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db_connect.php';

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

        // Debug information
        error_log("Login attempt - Email: " . $_SESSION['email']);
        error_log("Session data: " . print_r($_SESSION, true));

        if (!str_ends_with($_SESSION['email'], '@g.batstate-u.edu.ph')) {
            error_log("Access denied - Invalid email domain: " . $_SESSION['email']);
            echo "Access denied: Only Batangas State University users are allowed.<br>";
            echo "Your email: " . $_SESSION['email'] . "<br>";
            echo "Please use your @g.batstate-u.edu.ph email address.";
            session_destroy();
            exit();
        }

        // Check if user exists in database
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $user_info->email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            // Generate username from email (remove @g.batstate-u.edu.ph and any dots)
            $username = str_replace('.', '', explode('@', $user_info->email)[0]);
            
            // User doesn't exist, create new user
            $stmt = $conn->prepare("INSERT INTO users (username, name, email, user_type) VALUES (?, ?, ?, 'user')");
            $stmt->bind_param("sss", $username, $user_info->name, $user_info->email);
            
            if ($stmt->execute()) {
                $_SESSION['user_id'] = $conn->insert_id;
                $_SESSION['user_type'] = 'user';
                error_log("New user created with ID: " . $_SESSION['user_id']);
            } else {
                error_log("Error creating user: " . $stmt->error);
                echo "Error creating user account. Please try again.";
                session_destroy();
                exit();
            }
        } else {
            // User exists, get their information
            $user = $result->fetch_assoc();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_type'] = $user['user_type'];
            error_log("Existing user logged in with ID: " . $_SESSION['user_id']);
        }

        error_log("Login successful - Redirecting to index");
        header("Location: ../users/index.php");
        exit();
    } else {
        error_log("Token error: " . print_r($token["error"], true));
    }
}
?>
