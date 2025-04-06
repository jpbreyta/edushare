<?php
require_once 'db_connect.php';
require_once 'auth_functions.php';

$error_message = "";
$success_message = "";

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $name = sanitize_input($_POST['name']);
    $username = sanitize_input($_POST['username']);
    $email = sanitize_input($_POST['email']);
    $password = sanitize_input($_POST['password']);
    $repeat_password = sanitize_input($_POST['repeat_password']);
    $user_type = sanitize_input($_POST['user_type']);
    $organization = sanitize_input($_POST['organization']);
    
    // Validate input
    if (empty($name) || empty($username) || empty($email) || empty($password) || empty($repeat_password) || empty($user_type)) {
        $error_message = "All fields are required";
    } elseif ($password !== $repeat_password) {
        $error_message = "Passwords do not match";
    } elseif (strlen($password) < 6) {
        $error_message = "Password must be at least 6 characters long";
    } else {
        // Check if username or email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error_message = "Username or email already exists";
        } else {
            // Hash password
            $hashed_password = hash_password($password);
            
            // Insert new user
            $stmt = $conn->prepare("INSERT INTO users (name, username, email, password, user_type, organization) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssss", $name, $username, $email, $hashed_password, $user_type, $organization);
            
            if ($stmt->execute()) {
                $success_message = "Registration successful! You can now login.";
            } else {
                $error_message = "Error: " . $stmt->error;
            }
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
    <title>Register - EduShare</title>
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
                                <a class="nav-link" href="login.php">Login</a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link active" href="register.php">Register</a>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($error_message)): ?>
                            <div class="alert alert-danger"><?php echo $error_message; ?></div>
                        <?php endif; ?>
                        
                        <?php if (!empty($success_message)): ?>
                            <div class="alert alert-success"><?php echo $success_message; ?></div>
                        <?php endif; ?>
                        
                        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                            <div class="form-outline ">
                                <input type="text" id="name" name="name" class="form-control" required />
                                <label class="form-label" for="name">Full Name</label>
                            </div>

                            <div class="form-outline ">
                                <input type="text" id="username" name="username" class="form-control" required />
                                <label class="form-label" for="username">Username</label>
                            </div>

                            <div class="form-outline ">
                                <input type="email" id="email" name="email" class="form-control" required />
                                <label class="form-label" for="email">Email</label>
                            </div>
                            
                            <div class="form-outline ">
                                <select class="form-select" id="user_type" name="user_type" required>
                                    <option value="" selected disabled>Select account type</option>
                                    <option value="school">School</option>
                                    <option value="donor">Donor</option>
                                    <option value="student">Student</option>
                                </select>
                                <label class="form-label" for="user_type">Account Type</label>
                            </div>
                            
                            <div class="form-outline ">
                                <input type="text" id="organization" name="organization" class="form-control" />
                                <label class="form-label" for="organization">School/Organization Name (if applicable)</label>
                            </div>

                            <div class="form-outline ">
                                <input type="password" id="password" name="password" class="form-control" required />
                                <label class="form-label" for="password">Password</label>
                            </div>

                            <div class="form-outline ">
                                <input type="password" id="repeat_password" name="repeat_password" class="form-control" required />
                                <label class="form-label" for="repeat_password">Repeat password</label>
                            </div>

                            <div class="form-check d-flex justify-content-center ">
                                <input class="form-check-input me-2" type="checkbox" value="" id="terms" name="terms" required />
                                <label class="form-check-label" for="terms">
                                    I have read and agree to the terms
                                </label>
                            </div>

                            <button type="submit" class="btn btn-primary btn-block mb-3">Sign up</button>

                            <div class="text-center">
                                <p>Already a member? <a href="login.php" class="text-primary">Login</a></p>
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