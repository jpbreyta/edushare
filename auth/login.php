<?php
require_once 'db_connect.php';
require_once 'auth_functions.php';

$error_message = "";

if (is_logged_in()) {
    if (is_admin()) {
        redirect("../admin/index.php");
    } else {
        redirect("../users/index.php");
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $login = sanitize_input($_POST['login']);
    $password = sanitize_input($_POST['password']);
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
            
            if (verify_password($password, $user['password'])) {
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
                    <div class="card-header bg-primary-custom text-white">
                        <h3 class="text-center mb-0">EduShare</h3>
                        <p class="text-center mb-0">Digital Library for Underprivileged Schools</p>
                    </div>
                    <div class="card-header">
                        <ul class="nav nav-pills nav-justified mb-3" id="ex1" role="tablist">
                            <li class="nav-item" role="presentation">
                                <a class="nav-link active" href="login.php">Login</a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link" href="register.php">Register</a>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($error_message)): ?>
                            <div class="alert alert-danger"><?php echo $error_message; ?></div>
                        <?php endif; ?>
                        
                        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                            <div class="form-outline mb-4">
                                <input type="text" id="login" name="login" class="form-control" required />
                                <label class="form-label" for="login">Email or username</label>
                            </div>

                            <div class="form-outline mb-4">
                                <input type="password" id="password" name="password" class="form-control" required />
                                <label class="form-label" for="password">Password</label>
                            </div>

                            <div class="row mb-4">
                                <div class="col-md-6 d-flex justify-content-center">
                                    <div class="form-check mb-3 mb-md-0">
                                        <input class="form-check-input" type="checkbox" value="1" id="remember" name="remember" />
                                        <label class="form-check-label" for="remember"> Remember me </label>
                                    </div>
                                </div>

                                <div class="col-md-6 d-flex justify-content-center">
                                    <a href="#!" class="text-primary">Forgot password?</a>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary btn-block mb-4">Sign in</button>

                            <div class="text-center">
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