<?php
require_once '../includes/functions.php';
require_once 'db_connect.php';
require_once 'auth_functions.php';

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get & sanitize form data
    $name            = sanitize_input($_POST['name']);
    $username        = sanitize_input($_POST['username']);
    $email           = sanitize_input($_POST['email']);
    $password        = sanitize_input($_POST['password']);
    $repeat_password = sanitize_input($_POST['repeat_password']);
    $user_type       = sanitize_input($_POST['user_type']);
    $organization    = sanitize_input($_POST['organization']);
    
    // Validate input
    if (empty($name) || empty($username) || empty($email) || empty($password) || empty($repeat_password) || empty($user_type)) {
        redirect_with_message("register.php", "All fields are required", "error");
    } elseif ($password !== $repeat_password) {
        redirect_with_message("register.php", "Passwords do not match", "error");
    } elseif (strlen($password) < 6) {
        redirect_with_message("register.php", "Password must be at least 6 characters long", "error");
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        redirect_with_message("register.php", "Invalid email format", "error");
    } else {
        // Check if username or email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            redirect_with_message("register.php", "Username or email already exists", "error");
        } else {
            // Hash password
            $hashed_password = hash_password($password);
            
            // Insert new user
            $stmt = $conn->prepare("INSERT INTO users (name, username, email, password, user_type, organization) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssss", $name, $username, $email, $hashed_password, $user_type, $organization);
            
            if ($stmt->execute()) {
                redirect_with_message("login.php", "Registration successful! Please login.", "success");
            } else {
                redirect_with_message("register.php", "Registration failed. Please try again.", "error");
            }
        }
    }
}

$page_title = "Register";
include '../includes/header.php';
?>

<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-primary-custom text-white text-center">
                    <h3>Register to EduShare</h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                        <div class="form-outline mb-3">
                            <label class="form-label" for="name">Full Name</label>
                            <input type="text" id="name" name="name" class="form-control" required/>
                        </div>

                        <div class="form-outline mb-3">
                            <label class="form-label" for="username">Username</label>
                            <input type="text" id="username" name="username" class="form-control" required/>
                        </div>

                        <div class="form-outline mb-3">
                            <label class="form-label" for="email">Email</label>
                            <input type="email" id="email" name="email" class="form-control" required/>
                        </div>

                        <div class="form-outline mb-3">
                            <label class="form-label" for="user_type">Account Type</label>
                            <select class="form-select" id="user_type" name="user_type" required>
                                <option value="" disabled selected>Select account type</option>
                                <option value="school">School</option>
                                <option value="student">Student</option>
                            </select>
                        </div>

                        <div class="form-outline mb-3">
                            <label class="form-label" for="organization">Organization/School Name</label>
                            <input type="text" id="organization" name="organization" class="form-control"/>
                        </div>

                        <div class="form-outline mb-3">
                            <label class="form-label" for="password">Password</label>
                            <input type="password" id="password" name="password" class="form-control" required/>
                        </div>

                        <div class="form-outline mb-3">
                            <label class="form-label" for="repeat_password">Repeat Password</label>
                            <input type="password" id="repeat_password" name="repeat_password" class="form-control" required/>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Register</button>
                        </div>
                    </form>
                    <div class="text-center mt-3">
                        <p>Already have an account? <a href="login.php">Login here</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
