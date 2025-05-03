<?php
require_once '../auth/auth_functions.php';
check_permission('admin');
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EduShare Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
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
        
        .dashboard-card {
            border-left: 4px solid var(--primary-color);
            transition: transform 0.3s;
        }
        
        .dashboard-card:hover {
            transform: translateY(-5px);
        }
        
        .card-icon {
            font-size: 2.5rem;
            color: var(--primary-color);
        }
        
        .nav-tabs .nav-link.active {
            border-bottom: 3px solid var(--primary-color);
            font-weight: bold;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
        }
        
        .table-actions .btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary-custom">
        <div class="container-fluid">
            <a class="navbar-brand" href="../index.php">
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
                            <i class="fas fa-user-circle me-1"></i> Admin User
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
                        <li class="nav-item mt-3">
                            <a class="nav-link" href="#">
                                <i class="fas fa-cog me-2"></i> Settings
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">
                                <i class="fas fa-question-circle me-2"></i> Help
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="content-wrapper">
                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                        <h1 class="h2">Dashboard</h1>
                        <div class="btn-toolbar mb-2 mb-md-0">
                            <div class="btn-group me-2">
                                <button type="button" class="btn btn-sm btn-outline-secondary">
                                    <i class="fas fa-download me-1"></i> Export
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary">
                                    <i class="fas fa-print me-1"></i> Print
                                </button>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle">
                                <i class="fas fa-calendar me-1"></i> This week
                            </button>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card dashboard-card h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-uppercase mb-1">Total Users</div>
                                            <div class="h5 mb-0 font-weight-bold">1,248</div>
                                            <div class="mt-2 text-muted small"><span class="text-success me-2"><i class="fas fa-arrow-up"></i> 12%</span>Since last month</div>
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
                                            <div class="text-xs font-weight-bold text-uppercase mb-1">Resources</div>
                                            <div class="h5 mb-0 font-weight-bold">3,672</div>
                                            <div class="mt-2 text-muted small"><span class="text-success me-2"><i class="fas fa-arrow-up"></i> 8%</span>Since last month</div>
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
                                            <div class="text-xs font-weight-bold text-uppercase mb-1">Schools</div>
                                            <div class="h5 mb-0 font-weight-bold">246</div>
                                            <div class="mt-2 text-muted small"><span class="text-success me-2"><i class="fas fa-arrow-up"></i> 5%</span>Since last month</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-school card-icon"></i>
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
                                            <div class="text-xs font-weight-bold text-uppercase mb-1">Donations</div>
                                            <div class="h5 mb-0 font-weight-bold">$24,568</div>
                                            <div class="mt-2 text-muted small"><span class="text-success me-2"><i class="fas fa-arrow-up"></i> 15%</span>Since last month</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-hand-holding-heart card-icon"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-8">
                            <div class="card mb-4">
                                <div class="card-header bg-light">
                                    <h6 class="m-0 font-weight-bold">Recent Activity</h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>User</th>
                                                    <th>Activity</th>
                                                    <th>Resource</th>
                                                    <th>Date</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>John Smith</td>
                                                    <td>Uploaded</td>
                                                    <td>Mathematics Textbook</td>
                                                    <td>2023-06-15 14:23</td>
                                                </tr>
                                                <tr>
                                                    <td>Sarah Johnson</td>
                                                    <td>Downloaded</td>
                                                    <td>Science Lab Manual</td>
                                                    <td>2023-06-15 13:45</td>
                                                </tr>
                                                <tr>
                                                    <td>Maple Elementary</td>
                                                    <td>Registered</td>
                                                    <td>New School Account</td>
                                                    <td>2023-06-15 11:32</td>
                                                </tr>
                                                <tr>
                                                    <td>Global Education Fund</td>
                                                    <td>Donated</td>
                                                    <td>$5,000 Scholarship</td>
                                                    <td>2023-06-15 10:17</td>
                                                </tr>
                                                <tr>
                                                    <td>Michael Brown</td>
                                                    <td>Updated</td>
                                                    <td>History E-Book</td>
                                                    <td>2023-06-14 16:05</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="card-footer bg-light text-center">
                                    <a href="#" class="btn btn-sm btn-primary">View All Activity</a>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <div class="card mb-4">
                                <div class="card-header bg-light">
                                    <h6 class="m-0 font-weight-bold">User Statistics</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-4">
                                        <h6 class="small font-weight-bold">Schools <span class="float-end">35%</span></h6>
                                        <div class="progress" style="height: 12px;">
                                            <div class="progress-bar bg-success" role="progressbar" style="width: 35%" aria-valuenow="35" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                    </div>
                                    <div class="mb-4">
                                        <h6 class="small font-weight-bold">Donors <span class="float-end">20%</span></h6>
                                        <div class="progress" style="height: 12px;">
                                            <div class="progress-bar bg-info" role="progressbar" style="width: 20%" aria-valuenow="20" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                    </div>
                                    <div class="mb-4">
                                        <h6 class="small font-weight-bold">Students <span class="float-end">45%</span></h6>
                                        <div class="progress" style="height: 12px;">
                                            <div class="progress-bar bg-primary" role="progressbar" style="width: 45%" aria-valuenow="45" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-header bg-light border-top">
                                    <h6 class="m-0 font-weight-bold">Resource Types</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-4">
                                        <h6 class="small font-weight-bold">Textbooks <span class="float-end">30%</span></h6>
                                        <div class="progress" style="height: 12px;">
                                            <div class="progress-bar bg-danger" role="progressbar" style="width: 30%" aria-valuenow="30" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                    </div>
                                    <div class="mb-4">
                                        <h6 class="small font-weight-bold">E-Books <span class="float-end">25%</span></h6>
                                        <div class="progress" style="height: 12px;">
                                            <div class="progress-bar bg-warning" role="progressbar" style="width: 25%" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                    </div>
                                    <div class="mb-4">
                                        <h6 class="small font-weight-bold">Videos <span class="float-end">15%</span></h6>
                                        <div class="progress" style="height: 12px;">
                                            <div class="progress-bar bg-success" role="progressbar" style="width: 15%" aria-valuenow="15" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                    </div>
                                    <div class="mb-4">
                                        <h6 class="small font-weight-bold">Other <span class="float-end">30%</span></h6>
                                        <div class="progress" style="height: 12px;">
                                            <div class="progress-bar bg-info" role="progressbar" style="width: 30%" aria-valuenow="30" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</body>
</html>