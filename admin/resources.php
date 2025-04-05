<?php
// This is a static demonstration file - no actual authentication logic
$active_tab = 'resources';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resources Management - EduShare Admin</title>
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
        
        .resource-icon {
            font-size: 1.5rem;
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
                        <h1 class="h2">Educational Resources</h1>
                        <div class="btn-toolbar mb-2 mb-md-0">
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addResourceModal">
                                <i class="fas fa-plus me-1"></i> Add New Resource
                            </button>
                        </div>
                    </div>

                    <!-- Resource Filters -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-body">
                                    <form class="row g-3">
                                        <div class="col-md-3">
                                            <label for="resourceCategory" class="form-label">Category</label>
                                            <select class="form-select" id="resourceCategory">
                                                <option value="">All Categories</option>
                                                <option value="textbook">Textbook</option>
                                                <option value="ebook">E-Book</option>
                                                <option value="video">Video</option>
                                                <option value="audio">Audio</option>
                                                <option value="presentation">Presentation</option>
                                                <option value="worksheet">Worksheet</option>
                                                <option value="scholarship">Scholarship</option>
                                                <option value="other">Other</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label for="targetAudience" class="form-label">Target Audience</label>
                                            <select class="form-select" id="targetAudience">
                                                <option value="">All Audiences</option>
                                                <option value="elementary">Elementary School</option>
                                                <option value="middle">Middle School</option>
                                                <option value="high">High School</option>
                                                <option value="college">College/University</option>
                                                <option value="teacher">Teachers</option>
                                                <option value="all">All Levels</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="searchResource" class="form-label">Search</label>
                                            <input type="text" class="form-control" id="searchResource" placeholder="Title, description, or uploader">
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

                    <!-- Resources Table -->
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h6 class="m-0 font-weight-bold">Educational Resources</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="resourcesTable" class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Title</th>
                                            <th>Category</th>
                                            <th>Type</th>
                                            <th>Target Audience</th>
                                            <th>Uploaded By</th>
                                            <th>Upload Date</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>1</td>
                                            <td>
                                                <i class="fas fa-book resource-icon text-primary me-2"></i>
                                                Mathematics Textbook Grade 5
                                            </td>
                                            <td>Textbook</td>
                                            <td>File (PDF)</td>
                                            <td>Elementary School</td>
                                            <td>John Smith (School)</td>
                                            <td>2023-05-15</td>
                                            <td class="table-actions">
                                                <button class="btn btn-sm btn-info" data-bs-toggle="tooltip" title="View"><i class="fas fa-eye"></i></button>
                                                <button class="btn btn-sm btn-warning" data-bs-toggle="tooltip" title="Edit"><i class="fas fa-edit"></i></button>
                                                <button class="btn btn-sm btn-danger" data-bs-toggle="tooltip" title="Delete"><i class="fas fa-trash"></i></button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>2</td>
                                            <td>
                                                <i class="fas fa-file-video resource-icon text-danger me-2"></i>
                                                Introduction to Chemistry
                                            </td>
                                            <td>Video</td>
                                            <td>External Link</td>
                                            <td>High School</td>
                                            <td>Sarah Johnson (Donor)</td>
                                            <td>2023-05-18</td>
                                            <td class="table-actions">
                                                <button class="btn btn-sm btn-info" data-bs-toggle="tooltip" title="View"><i class="fas fa-eye"></i></button>
                                                <button class="btn btn-sm btn-warning" data-bs-toggle="tooltip" title="Edit"><i class="fas fa-edit"></i></button>
                                                <button class="btn btn-sm btn-danger" data-bs-toggle="tooltip" title="Delete"><i class="fas fa-trash"></i></button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>3</td>
                                            <td>
                                                <i class="fas fa-file-alt resource-icon text-success me-2"></i>
                                                English Grammar Worksheets
                                            </td>
                                            <td>Worksheet</td>
                                            <td>File (PDF)</td>
                                            <td>Middle School</td>
                                            <td>Emily Davis (School)</td>
                                            <td>2023-05-20</td>
                                            <td class="table-actions">
                                                <button class="btn btn-sm btn-info" data-bs-toggle="tooltip" title="View"><i class="fas fa-eye"></i></button>
                                                <button class="btn btn-sm btn-warning" data-bs-toggle="tooltip" title="Edit"><i class="fas fa-edit"></i></button>
                                                <button class="btn btn-sm btn-danger" data-bs-toggle="tooltip" title="Delete"><i class="fas fa-trash"></i></button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>4</td>
                                            <td>
                                                <i class="fas fa-graduation-cap resource-icon text-warning me-2"></i>
                                                STEM Scholarship Opportunity
                                            </td>
                                            <td>Scholarship</td>
                                            <td>External Link</td>
                                            <td>College/University</td>
                                            <td>Robert Wilson (Donor)</td>
                                            <td>2023-05-22</td>
                                            <td class="table-actions">
                                                <button class="btn btn-sm btn-info" data-bs-toggle="tooltip" title="View"><i class="fas fa-eye"></i></button>
                                                <button class="btn btn-sm btn-warning" data-bs-toggle="tooltip" title="Edit"><i class="fas fa-edit"></i></button>
                                                <button class="btn btn-sm btn-danger" data-bs-toggle="tooltip" title="Delete"><i class="fas fa-trash"></i></button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>5</td>
                                            <td>
                                                <i class="fas fa-headphones resource-icon text-info me-2"></i>
                                                Spanish Language Audio Lessons
                                            </td>
                                            <td>Audio</td>
                                            <td>File (MP3)</td>
                                            <td>All Levels</td>
                                            <td>Michael Brown (Student)</td>
                                            <td>2023-05-25</td>
                                            <td class="table-actions">
                                                <button class="btn btn-sm btn-info" data-bs-toggle="tooltip" title="View"><i class="fas fa-eye"></i></button>
                                                <button class="btn btn-sm btn-warning" data-bs-toggle="tooltip" title="Edit"><i class="fas fa-edit"></i></button>
                                                <button class="btn btn-sm btn-danger" data-bs-toggle="tooltip" title="Delete"><i class="fas fa-trash"></i></button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>6</td>
                                            <td>
                                                <i class="fas fa-file-powerpoint resource-icon text-danger me-2"></i>
                                                History of Ancient Civilizations
                                            </td>
                                            <td>Presentation</td>
                                            <td>File (PPTX)</td>
                                            <td>High School</td>
                                            <td>Jennifer Lee (Student)</td>
                                            <td>2023-05-28</td>
                                            <td class="table-actions">
                                                <button class="btn btn-sm btn-info" data-bs-toggle="tooltip" title="View"><i class="fas fa-eye"></i></button>
                                                <button class="btn btn-sm btn-warning" data-bs-toggle="tooltip" title="Edit"><i class="fas fa-edit"></i></button>
                                                <button class="btn btn-sm btn-danger" data-bs-toggle="tooltip" title="Delete"><i class="fas fa-trash"></i></button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>7</td>
                                            <td>
                                                <i class="fas fa-book-open resource-icon text-primary me-2"></i>
                                                Digital Literacy E-Book
                                            </td>
                                            <td>E-Book</td>
                                            <td>File (PDF)</td>
                                            <td>Teachers</td>
                                            <td>Admin User (Admin)</td>
                                            <td>2023-06-01</td>
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

    <!-- Add Resource Modal -->
    <div class="modal fade" id="addResourceModal" tabindex="-1" aria-labelledby="addResourceModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addResourceModalLabel">Add New Resource</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="mb-3">
                            <label for="resourceTitle" class="form-label">Title</label>
                            <input type="text" class="form-control  class="form-label">Title</label>
                            <input type="text" class="form-control" id="resourceTitle" required>
                        </div>
                        <div class="mb-3">
                            <label for="resourceDescription" class="form-label">Description</label>
                            <textarea class="form-control" id="resourceDescription" rows="3" required></textarea>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="resourceCategory" class="form-label">Category</label>
                                <select class="form-select" id="resourceCategory" required>
                                    <option value="" selected disabled>Select category</option>
                                    <option value="textbook">Textbook</option>
                                    <option value="ebook">E-Book</option>
                                    <option value="video">Video</option>
                                    <option value="audio">Audio</option>
                                    <option value="presentation">Presentation</option>
                                    <option value="worksheet">Worksheet</option>
                                    <option value="scholarship">Scholarship</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="targetAudienceSelect" class="form-label">Target Audience</label>
                                <select class="form-select" id="targetAudienceSelect" required>
                                    <option value="" selected disabled>Select target audience</option>
                                    <option value="elementary">Elementary School</option>
                                    <option value="middle">Middle School</option>
                                    <option value="high">High School</option>
                                    <option value="college">College/University</option>
                                    <option value="teacher">Teachers</option>
                                    <option value="all">All Levels</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Resource Type</label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="resourceType" id="typeFile" value="file" checked>
                                <label class="form-check-label" for="typeFile">
                                    Upload File
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="resourceType" id="typeLink" value="link">
                                <label class="form-check-label" for="typeLink">
                                    External Link
                                </label>
                            </div>
                        </div>
                        <div id="fileUploadSection" class="mb-3">
                            <label for="resourceFile" class="form-label">Upload File (Max 10MB)</label>
                            <input class="form-control" type="file" id="resourceFile">
                            <small class="text-muted">Allowed file types: PDF, DOC, DOCX, PPT, PPTX, XLS, XLSX, JPG, JPEG, PNG, MP4, MP3</small>
                        </div>
                        <div id="linkSection" class="mb-3" style="display: none;">
                            <label for="externalLink" class="form-label">External Link</label>
                            <input type="url" class="form-control" id="externalLink" placeholder="https://example.com/resource">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary">Add Resource</button>
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
            $('#resourcesTable').DataTable({
                responsive: true,
                lengthMenu: [10, 25, 50, 100],
                pageLength: 10
            });
            
            // Initialize tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            });
            
            // Toggle between file upload and external link
            $('#typeFile').change(function() {
                if ($(this).is(':checked')) {
                    $('#fileUploadSection').show();
                    $('#linkSection').hide();
                }
            });
            
            $('#typeLink').change(function() {
                if ($(this).is(':checked')) {
                    $('#fileUploadSection').hide();
                    $('#linkSection').show();
                }
            });
        });
    </script>
</body>
</html>