<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include common functions
require_once __DIR__ . '/functions.php';

// Get current page
$current_page = get_current_page();
$is_auth_page = in_array($current_page, ['login.php', 'register.php']);
$is_admin_page = strpos($current_page, 'admin') !== false;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>EduShare - Digital Library for Underprivileged Schools</title>
    
    <!-- Common CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css">
    
    <!-- DataTables CSS (only for admin pages) -->
    <?php if ($is_admin_page): ?>
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <?php endif; ?>

    <style>
        :root {
            --primary-color: #4CAF50;
            --primary-dark: #388E3C;
            --primary-light: #C8E6C9;
            --accent-color: #8BC34A;
        }
        
        .bg-primary-custom {
            background-color: var(--primary-color);
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
        }
        
        .text-primary {
            color: var(--primary-color) !important;
        }
        
        .nav-pills .nav-link.active {
            background-color: var(--primary-color);
        }

        /* Admin specific styles */
        <?php if ($is_admin_page): ?>
        .sidebar {
            min-height: calc(100vh - 56px);
            background-color: #f8f9fa;
            border-right: 1px solid #dee2e6;
        }
        
        .sidebar .nav-link {
            color: #333;
            border-radius: 0;
        }
        
        .sidebar .nav-link.active {
            background-color: var(--primary-color);
            color: white;
        }
        
        .sidebar .nav-link:hover:not(.active) {
            background-color: var(--primary-light);
        }
        
        .content-wrapper {
            padding: 20px;
        }
        
        .table-actions .btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }
        <?php endif; ?>
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary-custom">
        <div class="container<?php echo $is_admin_page ? '-fluid' : ''; ?>">
            <a class="navbar-brand" href="<?php echo $is_admin_page ? 'index.php' : '../index.php'; ?>">
                <i class="fas fa-book-open me-2"></i>EduShare<?php echo $is_admin_page ? ' Admin' : ''; ?>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <?php if (!$is_admin_page): ?>
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="../index.php">Home</a>
                    </li>
                    <?php if (!$is_auth_page && is_logged_in()): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="resources.php">Resources</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="my_bookmarks.php">
                            <i class="fas fa-bookmark"></i> My Bookmarks
                        </a>
                    </li>
                    <?php if (get_user_type() == 'donor' || get_user_type() == 'school' || get_user_type() == 'admin'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="upload.php">Upload Resources</a>
                    </li>
                    <?php endif; ?>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link" href="sdg4.php">About SDG 4</a>
                    </li>
                </ul>
                <?php endif; ?>

                <ul class="navbar-nav ms-auto">
                    <?php if (is_logged_in()): ?>
                        <?php if ($is_admin_page): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="../index.php" target="_blank">
                                <i class="fas fa-external-link-alt me-1"></i> View Site
                            </a>
                        </li>
                        <?php endif; ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user-circle me-1"></i> 
                                <?php echo htmlspecialchars($_SESSION['name']); ?>
                                <?php if (isset($_SESSION['user_type'])): ?>
                                    (<?php echo ucfirst($_SESSION['user_type']); ?>)
                                <?php endif; ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <?php if (!$is_admin_page): ?>
                                <li><a class="dropdown-item" href="index.php">Dashboard</a>
                                <?php endif; ?>
                                <li><a class="dropdown-item" href="<?php echo $is_admin_page ? '#' : 'profile.php'; ?>">
                                    <i class="fas fa-user-cog me-1"></i> Profile
                                </a></li>
                                <?php if (get_user_type() == 'admin'): ?>
                                <li><a class="dropdown-item" href="<?php echo $is_admin_page ? '#' : '../admin/index.php'; ?>">
                                    <i class="fas fa-cog me-1"></i> Admin Panel
                                </a></li>
                                <?php endif; ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="<?php echo $is_admin_page ? '../' : '../'; ?>auth/logout.php">
                                    <i class="fas fa-sign-out-alt me-1"></i> Logout
                                </a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo $is_admin_page ? '../' : ''; ?>auth/login.php">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo $is_admin_page ? '../' : ''; ?>auth/register.php">Register</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <?php if (!$is_admin_page): ?>
    <div class="container mt-4">
    <?php endif; ?>

    <!-- Common Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>
    <script src="<?php echo $is_admin_page ? '../' : ''; ?>includes/sweet_alerts.js"></script>
</body>
</html> 