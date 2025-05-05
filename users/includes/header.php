<?php
require_once '../auth/auth_functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EduShare - Digital Library for Underprivileged Schools</title>
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
        .nav-pills .nav-link.active {
            background-color: #4CAF50;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary-custom">
        <div class="container">
            <a class="navbar-brand" href="../index.php">
                <i class="fas fa-book-open me-2"></i>EduShare
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="../index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="resources.php">Resources</a>
                    </li>
                    <?php if (is_logged_in() && (get_user_type() == 'donor' || get_user_type() == 'school' || get_user_type() == 'admin')): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="upload.php">Upload Resources</a>
                    </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link" href="sdg4.php">About SDG 4</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <?php if (is_logged_in()): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-user-circle me-1"></i> <?php echo $_SESSION['name']; ?> (<?php echo ucfirst($_SESSION['user_type']); ?>)
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                                <li><a class="dropdown-item" href="index.php">Dashboard</a></li>
                                <li><a class="dropdown-item" href="profile.php">Profile</a></li>
                                <?php if (get_user_type() == 'admin'): ?>
                                <li><a class="dropdown-item" href="#">Admin Panel</a></li>
                                <?php endif; ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="../auth/logout.php">Logout</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="../auth/login.php">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="../auth/register.php">Register</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container mt-4">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>