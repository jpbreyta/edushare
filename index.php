<?php
require_once 'auth/auth_functions.php';
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
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary-custom">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-book-open me-2"></i>EduShare
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="users/resources.php">Resources</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="users/sdg4.php">About SDG 4</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Contact</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <?php if (isLoggedIn()): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-user-circle me-1"></i> <?php echo $_SESSION['name']; ?> (<?php echo ucfirst($_SESSION['user_type']); ?>)
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                                <li><a class="dropdown-item" href="users/index.php">Dashboard</a></li>
                                <li><a class="dropdown-item" href="#">Profile</a></li>
                                <?php if (getUserType() == 'admin'): ?>
                                    <li><a class="dropdown-item" href="./admin/index.php">Admin Panel</a></li>
                                <?php endif; ?>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item" href="auth/logout.php">Logout</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="auth/login.php">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="auth/register.php">Register</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h1 class="display-4 fw-bold mb-4">EduShare</h1>
                    <h2 class="mb-4">Digital Library for Underprivileged Schools</h2>
                    <p class="lead mb-4">
                        A platform where schools and donors can upload and track educational materials,
                        supporting SDG 4: Quality Education for all.
                    </p>
                    <div class="d-grid gap-2 d-md-flex justify-content-md-start">
                        <a href="users/resources.php" class="btn btn-primary btn-lg px-4 me-md-2">Browse Resources</a>
                        <a href="auth/register.php" class="btn btn-outline-secondary btn-lg px-4">Join Now</a>
                    </div>
                </div>
                <div class="col-md-6 text-center">
                    <img src="./assets/images/logo.png" alt="EduShare" class="img-fluid rounded shadow" style="max-width: 300px;">
                </div>

            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="container mb-5">
        <div class="text-center mb-5">
            <h2>Benefits for Users</h2>
            <p class="lead">EduShare provides a platform for sharing educational resources with those who need them most.</p>
        </div>

        <div class="row g-4">
            <div class="col-md-4">
                <div class="card h-100 text-center">
                    <div class="card-body">
                        <i class="fas fa-graduation-cap feature-icon"></i>
                        <h3 class="card-title">Increases Educational Access</h3>
                        <p class="card-text">Provides marginalized students with access to quality educational materials they might not otherwise have.</p>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card h-100 text-center">
                    <div class="card-body">
                        <i class="fas fa-database feature-icon"></i>
                        <h3 class="card-title">Organized Resource Sharing</h3>
                        <p class="card-text">Creates a structured system for cataloging, sharing, and accessing educational resources.</p>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card h-100 text-center">
                    <div class="card-body">
                        <i class="fas fa-users feature-icon"></i>
                        <h3 class="card-title">Community Participation</h3>
                        <p class="card-text">Encourages donors, schools, and students to actively participate in improving educational outcomes.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- SDG Alignment Section -->
    <section class="container mb-5">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h2>Aligned with SDG 4: Quality Education</h2>
                <p class="lead">EduShare directly supports the United Nations Sustainable Development Goal 4.</p>
                <p>SDG 4 aims to ensure inclusive and equitable quality education and promote lifelong learning opportunities for all. EduShare contributes to this goal by:</p>
                <ul>
                    <li>Providing access to educational resources for underprivileged schools</li>
                    <li>Creating a platform for knowledge sharing and collaboration</li>
                    <li>Connecting donors with schools that need resources the most</li>
                    <li>Tracking the impact of educational resource distribution</li>
                </ul>
            </div>
            <div class="col-md-6 text-center">
                <img src="./assets/images/sdg4.png" alt="SDG 4" class="img-fluid rounded shadow">
            </div>
        </div>
    </section>

    <!-- Call to Action -->
    <section class="container mb-5">
        <div class="p-5 text-center bg-light rounded">
            <h2>Join EduShare Today</h2>
            <p class="lead mb-4">Whether you're a school in need of resources, a donor looking to make an impact, or a student seeking educational materials, EduShare is for you.</p>
            <div class="d-grid gap-2 d-sm-flex justify-content-sm-center">
                <a href="auth/register.php" class="btn btn-primary btn-lg px-4 gap-3">Register Now</a>
                <a href="auth/login.php" class="btn btn-outline-secondary btn-lg px-4">Login</a>
            </div>
        </div>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>

<?php include './users/includes/footer.php'; ?>