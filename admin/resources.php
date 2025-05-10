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

$active_tab = 'resources';
$message = '';
$error = '';

// Get all resources and categories for display
$resources = getAllResources();
$schools = getAllSchools();
$categories = getAllResourceCategories();

// Handle CRUD operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $title = $_POST['title'];
                $description = $_POST['description'];
                $category = $_POST['category'];
                $resource_type = $_POST['resource_type'];
                $file_path = isset($_POST['file_path']) ? $_POST['file_path'] : null;
                $external_link = isset($_POST['external_link']) ? $_POST['external_link'] : null;
                $target_audience = $_POST['target_audience'];
                $uploaded_by = $_SESSION['user_id'];
                $school_id = !empty($_POST['school_id']) ? $_POST['school_id'] : null;
                $is_visible = isset($_POST['is_visible']) ? 1 : 0;
                
                try {
                    if (addResource($title, $description, $category, $resource_type, $file_path, $external_link, $target_audience, $uploaded_by, $school_id)) {
                        $message = 'Resource added successfully!';
                        header('Location: resources.php?message=' . urlencode($message));
                        exit();
                    } else {
                        $error = 'Failed to add resource. Please try again.';
                    }
                } catch (mysqli_sql_exception $e) {
                    $error = 'An error occurred while adding the resource: ' . $e->getMessage();
                }
                break;
                
            case 'edit':
                $id = $_POST['id'];
                $title = $_POST['title'];
                $description = $_POST['description'];
                $category = $_POST['category'];
                $resource_type = $_POST['resource_type'];
                $file_path = isset($_POST['file_path']) ? $_POST['file_path'] : null;
                $external_link = isset($_POST['external_link']) ? $_POST['external_link'] : null;
                $target_audience = $_POST['target_audience'];
                $school_id = !empty($_POST['school_id']) ? $_POST['school_id'] : null;
                $is_visible = isset($_POST['is_visible']) ? 1 : 0;
                
                try {
                    if (updateResource($id, $title, $description, $category, $resource_type, $file_path, $external_link, $target_audience, $school_id, $is_visible)) {
                        $message = 'Resource updated successfully!';
                        header('Location: resources.php?message=' . urlencode($message));
                        exit();
                    } else {
                        $error = 'Failed to update resource. Please try again.';
                    }
                } catch (mysqli_sql_exception $e) {
                    $error = 'An error occurred while updating the resource: ' . $e->getMessage();
                }
                break;
                
            case 'delete':
                $id = isset($_POST['id']) ? trim($_POST['id']) : '';
                
                if (empty($id)) {
                    $error = 'Invalid resource ID provided.';
                    break;
                }
                
                try {
                    if (deleteResource($id)) {
                        $message = 'Resource deleted successfully!';
                        header('Location: resources.php?message=' . urlencode($message));
                        exit();
                    } else {
                        $error = 'Failed to delete resource. The resource may not exist or has already been deleted.';
                    }
                } catch (mysqli_sql_exception $e) {
                    $error = 'An error occurred while deleting the resource: ' . $e->getMessage();
                    error_log('Error in resource deletion: ' . $e->getMessage());
                }
                break;
        }
    }
}

// Handle filtering
$filtered_resources = $resources;
if (isset($_GET['category']) && !empty($_GET['category'])) {
    $filtered_resources = array_filter($filtered_resources, function($resource) {
        return $resource['category_id'] == $_GET['category'];
    });
}
if (isset($_GET['type']) && !empty($_GET['type'])) {
    $filtered_resources = array_filter($filtered_resources, function($resource) {
        return $resource['resource_type'] === $_GET['type'];
    });
}
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = strtolower($_GET['search']);
    $filtered_resources = array_filter($filtered_resources, function($resource) use ($search) {
        return strpos(strtolower($resource['title']), $search) !== false ||
               strpos(strtolower($resource['description']), $search) !== false;
    });
}

// Display message from redirect
if (isset($_GET['message'])) {
    $message = $_GET['message'];
}
?>
<?php include 'includes/header.php'; ?>

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
                        <h1 class="h2">Resources Management</h1>
                        <div class="btn-toolbar mb-2 mb-md-0">
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addResourceModal">
                                <i class="fas fa-plus me-1"></i> Add New Resource
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

                    <!-- Resource Filters -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-body">
                                    <form class="row g-3">
                                        <div class="col-md-3">
                                            <label for="resourceCategory" class="form-label">Category</label>
                                            <select class="form-select" id="resourceCategory" name="category">
                                                <option value="">All Categories</option>
                                                <option value="textbook">Textbook</option>
                                                <option value="ebook">E-Book</option>
                                                <option value="presentation">Presentation</option>
                                                <option value="worksheet">Worksheet</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label for="resourceType" class="form-label">Type</label>
                                            <select class="form-select" id="resourceType" name="type">
                                                <option value="">All Types</option>
                                                <option value="file">File</option>
                                                <option value="link">Link</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="searchResource" class="form-label">Search</label>
                                            <input type="text" class="form-control" id="searchResource" name="search" placeholder="Title or description">
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

                    <!-- Resources Table -->
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="resourcesTable" class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Title</th>
                                            <th>Category</th>
                                            <th>Type</th>
                                            <th>School</th>
                                            <th>Target Audience</th>
                                            <th>Date</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($filtered_resources as $resource): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($resource['id']); ?></td>
                                                <td><?php echo htmlspecialchars($resource['title']); ?></td>
                                                <td><?php echo htmlspecialchars($resource['category']); ?></td>
                                                <td><?php echo htmlspecialchars($resource['resource_type']); ?></td>
                                                <td><?php echo htmlspecialchars($resource['school_name'] ?? 'N/A'); ?></td>
                                                <td><?php echo htmlspecialchars($resource['target_audience']); ?></td>
                                                <td><?php echo date('M d, Y', strtotime($resource['upload_date'])); ?></td>
                                                <td class="table-actions">
                                                    <button type="button" class="btn btn-sm btn-primary edit-resource" 
                                                            data-id="<?php echo $resource['id']; ?>"
                                                            data-title="<?php echo htmlspecialchars($resource['title']); ?>"
                                                            data-description="<?php echo htmlspecialchars($resource['description']); ?>"
                                                            data-category="<?php echo htmlspecialchars($resource['category']); ?>"
                                                            data-resource-type="<?php echo htmlspecialchars($resource['resource_type']); ?>"
                                                            data-file-path="<?php echo htmlspecialchars($resource['file_path']); ?>"
                                                            data-external-link="<?php echo htmlspecialchars($resource['external_link']); ?>"
                                                            data-target-audience="<?php echo htmlspecialchars($resource['target_audience']); ?>"
                                                            data-school-id="<?php echo $resource['school_id']; ?>"
                                                            data-is-visible="<?php echo $resource['is_visible'] ? '1' : '0'; ?>">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-danger" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#deleteResourceModal"
                                                            data-id="<?php echo $resource['id']; ?>"
                                                            data-title="<?php echo htmlspecialchars($resource['title']); ?>">
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
                </div>
            </main>
        </div>
    </div>

    <!-- Add Resource Modal -->
    <div class="modal fade" id="addResourceModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Resource</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">
                        <div class="mb-3">
                            <label for="title" class="form-label">Title</label>
                            <input type="text" class="form-control" id="title" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="category" class="form-label">Category</label>
                            <select class="form-select" id="category" name="category" required>
                                <option value="textbook">Textbook</option>
                                <option value="ebook">E-Book</option>
                                <option value="presentation">Presentation</option>
                                <option value="worksheet">Worksheet</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="resource_type" class="form-label">Type</label>
                            <select class="form-select" id="resource_type" name="resource_type" required>
                                <option value="file">File</option>
                                <option value="link">Link</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="file_path" class="form-label">File Path</label>
                            <input type="text" class="form-control" id="file_path" name="file_path">
                        </div>
                        <div class="mb-3">
                            <label for="external_link" class="form-label">External Link</label>
                            <input type="url" class="form-control" id="external_link" name="external_link">
                        </div>
                        <div class="mb-3">
                            <label for="target_audience" class="form-label">Target Audience</label>
                            <select class="form-select" id="target_audience" name="target_audience" required>
                                <option value="cics">CICS</option>
                                <option value="cte">CTE</option>
                                <option value="cit">CIT</option>
                                <option value="cas">CAS</option>
                                <option value="cabe">CABE</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="school_id" class="form-label">School</label>
                            <select class="form-select" id="school_id" name="school_id">
                                <option value="">Select School</option>
                                <?php foreach ($schools as $school): ?>
                                    <option value="<?php echo $school['id']; ?>"><?php echo htmlspecialchars($school['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="is_visible" class="form-label">Visibility</label>
                            <select class="form-select" id="is_visible" name="is_visible" required>
                                <option value="1">Visible</option>
                                <option value="0">Hidden</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Resource</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Resource Modal -->
    <div class="modal fade" id="editResourceModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Resource</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="id" id="editResourceId">
                        <div class="mb-3">
                            <label for="editTitle" class="form-label">Title</label>
                            <input type="text" class="form-control" id="editTitle" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label for="editDescription" class="form-label">Description</label>
                            <textarea class="form-control" id="editDescription" name="description" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="editCategory" class="form-label">Category</label>
                            <select class="form-select" id="editCategory" name="category" required>
                                <option value="textbook">Textbook</option>
                                <option value="ebook">E-Book</option>
                                <option value="presentation">Presentation</option>
                                <option value="worksheet">Worksheet</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="editResourceType" class="form-label">Resource Type</label>
                            <select class="form-select" id="editResourceType" name="resource_type" required>
                                <option value="file">File</option>
                                <option value="link">Link</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="editFilePath" class="form-label">File Path</label>
                            <input type="text" class="form-control" id="editFilePath" name="file_path">
                        </div>
                        <div class="mb-3">
                            <label for="editExternalLink" class="form-label">External Link</label>
                            <input type="url" class="form-control" id="editExternalLink" name="external_link">
                        </div>
                        <div class="mb-3">
                            <label for="editTargetAudience" class="form-label">Target Audience</label>
                            <select class="form-select" id="editTargetAudience" name="target_audience" required>
                                <option value="cics">CICS</option>
                                <option value="cte">CTE</option>
                                <option value="cit">CIT</option>
                                <option value="cas">CAS</option>
                                <option value="cabe">CABE</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="editSchoolId" class="form-label">School</label>
                            <select class="form-select" id="editSchoolId" name="school_id">
                                <option value="">Select School</option>
                                <?php foreach ($schools as $school): ?>
                                    <option value="<?php echo $school['id']; ?>"><?php echo htmlspecialchars($school['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="editIsVisible" class="form-label">Visibility</label>
                            <select class="form-select" id="editIsVisible" name="is_visible" required>
                                <option value="1">Visible</option>
                                <option value="0">Hidden</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Resource Modal -->
    <div class="modal fade" id="deleteResourceModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Delete Resource</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" id="delete_id">
                        <p>Are you sure you want to delete the resource "<span id="delete_title" class="fw-bold"></span>"?</p>
                        <p class="text-danger">This action cannot be undone.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete Resource</button>
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
            $('#resourcesTable').DataTable({
                order: [[0, 'desc']],
                pageLength: 10,
                language: {
                    search: "Search:",
                    lengthMenu: "Show _MENU_ entries per page",
                    info: "Showing _START_ to _END_ of _TOTAL_ entries",
                    infoEmpty: "No entries to show",
                    infoFiltered: "(filtered from _MAX_ total entries)"
                }
            });

            // Handle edit resource modal
            $('.edit-resource').click(function() {
                var id = $(this).data('id');
                var title = $(this).data('title');
                var description = $(this).data('description');
                var category = $(this).data('category').toLowerCase();
                var resourceType = $(this).data('resource-type');
                var filePath = $(this).data('file-path');
                var externalLink = $(this).data('external-link');
                var targetAudience = $(this).data('target-audience');
                var schoolId = $(this).data('school-id');
                var isVisible = $(this).data('is-visible');

                $('#editResourceId').val(id);
                $('#editTitle').val(title);
                $('#editDescription').val(description);
                $('#editCategory').val(category);
                $('#editResourceType').val(resourceType);
                $('#editFilePath').val(filePath);
                $('#editExternalLink').val(externalLink);
                $('#editTargetAudience').val(targetAudience);
                $('#editSchoolId').val(schoolId);
                $('#editIsVisible').val(isVisible);

                $('#editResourceModal').modal('show');
            });

            // Handle delete resource modal
            $('#deleteResourceModal').on('show.bs.modal', function(event) {
                var button = $(event.relatedTarget);
                var id = button.data('id');
                var title = button.data('title');
                $('#delete_id').val(id);
                $('#delete_title').text(title);
            });
        });
    </script>
</body>
</html>