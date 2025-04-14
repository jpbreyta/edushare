<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/db_connect.php';

session_start();

$client = new Google\Client();
$client->setClientId('312842461895-hkhj6va15q0efkdqgki0hj470o7ss6mg.apps.googleusercontent.com');
$client->setClientSecret('YOUR_CLIENT_SECRET');
$client->setRedirectUri('http://localhost/your-project/callback.php');

if (isset($_GET['code'])) {
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
    $client->setAccessToken($token);

$oauth2 = new Google\Service\Oauth2($client);
    $userInfo = $oauth2->userinfo->get();

    $email = $userInfo->email;
    $name = $userInfo->name;

    // Only allow Batangas State University emails
    if (!str_ends_with($email, '@batstate-u.edu.ph')) {
        die("Access restricted to Batangas State University members only.");
    }

    // Check if user exists
    $stmt = $conn->prepare("SELECT id, user_type FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['email'] = $email;
        $_SESSION['name'] = $name;
        $_SESSION['user_type'] = $user['user_type'];

        // Redirect based on user type
        if ($user['user_type'] === 'admin') {
            header("Location: ../admin/index.php");
        } else {
            header("Location: ../users/index.php");
        }
    } else {
        // New user → Auto-register
        $defaultUserType = 'student'; // Or 'professor'
        $stmt = $conn->prepare("INSERT INTO users (name, email, user_type) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $email, $defaultUserType);
        $stmt->execute();

        $_SESSION['user_id'] = $conn->insert_id;
        $_SESSION['email'] = $email;
        $_SESSION['name'] = $name;
        $_SESSION['user_type'] = $defaultUserType;

        header("Location: ../users/index.php");
    }
} else {
    die("Authentication failed.");
}
?>
