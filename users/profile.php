<?php
require_once '../auth/auth_functions.php';
require_once '../auth/db_connect.php';

if (!is_logged_in()) {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT name, email, user_type FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($name, $email, $user_type);
$stmt->fetch();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EduShare - My Profile</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
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

        .hero-section {
            background-color: #f8f9fa;
            padding: 80px 0;
            margin-bottom: 40px;
        }

        .feature-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: #4CAF50;
        }
    </style>
</head>

<body>
    <?php include './includes/header.php'; ?>

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-primary-custom text-white">
                        <h4 class="mb-0"><i class="fas fa-user-circle me-2"></i>My Profile</h4>
                    </div>
                    <div class="card-body">
                        <p><strong>Name:</strong> <?php echo htmlspecialchars($name); ?></p>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($email); ?></p>
                        <p><strong>User Type:</strong> <?php echo ucfirst(htmlspecialchars($user_type)); ?></p>
                        <a href="../auth/logout.php" class="btn btn-outline-secondary mt-3"><i class="fas fa-sign-out-alt me-1"></i>Logout</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include './includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
