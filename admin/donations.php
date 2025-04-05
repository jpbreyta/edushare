<?php
$active_tab = 'donations';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donations Management - EduShare Admin</title>
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
        
        .donation-card {
            transition: transform 0.3s;
        }
        
        .donation-card:hover {
            transform: translateY(-5px);
        }
        
        .donation-amount {
            font-size: 1.5rem;
            font-weight: bold;
            color: var(--primary-color);
        }
        
        .donation-stats {
            background-color: var(--primary-light);
            border-radius: 0.5rem;
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
                        <h1 class="h2">Donations Management</h1>
                        <div class="btn-toolbar mb-2 mb-md-0">
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDonationModal">
                                <i class="fas fa-plus me-1"></i> Record New Donation
                            </button>
                        </div>
                    </div>

                    <!-- Donation Statistics -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header bg-primary-custom text-white">
                                    <h5 class="mb-0">Donation Statistics</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3 mb-3">
                                            <div class="p-3 text-center donation-stats">
                                                <h3 class="donation-amount">$24,568</h3>
                                                <p class="mb-0">Total Donations</p>
                                            </div>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <div class="p-3 text-center donation-stats">
                                                <h3 class="donation-amount">42</h3>
                                                <p class="mb-0">Total Donors</p>
                                            </div>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <div class="p-3 text-center donation-stats">
                                                <h3 class="donation-amount">$5,000</h3>
                                                <p class="mb-0">Largest Donation</p>
                                            </div>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <div class="p-3 text-center donation-stats">
                                                <h3 class="donation-amount">$584</h3>
                                                <p class="mb-0">Average Donation</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Donation Filters -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-body">
                                    <form class="row g-3">
                                        <div class="col-md-3">
                                            <label for="donationType" class="form-label">Donation Type</label>
                                            <select class="form-select" id="donationType">
                                                <option value="">All Types</option>
                                                <option value="monetary">Monetary</option>
                                                <option value="resource">Resource</option>
                                                <option value="scholarship">Scholarship</option>
                                                <option value="equipment">Equipment</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label for="donationDate" class="form-label">Date Range</label>
                                            <select class="form-select" id="donationDate">
                                                <option value="">All Time</option>
                                                <option value="today">Today</option>
                                                <option value="week">This Week</option>
                                                <option value="month">This Month</option>
                                                <option value="year">This Year</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="searchDonation" class="form-label">Search</label>
                                            <input type="text" class="form-control" id="searchDonation" placeholder="Donor name, school, or purpose">
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

                    <!-- Donations Table -->
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h6 class="m-0 font-weight-bold">Donation Records</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="donationsTable" class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Donor</th>
                                            <th>Type</th>
                                            <th>Amount/Value</th>
                                            <th>Recipient School</th>
                                            <th>Purpose</th>
                                            <th>Date</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>1</td>
                                            <td>Global Education Fund</td>
                                            <td>Monetary</td>
                                            <td>$5,000</td>
                                            <td>Westside Elementary School</td>
                                            <td>Library Renovation</td>
                                            <td>2023-06-01</td>
                                            <td><span class="badge bg-success">Completed</span></td>
                                            <td class="table-actions">
                                                <button class="btn btn-sm btn-info" data-bs-toggle="tooltip" title="View"><i class="fas fa-eye"></i></button>
                                                <button class="btn btn-sm btn-warning" data-bs-toggle="tooltip" title="Edit"><i class="fas fa-edit"></i></button>
                                                <button class="btn btn-sm btn-danger" data-bs-toggle="tooltip" title="Delete"><i class="fas fa-trash"></i></button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>2</td>
                                            <td>Sarah Johnson</td>
                                            <td>Resource</td>
                                            <td>$1,200</td>
                                            <td>Central High School</td>
                                            <td>Science Lab Equipment</td>
                                            <td>2023-06-05</td>
                                            <td><span class="badge bg-success">Completed</span></td>
                                            <td class="table-actions">
                                                <button class="btn btn-sm btn-info" data-bs-toggle="tooltip" title="View"><i class="fas fa-eye"></i></button>
                                                <button class="btn btn-sm btn-warning" data-bs-toggle="tooltip" title="Edit"><i class="fas fa-edit"></i></button>
                                                <button class="btn btn-sm btn-danger" data-bs-toggle="tooltip" title="Delete"><i class="fas fa-trash"></i></button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>3</td>
                                            <td>Wilson Foundation</td>
                                            <td>Scholarship</td>
                                            <td>$2,500</td>
                                            <td>Riverside University</td>
                                            <td>STEM Scholarship</td>
                                            <td>2023-06-08</td>
                                            <td><span class="badge bg-warning">Pending</span></td>
                                            <td class="table-actions">
                                                <button class="btn btn-sm btn-info" data-bs-toggle="tooltip" title="View"><i class="fas fa-eye"></i></button>
                                                <button class="btn btn-sm btn-warning" data-bs-toggle="tooltip" title="Edit"><i class="fas fa-edit"></i></button>
                                                <button class="btn btn-sm btn-danger" data-bs-toggle="tooltip" title="Delete"><i class="fas fa-trash"></i></button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>4</td>
                                            <td>Tech for Education Inc.</td>
                                            <td>Equipment</td>
                                            <td>$3,500</td>
                                            <td>Oakridge Academy</td>
                                            <td>Computer Lab</td>
                                            <td>2023-06-10</td>
                                            <td><span class="badge bg-info">In Progress</span></td>
                                            <td class="table-actions">
                                                <button class="btn btn-sm btn-info" data-bs-toggle="tooltip" title="View"><i class="fas fa-eye"></i></button>
                                                <button class="btn btn-sm btn-warning" data-bs-toggle="tooltip" title="Edit"><i class="fas fa-edit"></i></button>
                                                <button class="btn btn-sm btn-danger" data-bs-toggle="tooltip" title="Delete"><i class="fas fa-trash"></i></button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>5</td>
                                            <td>John Smith</td>
                                            <td>Monetary</td>
                                            <td>$500</td>
                                            <td>Sunshine Elementary</td>
                                            <td>Art Supplies</td>
                                            <td>2023-06-12</td>
                                            <td><span class="badge bg-success">Completed</span></td>
                                            <td class="table-actions">
                                                <button class="btn btn-sm btn-info" data-bs-toggle="tooltip" title="View"><i class="fas fa-eye"></i></button>
                                                <button class="btn btn-sm btn-warning" data-bs-toggle="tooltip" title="Edit"><i class="fas fa-edit"></i></button>
                                                <button class="btn btn-sm btn-danger" data-bs-toggle="tooltip" title="Delete"><i class="fas fa-trash"></i></button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>6</td>
                                            <td>Community Education Alliance</td>
                                            <td>Resource</td>
                                            <td>$850</td>
                                            <td>Northside Middle School</td>
                                            <td>Library Books</td>
                                            <td>2023-06-15</td>
                                            <td><span class="badge bg-success">Completed</span></td>
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
                </div>
            </main>
        </div>
    </div>

    <!-- Add Donation Modal -->
    <div class="modal fade" id="addDonationModal" tabindex="-1" aria-labelledby="addDonationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addDonationModalLabel">Record New Donation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="donorName" class="form-label">Donor Name</label>
                                <input type="text" class="form-control" id="donorName" required>
                            </div>
                            <div class="col-md-6">
                                <label for="donorEmail" class="form-label">Donor Email</label>
                                <input type="email" class="form-control" id="donorEmail" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="donationType" class="form-label">Donation Type</label>
                                <select class="form-select" id="donationType" required>
                                    <option value="" selected disabled>Select donation type</option>
                                    <option value="monetary">Monetary</option>
                                    <option value="resource">Resource</option>
                                    <option value="scholarship">Scholarship</option>
                                    <option value="equipment">Equipment</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="donationAmount" class="form-label">Amount/Value</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control" id="donationAmount" required>
                                </div>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="recipientSchool" class="form-label">Recipient School</label>
                                <select class="form-select" id="recipientSchool" required>
                                    <option value="" selected disabled>Select school</option>
                                    <option value="1">Westside Elementary School</option>
                                    <option value="2">Central High School</option>
                                    <option value="3">Oakridge Academy</option>
                                    <option value="4">Riverside University</option>
                                    <option value="5">Northside Middle School</option>
                                    <option value="6">Sunshine Elementary</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="donationDate" class="form-label">Donation Date</label>
                                <input type="date" class="form-control" id="donationDate" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="donationPurpose" class="form-label">Purpose</label>
                            <textarea class="form-control" id="donationPurpose" rows="2" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="donationStatus" class="form-label">Status</label>
                            <select class="form-select" id="donationStatus" required>
                                <option value="pending">Pending</option>
                                <option value="in_progress">In Progress</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="donationNotes" class="form-label">Additional Notes</label>
                            <textarea class="form-control" id="donationNotes" rows="3"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary">Record Donation</button>
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
            $('#donationsTable').DataTable({
                responsive: true,
                lengthMenu: [10, 25, 50, 100],
                pageLength: 10
            });
            
            // Initialize tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            });
        });
    </script>
</body>
</html>