<?php
// This is a static demonstration file - no actual authentication logic
$active_tab = 'schools';
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
                            <i class="fas fa-user-circle me-1"></i> Admin User
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="#"><i class="fas fa-user-cog me-1"></i> Profile</a></li>
                            <li><a class="dropdown-item" href="#"><i class="fas fa-cog me-1"></i> Settings</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="#"><i class="fas fa-sign-out-alt me-1"></i> Logout</a></li>
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

                    <!-- School Filters -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-body">
                                    <form class="row g-3">
                                        <div class="col-md-3">
                                            <label for="schoolRegion" class="form-label">Region</label>
                                            <select class="form-select" id="schoolRegion">
                                                <option value="">All Regions</option>
                                                <option value="north">North</option>
                                                <option value="south">South</option>
                                                <option value="east">East</option>
                                                <option value="west">West</option>
                                                <option value="central">Central</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label for="schoolLevel" class="form-label">School Level</label>
                                            <select class="form-select" id="schoolLevel">
                                                <option value="">All Levels</option>
                                                <option value="elementary">Elementary</option>
                                                <option value="middle">Middle School</option>
                                                <option value="high">High School</option>
                                                <option value="college">College/University</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="searchSchool" class="form-label">Search</label>
                                            <input type="text" class="form-control" id="searchSchool" placeholder="School name, location, or contact">
                                        </div>
                                        <div class="col-md-2 d-flex align-items-end">
                                            <button type="button" class="btn btn-primary w-100">
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
                            <button type="button" class="btn btn-outline-primary active" id="tableViewBtn">
                                <i class="fas fa-table me-1"></i> Table View
                            </button>
                            <button type="button" class="btn btn-outline-primary" id="cardViewBtn">
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
                                            <th>Location</th>
                                            <th>Contact Person</th>
                                            <th>Email</th>
                                            <th>Phone</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>1</td>
                                            <td>Westside Elementary School</td>
                                            <td>Elementary</td>
                                            <td>West Region, City A</td>
                                            <td>John Smith</td>
                                            <td>john.smith@westside.edu</td>
                                            <td>+1 (123) 456-7890</td>
                                            <td class="table-actions">
                                                <button class="btn btn-sm btn-info" data-bs-toggle="tooltip" title="View"><i class="fas fa-eye"></i></button>
                                                <button class="btn btn-sm btn-warning" data-bs-toggle="tooltip" title="Edit"><i class="fas fa-edit"></i></button>
                                                <button class="btn btn-sm btn-danger" data-bs-toggle="tooltip" title="Delete"><i class="fas fa-trash"></i></button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>2</td>
                                            <td>Central High School</td>
                                            <td>High School</td>
                                            <td>Central Region, City B</td>
                                            <td>Emily Davis</td>
                                            <td>emily.davis@central.edu</td>
                                            <td>+1 (234) 567-8901</td>
                                            <td class="table-actions">
                                                <button class="btn btn-sm btn-info" data-bs-toggle="tooltip" title="View"><i class="fas fa-eye"></i></button>
                                                <button class="btn btn-sm btn-warning" data-bs-toggle="tooltip" title="Edit"><i class="fas fa-edit"></i></button>
                                                <button class="btn btn-sm btn-danger" data-bs-toggle="tooltip" title="Delete"><i class="fas fa-trash"></i></button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>3</td>
                                            <td>Oakridge Academy</td>
                                            <td>Middle School</td>
                                            <td>East Region, City C</td>
                                            <td>Michael Brown</td>
                                            <td>michael.brown@oakridge.edu</td>
                                            <td>+1 (345) 678-9012</td>
                                            <td class="table-actions">
                                                <button class="btn btn-sm btn-info" data-bs-toggle="tooltip" title="View"><i class="fas fa-eye"></i></button>
                                                <button class="btn btn-sm btn-warning" data-bs-toggle="tooltip" title="Edit"><i class="fas fa-edit"></i></button>
                                                <button class="btn btn-sm btn-danger" data-bs-toggle="tooltip" title="Delete"><i class="fas fa-trash"></i></button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>4</td>
                                            <td>Riverside University</td>
                                            <td>College/University</td>
                                            <td>South Region, City D</td>
                                            <td>Jennifer Lee</td>
                                            <td>jennifer.lee@riverside.edu</td>
                                            <td>+1 (456) 789-0123</td>
                                            <td class="table-actions">
                                                <button class="btn btn-sm btn-info" data-bs-toggle="tooltip" title="View"><i class="fas fa-eye"></i></button>
                                                <button class="btn btn-sm btn-warning" data-bs-toggle="tooltip" title="Edit"><i class="fas fa-edit"></i></button>
                                                <button class="btn btn-sm btn-danger" data-bs-toggle="tooltip" title="Delete"><i class="fas fa-trash"></i></button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>5</td>
                                            <td>Northside Middle School</td>
                                            <td>Middle School</td>
                                            <td>North Region, City E</td>
                                            <td>Robert Wilson</td>
                                            <td>robert.wilson@northside.edu</td>
                                            <td>+1 (567) 890-1234</td>
                                            <td class="table-actions">
                                                <button class="btn btn-sm btn-info" data-bs-toggle="tooltip" title="View"><i class="fas fa-eye"></i></button>
                                                <button class="btn btn-sm btn-warning" data-bs-toggle="tooltip" title="Edit"><i class="fas fa-edit"></i></button>
                                                <button class="btn btn-sm btn-danger" data-bs-toggle="tooltip" title="Delete"><i class="fas fa-trash"></i></button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>6</td>
                                            <td>Sunshine Elementary</td>
                                            <td>Elementary</td>
                                            <td>South Region, City F</td>
                                            <td>Sarah Johnson</td>
                                            <td>sarah.johnson@sunshine.edu</td>
                                            <td>+1 (678) 901-2345</td>
                                            <td class="table-actions">
                                                <button class="btn btn-sm btn-info" data-bs-toggle="tooltip" title="View"><i class="fas fa-eye"></i></button>
                                                <button class="btn btn-sm btn-warning" data-bs-toggle="tooltip" title="Edit"><i class="fas fa-edit"></i></button>
                                                <button class="btn btn-sm btn-danger" data-bs-toggle="tooltip" title="Delete"><i class="fas fa-trash"></i></button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Schools Card View -->
                    <div id="cardView" class="row" style="display: none;">
                        <div class="col-md-4 mb-4">
                            <div class="card school-card h-100">
                                <div class="card-header bg-primary-custom text-white">
                                    <h5 class="mb-0">Westside Elementary School</h5>
                                </div>
                                <div class="card-body">
                                    <p><strong><i class="fas fa-map-marker-alt me-2"></i> Location:</strong> West Region, City A</p>
                                    <p><strong><i class="fas fa-graduation-cap me-2"></i> Level:</strong> Elementary</p>
                                    <p><strong><i class="fas fa-user me-2"></i> Contact:</strong> John Smith</p>
                                    <p><strong><i class="fas fa-envelope me-2"></i> Email:</strong> john.smith@westside.edu</p>
                                    <p><strong><i class="fas fa-phone me-2"></i> Phone:</strong> +1 (123) 456-7890</p>
                                </div>
                                <div class="card-footer">
                                    <div class="d-flex justify-content-between">
                                        <button class="btn btn-sm btn-info"><i class="fas fa-eye me-1"></i> View</button>
                                        <button class="btn btn-sm btn-warning"><i class="fas fa-edit me-1"></i> Edit</button>
                                        <button class="btn btn-sm btn-danger"><i class="fas fa-trash me-1"></i> Delete</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-4">
                            <div class="card school-card h-100">
                                <div class="card-header bg-primary-custom text-white">
                                    <h5 class="mb-0">Central High School</h5>
                                </div>
                                <div class="card-body">
                                    <p><strong><i class="fas fa-map-marker-alt me-2"></i> Location:</strong> Central Region, City B</p>
                                    <p><strong><i class="fas fa-graduation-cap me-2"></i> Level:</strong> High School</p>
                                    <p><strong><i class="fas fa-user me-2"></i> Contact:</strong> Emily Davis</p>
                                    <p><strong><i class="fas fa-envelope me-2"></i> Email:</strong> emily.davis@central.edu</p>
                                    <p><strong><i class="fas fa-phone me-2"></i> Phone:</strong> +1 (234) 567-8901</p>
                                </div>
                                <div class="card-footer">
                                    <div class="d-flex justify-content-between">
                                        <button class="btn btn-sm btn-info"><i class="fas fa-eye me-1"></i> View</button>
                                        <button class="btn btn-sm btn-warning"><i class="fas fa-edit me-1"></i> Edit</button>
                                        <button class="btn btn-sm btn-danger"><i class="fas fa-trash me-1"></i> Delete</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-4">
                            <div class="card school-card h-100">
                                <div class="card-header bg-primary-custom text-white">
                                    <h5 class="mb-0">Oakridge Academy</h5>
                                </div>
                                <div class="card-body">
                                    <p><strong><i class="fas fa-map-marker-alt me-2"></i> Location:</strong> East Region, City C</p>
                                    <p><strong><i class="fas fa-graduation-cap me-2"></i> Level:</strong> Middle School</p>
                                    <p><strong><i class="fas fa-user me-2"></i> Contact:</strong> Michael Brown</p>
                                    <p><strong><i class="fas fa-envelope me-2"></i> Email:</strong> michael.brown@oakridge.edu</p>
                                    <p><strong><i class="fas fa-phone me-2"></i> Phone:</strong> +1 (345) 678-9012</p>
                                </div>
                                <div class="card-footer">
                                    <div class="d-flex justify-content-between">
                                        <button class="btn btn-sm btn-info"><i class="fas fa-eye me-1"></i> View</button>
                                        <button class="btn btn-sm btn-warning"><i class="fas fa-edit me-1"></i> Edit</button>
                                        <button class="btn btn-sm btn-danger"><i class="fas fa-trash me-1"></i> Delete</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-4">
                            <div class="card school-card h-100">
                                <div class="card-header bg-primary-custom text-white">
                                    <h5 class="mb-0">Riverside University</h5>
                                </div>
                                <div class="card-body">
                                    <p><strong><i class="fas fa-map-marker-alt me-2"></i> Location:</strong> South Region, City D</p>
                                    <p><strong><i class="fas fa-graduation-cap me-2"></i> Level:</strong> College/University</p>
                                    <p><strong><i class="fas fa-user me-2"></i> Contact:</strong> Jennifer Lee</p>
                                    <p><strong><i class="fas fa-envelope me-2"></i> Email:</strong> jennifer.lee@riverside.edu</p>
                                    <p><strong><i class="fas fa-phone me-2"></i> Phone:</strong> +1 (456) 789-0123</p>
                                </div>
                                <div class="card-footer">
                                    <div class="d-flex justify-content-between">
                                        <button class="btn btn-sm btn-info"><i class="fas fa-eye me-1"></i> View</button>
                                        <button class="btn btn-sm btn-warning"><i class="fas fa-edit me-1"></i> Edit</button>
                                        <button class="btn btn-sm btn-danger"><i class="fas fa-trash me-1"></i> Delete</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-4">
                            <div class="card school-card h-100">
                                <div class="card-header bg-primary-custom text-white">
                                    <h5 class="mb-0">Northside Middle School</h5>
                                </div>
                                <div class="card-body">
                                    <p><strong><i class="fas fa-map-marker-alt me-2"></i> Location:</strong> North Region, City E</p>
                                    <p><strong><i class="fas fa-graduation-cap me-2"></i> Level:</strong> Middle School</p>
                                    <p><strong><i class="fas fa-user me-2"></i> Contact:</strong> Robert Wilson</p>
                                    <p><strong><i class="fas fa-envelope me-2"></i> Email:</strong> robert.wilson@northside.edu</p>
                                    <p><strong><i class="fas fa-phone me-2"></i> Phone:</strong> +1 (567) 890-1234</p>
                                </div>
                                <div class="card-footer">
                                    <div class="d-flex justify-content-between">
                                        <button class="btn btn-sm btn-info"><i class="fas fa-eye me-1"></i> View</button>
                                        <button class="btn btn-sm btn-warning"><i class="fas fa-edit me-1"></i> Edit</button>
                                        <button class="btn btn-sm btn-danger"><i class="fas fa-trash me-1"></i> Delete</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-4">
                            <div class="card school-card h-100">
                                <div class="card-header bg-primary-custom text-white">
                                    <h5 class="mb-0">Sunshine Elementary</h5>
                                </div>
                                <div class="card-body">
                                    <p><strong><i class="fas fa-map-marker-alt me-2"></i> Location:</strong> South Region, City F</p>
                                    <p><strong><i class="fas fa-graduation-cap me-2"></i> Level:</strong> Elementary</p>
                                    <p><strong><i class="fas fa-user me-2"></i> Contact:</strong> Sarah Johnson</p>
                                    <p><strong><i class="fas fa-envelope me-2"></i> Email:</strong> sarah.johnson@sunshine.edu</p>
                                    <p><strong><i class="fas fa-phone me-2"></i> Phone:</strong> +1 (678) 901-2345</p>
                                </div>
                                <div class="card-footer">
                                    <div class="d-flex justify-content-between">
                                        <button class="btn btn-sm btn-info"><i class="fas fa-eye me-1"></i> View</button>
                                        <button class="btn btn-sm btn-warning"><i class="fas fa-edit me-1"></i> Edit</button>
                                        <button class="btn btn-sm btn-danger"><i class="fas fa-trash me-1"></i> Delete</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Add School Modal -->
    <div class="modal fade" id="addSchoolModal" tabindex="-1" aria-labelledby="addSchoolModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addSchoolModalLabel">Add New School</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="schoolName" class="form-label">School Name</label>
                                <input type="text" class="form-control" id="schoolName" required>
                            </div>
                            <div class="col-md-6">
                                <label for="schoolLevel" class="form-label">School Level</label>
                                <select class="form-select" id="schoolLevel" required>
                                    <option value="" selected disabled>Select school level</option>
                                    <option value="elementary">Elementary</option>
                                    <option value="middle">Middle School</option>
                                    <option value="high">High School</option>
                                    <option value="college">College/University</option>
                                </select>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="schoolRegion" class="form-label">Region</label>
                                <select class="form-select" id="schoolRegion" required>
                                    <option value="" selected disabled>Select region</option>
                                    <option value="north">North</option>
                                    <option value="south">South</option>
                                    <option value="east">East</option>
                                    <option value="west">West</option>
                                    <option value="central">Central</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="schoolCity" class="form-label">City</label>
                                <input type="text" class="form-control" id="schoolCity" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="schoolAddress" class="form-label">Full Address</label>
                            <textarea class="form-control" id="schoolAddress" rows="2" required></textarea>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="contactPerson" class="form-label">Contact Person</label>
                                <input type="text" class="form-control" id="contactPerson" required>
                            </div>
                            <div class="col-md-6">
                                <label for="contactEmail" class="form-label">Email</label>
                                <input type="email" class="form-control" id="contactEmail" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="contactPhone" class="form-label">Phone</label>
                                <input type="tel" class="form-control" id="contactPhone" required>
                            </div>
                            <div class="col-md-6">
                                <label for="schoolWebsite" class="form-label">Website (optional)</label>
                                <input type="url" class="form-control" id="schoolWebsite">
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary">Add School</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // Initialize DataTable
            $('#schoolsTable').DataTable({
                responsive: true,
                lengthMenu: [10, 25, 50, 100],
                pageLength: 10
            });
            
            // Initialize tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            });
            
            // Toggle between table and card view
            $('#tableViewBtn').click(function() {
                $('#tableView').show();
                $('#cardView').hide();
                $(this).addClass('active');
                $('#cardViewBtn').removeClass('active');
            });
            
            $('#cardViewBtn').click(function() {
                $('#tableView').hide();
                $('#cardView').show();
                $(this).addClass('active');
                $('#tableViewBtn').removeClass('active');
            });
        });
    </script>
</body>
</html>