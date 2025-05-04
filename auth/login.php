<?php
require_once 'config.php';
require_once 'db_connect.php';
require_once 'auth_functions.php';

$google_auth_url = $google_client->createAuthUrl();
$error_message = "";

if (isLoggedIn()) {
    if (isAdmin()) {
        redirect("../admin/index.php");
    } else {
        redirect("../users/index.php");
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $login = sanitizeInput($_POST['login']);
    $password = sanitizeInput($_POST['password']);
    $remember = isset($_POST['remember']) ? true : false;

    if (empty($login) || empty($password)) {
        $error_message = "All fields are required";
    } else {
        $stmt = $conn->prepare("SELECT id, username, email, password, user_type, name FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $login, $login);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            if (verifyPassword($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['user_type'] = $user['user_type'];
                $_SESSION['name'] = $user['name'];

                if ($remember) {
                    $token = bin2hex(random_bytes(32));
                    $expires = time() + (30 * 24 * 60 * 60);

                    $stmt = $conn->prepare("INSERT INTO remember_tokens (user_id, token, expires) VALUES (?, ?, ?)");
                    $stmt->bind_param("iss", $user['id'], $token, date('Y-m-d H:i:s', $expires));
                    $stmt->execute();

                    setcookie("remember_token", $token, $expires, "/", "", true, true);
                }

                if ($user['user_type'] === 'admin') {
                    redirect("../admin/index.php");
                } else {
                    redirect("../users/index.php");
                }
            } else {
                $error_message = "Invalid password";
            }
        } else {
            $error_message = "User not found";
        }
    }
}
include '../users/includes/header.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - EduShare</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .bg-primary-custom {
            background-color: #4CAF50;
        }

        .btn-primary {
            background-color: #4CAF50;
            border-color: #4CAF50;
        }

        .btn-primary:hover {
            background-color: #388E3C;
            border-color: #388E3C;
        }

        .text-primary {
            color: #4CAF50 !important;
        }
    </style>
</head>

<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-primary-custom text-white text-center">
                        <h3>Login to EduShare</h3>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($error_message)): ?>
                            <div class="alert alert-danger"> <?php echo $error_message; ?> </div>
                        <?php endif; ?>
                        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                            <div class="mb-3">
                                <label for="login" class="form-label">Email or Username</label>
                                <input type="text" id="login" name="login" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" id="password" name="password" class="form-control" required>
                            </div>
                            <div class="mb-3 form-check">
                                <input class="form-check-input" type="checkbox" id="remember" name="remember">
                                <label class="form-check-label" for="remember">Remember Me</label>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Sign In</button>
                            </div>
                            <div class="d-grid mt-3">
                                <a href="<?php echo htmlspecialchars($google_auth_url); ?>" class="btn btn-primary">
                                    <i class="fab fa-google"></i> Sign in with BSU ACCOUNT
                                </a>
                            </div>
                            <div class="text-center mt-3">
                                <a href="#" class="text-primary">Forgot Password?</a>
                            </div>
                            <div class="text-center mt-3">
                                <p>Not a member? <a href="register.php" class="text-primary">Register</a></p>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>