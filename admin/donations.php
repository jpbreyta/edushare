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

$active_tab = 'donations';
$message = $_SESSION['message'] ?? '';
$error = $_SESSION['error'] ?? '';

// Clear the session message after displaying it
if (isset($_SESSION['message'])) {
    unset($_SESSION['message']);
}
if (isset($_SESSION['error'])) {
    unset($_SESSION['error']);
}

// Define valid resource types based on ENUM
$valid_resource_types = ['book', 'equipment', 'software', 'materials', 'other'];

// Handle AJAX request for DataTables
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'get_donations') {
    $search = $_POST['search']['value'] ?? $_POST['search'] ?? ''; // Handle DataTables search structure
    
    // Fetch total count before search for DataTables metadata using stored procedure
    $totalRecords = getDonationsCount();

    // Fetch filtered data using the search SP
    $donations = searchDonations($search); 
    
    header('Content-Type: application/json');
    echo json_encode([
        'draw' => isset($_POST['draw']) ? intval($_POST['draw']) : 0,
        'recordsTotal' => $totalRecords, 
        'recordsFiltered' => count($donations), // Count after search by SP
        'data' => $donations
    ]);
    exit;
}

// Handle standard form submissions for Add/Edit/Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'add':
            $title = trim($_POST['title'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $resource_type = $_POST['resource_type'] ?? '';
            $quantity_str = trim($_POST['quantity'] ?? '1');
            $donor_name = trim($_POST['donor_name'] ?? '');
            $school_id = $_POST['school_id'] ?? null;
            
            if (empty($title) || empty($resource_type) || !in_array($resource_type, $valid_resource_types) || empty($quantity_str) || !ctype_digit($quantity_str) || (int)$quantity_str <= 0 || empty($donor_name) || $school_id === null) {
                $_SESSION['error'] = 'Please fill in all required fields with valid values (Title, Resource Type, Quantity > 0, Donor Name, School).';
            } else {
                if (addDonation($title, $description, $resource_type, (int)$quantity_str, $donor_name, (int)$school_id)) {
                    $_SESSION['message'] = 'Resource donation added successfully';
                } else {
                    $_SESSION['error'] = 'Failed to add resource donation. Details: ' . ($conn->error ?? 'Unknown error');
                }
            }
            header('Location: donations.php');
            exit;
            break;

        case 'edit':
            $id = $_POST['id'] ?? null;
            $title = trim($_POST['title'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $resource_type = $_POST['resource_type'] ?? '';
            $quantity_str = trim($_POST['quantity'] ?? '');
            $donor_name = trim($_POST['donor_name'] ?? ''); // Now allow editing donor name

            if (!$id || empty($title) || empty($resource_type) || !in_array($resource_type, $valid_resource_types) || empty($quantity_str) || !ctype_digit($quantity_str) || (int)$quantity_str <= 0 || empty($donor_name)) {
                $_SESSION['error'] = 'Invalid data provided for update. Please check required fields (Title, Resource Type, Quantity > 0, Donor Name).';
            } else {
                // Pass donor_name to update function
                $update_result = updateDonation((int)$id, $title, $description, $resource_type, (int)$quantity_str, $donor_name);
                if ($update_result) {
                    $_SESSION['message'] = 'Resource donation updated successfully';
                } else {
                    $_SESSION['error'] = 'Failed to update resource donation. Please try again.';
                }
            }
            header('Location: donations.php');
            exit;
            break;

        case 'delete':
            $id = $_POST['id'] ?? null;
            if (!$id || !ctype_digit((string)$id)) {
                $_SESSION['error'] = 'Invalid donation ID provided';
            } else {
                if (deleteDonation((int)$id)) {
                    $_SESSION['message'] = 'Resource donation deleted successfully';
                } else {
                    $_SESSION['error'] = 'Failed to delete resource donation. Details: ' . ($conn->error ?? 'Unknown error');
                }
            }
            header('Location: donations.php');
            exit;
            break;
    }
}

// Get all donations and schools for display
$schools = getAllSchools();
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
                                <i class="fas fa-hand-holding-heart me-2"></i> Resource Donations
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
                        <h1 class="h2">Resource Donations Management</h1>
                        <div class="btn-toolbar mb-2 mb-md-0">
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDonationModal">
                                <i class="fas fa-plus me-1"></i> Add New Resource Donation
                            </button>
                        </div>
                    </div>

                    <?php if ($message): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?php echo htmlspecialchars($message); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php echo htmlspecialchars($error); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Resource Filters -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="row g-3">
                                         <div class="col-md-8">
                                             <label for="searchDonation" class="form-label">Search</label>
                                             <input type="text" class="form-control" id="searchDonation" placeholder="Title, description, type, donor, school...">
                                         </div>
                                         <div class="col-md-4 d-flex align-items-end">
                                             <button type="button" class="btn btn-primary w-100" id="applyFilterBtn">
                                                 <i class="fas fa-search me-1"></i> Search
                                             </button>
                                         </div>
                                     </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Resources Table -->
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h6 class="m-0 font-weight-bold">Resource Donation Records</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="donationsTable" class="table table-striped table-hover w-100">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Title</th>
                                            <th>Type</th>
                                            <th>Qty</th>
                                            <th>Donor Name</th>
                                            <th>School</th>
                                            <th>Date</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- DataTables will populate this -->
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
    <div class="modal fade" id="addDonationModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Resource Donation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <input type="hidden" name="action" value="add">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="addTitle" class="form-label">Resource Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="addTitle" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label for="addDescription" class="form-label">Description</label>
                            <textarea class="form-control" id="addDescription" name="description" rows="3"></textarea>
                        </div>
                         <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="addResourceType" class="form-label">Resource Type <span class="text-danger">*</span></label>
                                <select class="form-select" id="addResourceType" name="resource_type" required>
                                    <option value="">Select Type...</option>
                                    <?php foreach ($valid_resource_types as $type): ?>
                                    <option value="<?php echo $type; ?>"><?php echo ucfirst($type); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="addQuantity" class="form-label">Quantity <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="addQuantity" name="quantity" min="1" value="1" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="addDonorName" class="form-label">Donor Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="addDonorName" name="donor_name" placeholder="Enter donor name..." required>
                            </div>
                            <div class="col-md-6">
                                <label for="addSchoolId" class="form-label">Recipient School <span class="text-danger">*</span></label>
                                <select class="form-select" id="addSchoolId" name="school_id" required>
                                    <option value="">Select School...</option>
                                    <?php foreach ($schools as $school): ?><option value="<?php echo $school['id']; ?>"><?php echo htmlspecialchars($school['name']); ?></option><?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Resource Donation</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Resource Modal -->
    <div class="modal fade" id="editDonationModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Resource Donation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" id="editId">
                    <div class="modal-body">
                         <div class="mb-3">
                            <label for="editTitle" class="form-label">Resource Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="editTitle" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label for="editDescription" class="form-label">Description</label>
                            <textarea class="form-control" id="editDescription" name="description" rows="3"></textarea>
                        </div>
                         <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="editResourceType" class="form-label">Resource Type <span class="text-danger">*</span></label>
                                <select class="form-select" id="editResourceType" name="resource_type" required>
                                     <option value="">Select Type...</option>
                                    <?php foreach ($valid_resource_types as $type): ?>
                                    <option value="<?php echo $type; ?>"><?php echo ucfirst($type); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="editQuantity" class="form-label">Quantity <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="editQuantity" name="quantity" min="1" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="editDonorName" class="form-label">Donor Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="editDonorName" name="donor_name" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Resource Donation</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteDonationModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" id="deleteId">
                    <div class="modal-body">
                        <p>Are you sure you want to delete the resource donation "<strong id="deleteDonationTitle"></strong>"? This action cannot be undone.</p>
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
            var table = $('#donationsTable').DataTable({
                responsive: true,
                processing: true,
                serverSide: true, // Use server-side processing for searching/pagination with SP
                ajax: {
                    url: 'donations.php', // Post to this same file
                    type: 'POST',
                    data: function(d) {
                        // Add action and potentially other standard DataTable params
                        d.action = 'get_donations';
                        // d.start, d.length, d.order, etc. are sent automatically by DataTables
                        // The search value is sent under d.search.value
                    },
                     error: function (xhr, error, thrown) {
                        console.error("DataTables error:", error, thrown);
                        alert('Error fetching data. Check console for details.');
                    }
                },
                columns: [
                    { data: 'id' },
                    { data: 'title' },
                    { data: 'resource_type', render: function(data){ return data ? data.charAt(0).toUpperCase() + data.slice(1) : ''; } }, // Capitalize type
                    { data: 'quantity' },
                    { data: 'donor_name' },
                    { data: 'school_name' },
                    { data: 'created_at', render: function(data) { 
                        try {
                            var date = new Date(data + 'Z'); // Assume UTC, add Z
                            if (isNaN(date)) return 'Invalid Date';
                            return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric'});
                        } catch (e) { return 'Invalid Date'; }
                        }
                    },
                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row) {
                            // Escape potential HTML issues in data attributes
                            const title = $('<div />').text(row.title).html();
                            const description = $('<div />').text(row.description).html();
                            const donorName = $('<div />').text(row.donor_name).html();
                            return `
                                <button class="btn btn-sm btn-primary edit-donation" 
                                    data-id="${row.id}"
                                    data-title="${title}"
                                    data-description="${description}"
                                    data-resource-type="${row.resource_type}"
                                    data-quantity="${row.quantity}"
                                    data-donor-name="${donorName}"
                                    data-bs-toggle="tooltip" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger delete-donation" 
                                    data-id="${row.id}"
                                    data-title="${title}"
                                    data-bs-toggle="tooltip" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            `;
                        }
                    }
                ],
                order: [[6, 'desc']], // Default sort by created_at date (column index 6)
                // Initialize tooltips after each draw
                 drawCallback: function( settings ) {
                     var tooltipTriggerList = [].slice.call(document.querySelectorAll('#donationsTable [data-bs-toggle="tooltip"]'))
                     tooltipTriggerList.forEach(function (tooltipTriggerEl) {
                         // Dispose existing tooltip before creating new one if it exists
                         var existingTooltip = bootstrap.Tooltip.getInstance(tooltipTriggerEl);
                         if (existingTooltip) {
                             existingTooltip.dispose();
                         }
                         new bootstrap.Tooltip(tooltipTriggerEl);
                     });
                 }
            });

            // Custom search trigger
            $('#applyFilterBtn').on('click', function(e) {
                 table.search($('#searchDonation').val()).draw();
            });
            // Optional: Trigger search on pressing Enter in the search input
            $('#searchDonation').on('keypress', function(e) {
                if (e.which == 13) { // Enter key pressed
                    table.search(this.value).draw();
                    return false; // Prevent form submission
                }
            });

            // Use delegated event handling for buttons inside the table
            $('#donationsTable tbody').on('click', '.edit-donation', function () {
                var data = $(this).data(); // Get all data attributes
                console.log("Edit data:", data);
                $('#editId').val(data.id);
                $('#editTitle').val(data.title);
                $('#editDescription').val(data.description);
                $('#editResourceType').val(data.resourceType); // Match data attribute case
                $('#editQuantity').val(data.quantity);
                $('#editDonorName').val(data.donorName);
                $('#editDonationModal').modal('show');
            });

            $('#donationsTable tbody').on('click', '.delete-donation', function () {
                var data = $(this).data();
                $('#deleteId').val(data.id);
                $('#deleteDonationTitle').text(data.title); // Show title in confirmation
                $('#deleteDonationModal').modal('show');
            });
        });
    </script>
</body>
</html>