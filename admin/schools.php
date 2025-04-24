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

$active_tab = 'schools';
$message = '';
$error = '';

// Handle CRUD operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $name = $_POST['name'];
                $level = $_POST['level'];
                $region = $_POST['region'];
                $address = $_POST['address'];
                $contact_person = $_POST['contact_person'];
                $email = $_POST['email'];
                $phone = $_POST['phone'];
                
                try {
                    if (addSchool($name, $level, $region, $address, $contact_person, $email, $phone)) {
                        $message = 'School added successfully!';
                    } else {
                        $error = 'Failed to add school. Please try again.';
                    }
                } catch (mysqli_sql_exception $e) {
                    if ($e->getCode() == 1062) { // MySQL error code for duplicate entry
                        $error = 'A school with this email already exists in the database.';
                    } else {
                        $error = 'An error occurred while adding the school: ' . $e->getMessage();
                    }
                }
                break;
                
            case 'edit':
                $id = $_POST['id'];
                $name = $_POST['name'];
                $level = $_POST['level'];
                $region = $_POST['region'];
                $address = $_POST['address'];
                $contact_person = $_POST['contact_person'];
                $email = $_POST['email'];
                $phone = $_POST['phone'];
                
                try {
                    if (updateSchool($id, $name, $level, $region, $address, $contact_person, $email, $phone)) {
                        $message = 'School updated successfully!';
                    } else {
                        $error = 'Failed to update school. Please try again.';
                    }
                } catch (mysqli_sql_exception $e) {
                    if ($e->getCode() == 1062) {
                        $error = 'A school with this email already exists in the database.';
                    } else {
                        $error = 'An error occurred while updating the school: ' . $e->getMessage();
                    }
                }
                break;
                
            case 'delete':
                $id = $_POST['id'];
                if (deleteSchool($id)) {
                    $message = 'School deleted successfully!';
                } else {
                    $error = 'Failed to delete school. Please try again.';
                }
                break;
        }
    }
}

// Get all schools for display
$schools = getAllSchools();

// Handle filtering
$filtered_schools = $schools;
if (isset($_GET['region']) && !empty($_GET['region'])) {
    $filtered_schools = array_filter($filtered_schools, function($school) {
        return $school['region'] === $_GET['region'];
    });
}
if (isset($_GET['level']) && !empty($_GET['level'])) {
    $filtered_schools = array_filter($filtered_schools, function($school) {
        return $school['level'] === $_GET['level'];
    });
}
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = strtolower($_GET['search']);
    $filtered_schools = array_filter($filtered_schools, function($school) use ($search) {
        return strpos(strtolower($school['name']), $search) !== false ||
               strpos(strtolower($school['address']), $search) !== false ||
               strpos(strtolower($school['contact_person']), $search) !== false;
    });
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schools Management - EduShare Admin</title>
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
        
        .table-actions .btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }
        
        .school-card {
            transition: transform 0.3s;
        }
        
        .school-card:hover {
            transform: translateY(-5px);
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary-custom">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
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

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="content-wrapper">
                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                        <h1 class="h2">Schools Management</h1>
                        <div class="btn-toolbar mb-2 mb-md-0">
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSchoolModal">
                                <i class="fas fa-plus me-1"></i> Add New School
                            </button>
                        </div>
                    </div>

                    <?php if ($message): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?php echo $message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php echo $error; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <!-- School Filters -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-body">
                                    <form class="row g-3" method="GET">
                                        <div class="col-md-3">
                                            <label for="regionFilter" class="form-label">Region</label>
                                            <select class="form-select" id="regionFilter" name="region">
                                                <option value="">All Regions</option>
                                                <option value="north">North</option>
                                                <option value="south">South</option>
                                                <option value="east">East</option>
                                                <option value="west">West</option>
                                                <option value="central">Central</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label for="levelFilter" class="form-label">School Level</label>
                                            <select class="form-select" id="levelFilter" name="level">
                                                <option value="">All Levels</option>
                                                <option value="elementary">Elementary</option>
                                                <option value="middle">Middle School</option>
                                                <option value="high">High School</option>
                                                <option value="college">College/University</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="searchFilter" class="form-label">Search</label>
                                            <input type="text" class="form-control" id="searchFilter" name="search" placeholder="School name, location, or contact">
                                        </div>
                                        <div class="col-md-2 d-flex align-items-end">
                                            <button type="submit" class="btn btn-primary w-100">
                                                <i class="fas fa-filter me-1"></i> Filter
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- View Toggle Buttons -->
                    <div class="mb-3">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-outline-primary active view-toggle" data-view="table">
                                <i class="fas fa-table me-1"></i> Table View
                            </button>
                            <button type="button" class="btn btn-outline-primary view-toggle" data-view="card">
                                <i class="fas fa-th-large me-1"></i> Card View
                            </button>
                        </div>
                    </div>

                    <!-- Schools Table View -->
                    <div id="tableView" class="card mb-4">
                        <div class="card-header bg-light">
                            <h6 class="m-0 font-weight-bold">Registered Schools</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="schoolsTable" class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>School Name</th>
                                            <th>Level</th>
                                            <th>Region</th>
                                            <th>Address</th>
                                            <th>Contact Person</th>
                                            <th>Email</th>
                                            <th>Phone</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($filtered_schools as $school): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($school['id']); ?></td>
                                                <td><?php echo htmlspecialchars($school['name']); ?></td>
                                                <td><?php echo htmlspecialchars($school['level']); ?></td>
                                                <td><?php echo htmlspecialchars($school['region']); ?></td>
                                                <td><?php echo htmlspecialchars($school['address']); ?></td>
                                                <td><?php echo htmlspecialchars($school['contact_person']); ?></td>
                                                <td><?php echo htmlspecialchars($school['email']); ?></td>
                                                <td><?php echo htmlspecialchars($school['phone']); ?></td>
                                                <td class="table-actions">
                                                    <button type="button" class="btn btn-sm btn-primary edit-school" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#editSchoolModal"
                                                            data-id="<?php echo $school['id']; ?>"
                                                            data-name="<?php echo htmlspecialchars($school['name']); ?>"
                                                            data-level="<?php echo htmlspecialchars($school['level']); ?>"
                                                            data-region="<?php echo htmlspecialchars($school['region']); ?>"
                                                            data-address="<?php echo htmlspecialchars($school['address']); ?>"
                                                            data-contact="<?php echo htmlspecialchars($school['contact_person']); ?>"
                                                            data-email="<?php echo htmlspecialchars($school['email']); ?>"
                                                            data-phone="<?php echo htmlspecialchars($school['phone']); ?>">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-danger delete-school" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#deleteSchoolModal"
                                                            data-id="<?php echo $school['id']; ?>"
                                                            data-name="<?php echo htmlspecialchars($school['name']); ?>">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Schools Card View -->
                    <div id="cardView" class="row" style="display: none;">
                        <?php foreach ($filtered_schools as $school): ?>
                            <div class="col-md-4 mb-4">
                                <div class="card school-card h-100">
                                    <div class="card-header bg-primary-custom text-white">
                                        <h5 class="mb-0"><?php echo htmlspecialchars($school['name']); ?></h5>
                                    </div>
                                    <div class="card-body">
                                        <p><strong><i class="fas fa-map-marker-alt me-2"></i> Location:</strong> <?php echo htmlspecialchars($school['region']); ?></p>
                                        <p><strong><i class="fas fa-graduation-cap me-2"></i> Level:</strong> <?php echo htmlspecialchars($school['level']); ?></p>
                                        <p><strong><i class="fas fa-user me-2"></i> Contact:</strong> <?php echo htmlspecialchars($school['contact_person']); ?></p>
                                        <p><strong><i class="fas fa-envelope me-2"></i> Email:</strong> <?php echo htmlspecialchars($school['email']); ?></p>
                                        <p><strong><i class="fas fa-phone me-2"></i> Phone:</strong> <?php echo htmlspecialchars($school['phone']); ?></p>
                                        <p><strong><i class="fas fa-map me-2"></i> Address:</strong> <?php echo htmlspecialchars($school['address']); ?></p>
                                    </div>
                                    <div class="card-footer">
                                        <div class="d-flex justify-content-between">
                                            <button type="button" class="btn btn-sm btn-primary edit-school" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#editSchoolModal"
                                                    data-id="<?php echo $school['id']; ?>"
                                                    data-name="<?php echo htmlspecialchars($school['name']); ?>"
                                                    data-level="<?php echo htmlspecialchars($school['level']); ?>"
                                                    data-region="<?php echo htmlspecialchars($school['region']); ?>"
                                                    data-address="<?php echo htmlspecialchars($school['address']); ?>"
                                                    data-contact="<?php echo htmlspecialchars($school['contact_person']); ?>"
                                                    data-email="<?php echo htmlspecialchars($school['email']); ?>"
                                                    data-phone="<?php echo htmlspecialchars($school['phone']); ?>">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger delete-school" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#deleteSchoolModal"
                                                    data-id="<?php echo $school['id']; ?>"
                                                    data-name="<?php echo htmlspecialchars($school['name']); ?>">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Add School Modal -->
    <div class="modal fade" id="addSchoolModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New School</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">
                        <div class="mb-3">
                            <label for="name" class="form-label">School Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="level" class="form-label">School Level</label>
                            <select class="form-select" id="level" name="level" required>
                                <option value="elementary">Elementary</option>
                                <option value="middle">Middle School</option>
                                <option value="high">High School</option>
                                <option value="college">College/University</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="region" class="form-label">Region</label>
                            <select class="form-select" id="region" name="region" required>
                                <option value="north">North</option>
                                <option value="south">South</option>
                                <option value="east">East</option>
                                <option value="west">West</option>
                                <option value="central">Central</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="address" class="form-label">Address</label>
                            <textarea class="form-control" id="address" name="address" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="contact_person" class="form-label">Contact Person</label>
                            <input type="text" class="form-control" id="contact_person" name="contact_person" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone</label>
                            <input type="tel" class="form-control" id="phone" name="phone" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add School</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit School Modal -->
    <div class="modal fade" id="editSchoolModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit School</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="id" id="edit_id">
                        <div class="mb-3">
                            <label for="edit_name" class="form-label">School Name</label>
                            <input type="text" class="form-control" id="edit_name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_level" class="form-label">School Level</label>
                            <select class="form-select" id="edit_level" name="level" required>
                                <option value="elementary">Elementary</option>
                                <option value="middle">Middle School</option>
                                <option value="high">High School</option>
                                <option value="college">College/University</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="edit_region" class="form-label">Region</label>
                            <select class="form-select" id="edit_region" name="region" required>
                                <option value="north">North</option>
                                <option value="south">South</option>
                                <option value="east">East</option>
                                <option value="west">West</option>
                                <option value="central">Central</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="edit_address" class="form-label">Address</label>
                            <textarea class="form-control" id="edit_address" name="address" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="edit_contact_person" class="form-label">Contact Person</label>
                            <input type="text" class="form-control" id="edit_contact_person" name="contact_person" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="edit_email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_phone" class="form-label">Phone</label>
                            <input type="tel" class="form-control" id="edit_phone" name="phone" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update School</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete School Modal -->
    <div class="modal fade" id="deleteSchoolModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Delete School</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" id="delete_id">
                        <p>Are you sure you want to delete <span id="delete_school_name" class="fw-bold"></span>?</p>
                        <p class="text-danger">This action cannot be undone.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete School</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize DataTable
            var table = $('#schoolsTable').DataTable({
                "paging": true,
                "lengthChange": false,
                "searching": false,
                "ordering": true,
                "info": true,
                "autoWidth": false,
                "responsive": true
            });

            // Handle view toggle buttons
            $('.view-toggle').click(function() {
                var view = $(this).data('view');
                if (view === 'table') {
                    $('#tableView').show();
                    $('#cardView').hide();
                    $('.view-toggle').removeClass('active');
                    $(this).addClass('active');
                } else if (view === 'card') {
                    $('#tableView').hide();
                    $('#cardView').show();
                    $('.view-toggle').removeClass('active');
                    $(this).addClass('active');
                }
            });

            // Handle filter form submission
            $('#filterForm').on('submit', function(e) {
                e.preventDefault();
                var level = $('#levelFilter').val();
                var region = $('#regionFilter').val();
                var search = $('#searchFilter').val().toLowerCase();

                // Filter table view
                table.column(2).search(level).draw();
                table.column(3).search(region).draw();
                table.search(search).draw();

                // Filter card view
                $('.school-card').each(function() {
                    var cardLevel = $(this).find('p:contains("Level:")').text().toLowerCase().replace('level:', '').trim();
                    var cardRegion = $(this).find('p:contains("Location:")').text().toLowerCase().replace('location:', '').trim();
                    var cardName = $(this).find('.card-header h5').text().toLowerCase();
                    var cardContact = $(this).find('p:contains("Contact:")').text().toLowerCase().replace('contact:', '').trim();
                    var cardEmail = $(this).find('p:contains("Email:")').text().toLowerCase().replace('email:', '').trim();

                    var levelMatch = level === '' || cardLevel.includes(level);
                    var regionMatch = region === '' || cardRegion.includes(region);
                    var searchMatch = search === '' || 
                        cardName.includes(search) || 
                        cardContact.includes(search) || 
                        cardEmail.includes(search);

                    $(this).closest('.col-md-4').toggle(levelMatch && regionMatch && searchMatch);
                });
            });

            // Handle reset button
            $('#resetFilters').click(function() {
                $('#filterForm')[0].reset();
                table.search('').columns().search('').draw();
                $('.school-card').closest('.col-md-4').show();
            });

            // Handle edit button click
            $('.edit-school').click(function() {
                var id = $(this).data('id');
                var name = $(this).data('name');
                var level = $(this).data('level');
                var region = $(this).data('region');
                var address = $(this).data('address');
                var contact = $(this).data('contact');
                var email = $(this).data('email');
                var phone = $(this).data('phone');

                $('#edit_id').val(id);
                $('#edit_name').val(name);
                $('#edit_level').val(level);
                $('#edit_region').val(region);
                $('#edit_address').val(address);
                $('#edit_contact_person').val(contact);
                $('#edit_email').val(email);
                $('#edit_phone').val(phone);
            });

            // Handle delete button click
            $('.delete-school').click(function() {
                var id = $(this).data('id');
                var name = $(this).data('name');
                $('#delete_id').val(id);
                $('#delete_school_name').text(name);
            });
        });
    </script>
</body>
</html>