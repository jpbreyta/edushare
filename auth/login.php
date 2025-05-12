<?php
require_once '../includes/functions.php';
require_once 'db_connect.php';
require_once 'auth_functions.php';
require_once 'config.php';

$google_auth_url = $google_client->createAuthUrl();
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
    $password = $_POST['password'];
    $remember = isset($_POST['remember']) ? true : false;
    
    if (empty($login) || empty($password)) {
        redirect_with_message("login.php", "Please fill in all fields", "error");
    }
    
    $user = authenticate_user($login, $password);
    
    if ($user) {
        // Set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['user_type'] = $user['user_type'];
        
        // Handle remember me
        if ($remember) {
            $token = generate_remember_token();
            set_remember_token($user['id'], $token);
            setcookie('remember_token', $token, time() + (86400 * 30), "/"); // 30 days
        }
        
        // Redirect based on user type
        if ($user['user_type'] === 'admin') {
            redirect_with_message("../admin/index.php", "Welcome back, " . $user['name'] . "!");
        } else {
            redirect_with_message("../users/index.php", "Welcome back, " . $user['name'] . "!");
        }
    } else {
        redirect_with_message("login.php", "Invalid login credentials", "error");
    }
}

$page_title = "Login";
include '../includes/header.php';
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-primary-custom text-white text-center">
                    <h3>Login to EduShare</h3>
                </div>
                <div class="card-body">
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

<?php include '../includes/footer.php'; ?>