<?php
require_once 'db_functions.php';
require_once '../auth/auth_functions.php';
check_permission('admin');

$active_tab = 'resources';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_resource':
                $errors = validate_resource_data($_POST);
                if (empty($errors)) {
                    $response = handle_database_operation('add', 'resources', $_POST);
                    if ($response['success']) {
                        $success_message = 'Resource added successfully';
                    } else {
                        $error_message = $response['message'];
                    }
                }
                break;

            case 'edit_resource':
                $errors = validate_resource_data($_POST);
                if (empty($errors)) {
                    $response = handle_database_operation('edit', 'resources', $_POST);
                    if ($response['success']) {
                        $success_message = 'Resource updated successfully';
                    } else {
                        $error_message = $response['message'];
                    }
                }
                break;

            case 'delete_resource':
                $response = handle_database_operation('delete', 'resources', ['id' => $_POST['resource_id']]);
                if ($response['success']) {
                    $success_message = 'Resource deleted successfully';
                } else {
                    $error_message = $response['message'];
                }
                break;
        }
    }
}

// Get all resources
$resources = get_all_records('resources');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Resources - EduShare Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .bg-primary-custom {
            background-color: #0d6efd;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container mt-4">
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>
        
        <?php if (isset($errors) && !empty($errors)): ?>
            <div class="alert alert-danger">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Manage Resources</h5>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addResourceModal">
                    <i class="fas fa-plus"></i> Add Resource
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="resourcesTable" class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Title</th>
                                <th>Description</th>
                                <th>Category</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($resources as $resource): ?>
                                <tr>
                                    <td><?php echo $resource['id']; ?></td>
                                    <td><?php echo htmlspecialchars($resource['title']); ?></td>
                                    <td><?php echo htmlspecialchars($resource['description']); ?></td>
                                    <td><?php echo htmlspecialchars($resource['category']); ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-primary edit-resource" data-id="<?php echo $resource['id']; ?>">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger delete-resource" data-id="<?php echo $resource['id']; ?>">
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

    <!-- Add Resource Modal -->
    <div class="modal fade" id="addResourceModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Resource</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add_resource">
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
                            <input type="text" class="form-control" id="category" name="category" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit_resource">
                        <input type="hidden" name="id" id="edit_resource_id">
                        <div class="mb-3">
                            <label for="edit_title" class="form-label">Title</label>
                            <input type="text" class="form-control" id="edit_title" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_description" class="form-label">Description</label>
                            <textarea class="form-control" id="edit_description" name="description" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="edit_category" class="form-label">Category</label>
                            <input type="text" class="form-control" id="edit_category" name="category" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Update Resource</button>
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
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="delete_resource">
                        <input type="hidden" name="resource_id" id="delete_resource_id">
                        <p>Are you sure you want to delete this resource?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </div>
                </form>
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
            
            // Handle edit resource button click
            $('.edit-resource').click(function() {
                var resourceId = $(this).data('id');
                $.ajax({
                    url: 'db_functions.php',
                    method: 'GET',
                    data: { id: resourceId, table: 'resources' },
                    success: function(response) {
                        var resource = JSON.parse(response);
                        $('#edit_resource_id').val(resource.id);
                        $('#edit_title').val(resource.title);
                        $('#edit_description').val(resource.description);
                        $('#edit_category').val(resource.category);
                        $('#editResourceModal').modal('show');
                    }
                });
            });

            // Handle delete resource button click
            $('.delete-resource').click(function() {
                var resourceId = $(this).data('id');
                $('#delete_resource_id').val(resourceId);
                $('#deleteResourceModal').modal('show');
            });
        });
    </script>
</body>
</html>