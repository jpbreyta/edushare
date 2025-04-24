<?php
require_once '../auth/config.php';
require_once __DIR__ . '/db_functions.php';

// Check if session is not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit();
}

$active_tab = 'dashboard';

// Get all data for dashboard
$schools = getAllSchools();
$resources = getAllResources();
$donations = getAllDonations();
$users = getAllUsers();

// Calculate statistics
$total_schools = count($schools);
$total_resources = count($resources);
$total_donations = count($donations);
$total_users = count($users);

// Calculate user type distribution
$user_types = [
    'school_admin' => 0,
    'donor' => 0,
    'teacher' => 0
];

foreach ($users as $user) {
    if (isset($user_types[$user['user_type']])) {
        $user_types[$user['user_type']]++;
    }
}

$total_user_types = array_sum($user_types);
$school_admin_percentage = $total_user_types > 0 ? round(($user_types['school_admin'] / $total_user_types) * 100) : 0;
$donor_percentage = $total_user_types > 0 ? round(($user_types['donor'] / $total_user_types) * 100) : 0;
$teacher_percentage = $total_user_types > 0 ? round(($user_types['teacher'] / $total_user_types) * 100) : 0;

// Calculate donation status distribution
$donation_status = [
    'pending' => 0,
    'in_progress' => 0,
    'completed' => 0,
    'cancelled' => 0
];

foreach ($donations as $donation) {
    if (isset($donation_status[$donation['status']])) {
        $donation_status[$donation['status']]++;
    }
}

// Calculate percentages safely
$donation_percentages = [
    'pending' => $total_donations > 0 ? round(($donation_status['pending'] / $total_donations) * 100) : 0,
    'in_progress' => $total_donations > 0 ? round(($donation_status['in_progress'] / $total_donations) * 100) : 0,
    'completed' => $total_donations > 0 ? round(($donation_status['completed'] / $total_donations) * 100) : 0,
    'cancelled' => $total_donations > 0 ? round(($donation_status['cancelled'] / $total_donations) * 100) : 0
];

// Get recent donations
$recent_donations = array_slice($donations, 0, 5);

// Get recent resources
$recent_resources = array_slice($resources, 0, 5);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - EduShare Admin</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
        }
        
        .dashboard-card {
            transition: transform 0.3s;
        }
        
        .dashboard-card:hover {
            transform: translateY(-5px);
        }
        
        .card-icon {
            font-size: 2rem;
            color: var(--primary-color);
        }
        
        .progress {
            height: 12px;
            border-radius: 6px;
        }
        
        .progress-bar {
            background-color: var(--primary-color);
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary-custom">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-book-open me-2"></i>EduShare Admin
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="../index.php" target="_blank">
                            <i class="fas fa-external-link-alt me-1"></i> View Site
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle me-1"></i> <?php echo htmlspecialchars($_SESSION['name']); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="#"><i class="fas fa-user-cog me-1"></i> Profile</a></li>
                            <li><a class="dropdown-item" href="#"><i class="fas fa-cog me-1"></i> Settings</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="../auth/logout.php"><i class="fas fa-sign-out-alt me-1"></i> Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 d-md-block sidebar collapse">
                <div class="position-sticky pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link <?php echo $active_tab == 'dashboard' ? 'active' : ''; ?>" href="index.php">
                                <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $active_tab == 'users' ? 'active' : ''; ?>" href="users.php">
                                <i class="fas fa-users me-2"></i> Users
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $active_tab == 'resources' ? 'active' : ''; ?>" href="resources.php">
                                <i class="fas fa-book me-2"></i> Resources
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $active_tab == 'schools' ? 'active' : ''; ?>" href="schools.php">
                                <i class="fas fa-school me-2"></i> Schools
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $active_tab == 'donations' ? 'active' : ''; ?>" href="donations.php">
                                <i class="fas fa-hand-holding-heart me-2"></i> Donations
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Dashboard</h1>
                </div>
                
                <!-- Statistics Cards -->
                <div class="row">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card dashboard-card h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-uppercase mb-1">Total Users</div>
                                        <div class="h5 mb-0 font-weight-bold"><?php echo $total_users; ?></div>
                                        <div class="mt-2 text-muted small">
                                            <span class="text-success me-2">
                                                <i class="fas fa-users"></i>
                                            </span>
                                            Active Users
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-users card-icon"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card dashboard-card h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-uppercase mb-1">Total Resources</div>
                                        <div class="h5 mb-0 font-weight-bold"><?php echo $total_resources; ?></div>
                                        <div class="mt-2 text-muted small">
                                            <span class="text-success me-2">
                                                <i class="fas fa-book"></i>
                                            </span>
                                            Available Resources
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-book card-icon"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card dashboard-card h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-uppercase mb-1">Total Donations</div>
                                        <div class="h5 mb-0 font-weight-bold"><?php echo $total_donations; ?></div>
                                        <div class="mt-2 text-muted small">
                                            <span class="text-success me-2">
                                                <i class="fas fa-hand-holding-heart"></i>
                                            </span>
                                            Total Donations
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-hand-holding-heart card-icon"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card dashboard-card h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-uppercase mb-1">Schools</div>
                                        <div class="h5 mb-0 font-weight-bold"><?php echo $total_schools; ?></div>
                                        <div class="mt-2 text-muted small">
                                            <span class="text-success me-2">
                                                <i class="fas fa-school"></i>
                                            </span>
                                            Registered Schools
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-school card-icon"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Charts Row -->
                <div class="row">
                    <!-- User Statistics -->
                    <div class="col-lg-4">
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h6 class="m-0 font-weight-bold">User Statistics</h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-4">
                                    <h6 class="small font-weight-bold">School Admins <span class="float-end"><?php echo $school_admin_percentage; ?>%</span></h6>
                                    <div class="progress">
                                        <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $school_admin_percentage; ?>%" aria-valuenow="<?php echo $school_admin_percentage; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <h6 class="small font-weight-bold">Donors <span class="float-end"><?php echo $donor_percentage; ?>%</span></h6>
                                    <div class="progress">
                                        <div class="progress-bar bg-info" role="progressbar" style="width: <?php echo $donor_percentage; ?>%" aria-valuenow="<?php echo $donor_percentage; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <h6 class="small font-weight-bold">Teachers <span class="float-end"><?php echo $teacher_percentage; ?>%</span></h6>
                                    <div class="progress">
                                        <div class="progress-bar bg-primary" role="progressbar" style="width: <?php echo $teacher_percentage; ?>%" aria-valuenow="<?php echo $teacher_percentage; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Donation Status -->
                    <div class="col-lg-4">
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h6 class="m-0 font-weight-bold">Donation Status</h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-4">
                                    <h6 class="small font-weight-bold">Pending <span class="float-end"><?php echo $donation_percentages['pending']; ?>%</span></h6>
                                    <div class="progress">
                                        <div class="progress-bar bg-warning" role="progressbar" style="width: <?php echo $donation_percentages['pending']; ?>%" aria-valuenow="<?php echo $donation_status['pending']; ?>" aria-valuemin="0" aria-valuemax="<?php echo $total_donations; ?>"></div>
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <h6 class="small font-weight-bold">In Progress <span class="float-end"><?php echo $donation_percentages['in_progress']; ?>%</span></h6>
                                    <div class="progress">
                                        <div class="progress-bar bg-info" role="progressbar" style="width: <?php echo $donation_percentages['in_progress']; ?>%" aria-valuenow="<?php echo $donation_status['in_progress']; ?>" aria-valuemin="0" aria-valuemax="<?php echo $total_donations; ?>"></div>
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <h6 class="small font-weight-bold">Completed <span class="float-end"><?php echo $donation_percentages['completed']; ?>%</span></h6>
                                    <div class="progress">
                                        <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $donation_percentages['completed']; ?>%" aria-valuenow="<?php echo $donation_status['completed']; ?>" aria-valuemin="0" aria-valuemax="<?php echo $total_donations; ?>"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Recent Activity -->
                    <div class="col-lg-4">
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h6 class="m-0 font-weight-bold">Recent Activity</h6>
                            </div>
                            <div class="card-body">
                                <div class="list-group list-group-flush">
                                    <?php foreach ($recent_donations as $donation): ?>
                                        <div class="list-group-item px-0">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h6 class="mb-0"><?php echo htmlspecialchars($donation['title']); ?></h6>
                                                    <small class="text-muted"><?php echo htmlspecialchars($donation['donor_name']); ?></small>
                                                </div>
                                                <span class="badge bg-<?php 
                                                    switch($donation['status']) {
                                                        case 'completed': echo 'success'; break;
                                                        case 'in_progress': echo 'info'; break;
                                                        case 'cancelled': echo 'danger'; break;
                                                        default: echo 'warning';
                                                    }
                                                ?>">
                                                    <?php echo ucfirst(str_replace('_', ' ', $donation['status'])); ?>
                                                </span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Resources -->
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h6 class="m-0 font-weight-bold">Recent Resources</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Type</th>
                                        <th>Uploaded By</th>
                                        <th>School</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_resources as $resource): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($resource['title']); ?></td>
                                            <td><?php echo htmlspecialchars($resource['resource_type']); ?></td>
                                            <td><?php echo htmlspecialchars($resource['uploader_name']); ?></td>
                                            <td><?php echo htmlspecialchars($resource['school_name'] ?? 'N/A'); ?></td>
                                            <td>
                                                <span class="badge bg-<?php 
                                                    switch($resource['status']) {
                                                        case 'approved': echo 'success'; break;
                                                        case 'rejected': echo 'danger'; break;
                                                        default: echo 'warning';
                                                    }
                                                ?>">
                                                    <?php echo ucfirst($resource['status']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('M d, Y', strtotime($resource['created_at'])); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- DataTables -->
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
</body>
</html>