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
$error = '';

// Clear the session message after displaying it
if (isset($_SESSION['message'])) {
    unset($_SESSION['message']);
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'get_donations':
                $type = $_POST['type'] ?? '';
                $date = $_POST['date'] ?? '';
                $search = $_POST['search'] ?? '';
                
                $sql = "SELECT d.*, s.name as school_name 
                        FROM donations d 
                        LEFT JOIN schools s ON d.school_id = s.id 
                        WHERE 1=1";
                
                $params = [];
                $types = '';
                
                if (!empty($type)) {
                    $sql .= " AND d.resource_type = ?";
                    $params[] = $type;
                    $types .= 's';
                }
                
                if (!empty($date)) {
                    $today = date('Y-m-d');
                    switch ($date) {
                        case 'today':
                            $sql .= " AND d.donation_date = ?";
                            $params[] = $today;
                            $types .= 's';
                            break;
                        case 'week':
                            $sql .= " AND d.donation_date >= DATE_SUB(?, INTERVAL 1 WEEK)";
                            $params[] = $today;
                            $types .= 's';
                            break;
                        case 'month':
                            $sql .= " AND d.donation_date >= DATE_SUB(?, INTERVAL 1 MONTH)";
                            $params[] = $today;
                            $types .= 's';
                            break;
                        case 'year':
                            $sql .= " AND d.donation_date >= DATE_SUB(?, INTERVAL 1 YEAR)";
                            $params[] = $today;
                            $types .= 's';
                            break;
                    }
                }
                
                if (!empty($search)) {
                    $sql .= " AND (d.title LIKE ? OR d.description LIKE ? OR d.donor_name LIKE ?)";
                    $searchTerm = "%$search%";
                    $params[] = $searchTerm;
                    $params[] = $searchTerm;
                    $params[] = $searchTerm;
                    $types .= 'sss';
                }
                
                $sql .= " ORDER BY d.donation_date DESC";
                
                $stmt = $conn->prepare($sql);
                if (!empty($params)) {
                    $stmt->bind_param($types, ...$params);
                }
                $stmt->execute();
                $result = $stmt->get_result();
                
                $donations = [];
                while ($row = $result->fetch_assoc()) {
                    $donations[] = $row;
                }
                
                header('Content-Type: application/json');
                echo json_encode([
                    'data' => $donations,
                    'recordsTotal' => count($donations),
                    'recordsFiltered' => count($donations)
                ]);
                exit;
                break;
                
            case 'get_donation':
                if (isset($_POST['id'])) {
                    $donation = getDonationById($_POST['id']);
                    if ($donation) {
                        header('Content-Type: application/json');
                        echo json_encode($donation);
                    } else {
                        header('Content-Type: application/json');
                        echo json_encode(['error' => 'Donation not found']);
                    }
                } else {
                    header('Content-Type: application/json');
                    echo json_encode(['error' => 'No ID provided']);
                }
                exit;
                break;
                
            case 'add':
                $donor_name = $_POST['donor_name'] ?? '';
                $donor_email = $_POST['donor_email'] ?? '';
                $resource_type = $_POST['resource_type'] ?? '';
                $title = $_POST['title'] ?? '';
                $description = $_POST['description'] ?? '';
                $file_path = $_POST['file_path'] ?? '';
                $external_link = $_POST['external_link'] ?? '';
                $school_id = $_POST['school_id'] ?? null;
                $donation_date = $_POST['donation_date'] ?? date('Y-m-d');
                $purpose = $_POST['purpose'] ?? '';
                $status = $_POST['status'] ?? 'pending';
                $notes = $_POST['notes'] ?? '';
                
                if (empty($donor_name) || empty($donor_email) || empty($resource_type) || empty($title) || empty($school_id)) {
                    $_SESSION['error'] = 'Please fill in all required fields';
                    header('Location: donations.php');
                    exit;
                }
                
                if (addDonation($donor_name, $donor_email, $resource_type, $title, $description, $file_path, $external_link, $school_id, $donation_date, $purpose, $status, $notes)) {
                    $_SESSION['message'] = 'Donation added successfully';
                } else {
                    $_SESSION['error'] = 'Failed to add donation. Please try again.';
                }
                header('Location: donations.php');
                exit;
                break;
                
            case 'edit':
                $id = $_POST['id'] ?? null;
                if (!$id) {
                    $_SESSION['error'] = 'No donation ID provided';
                    header('Location: donations.php');
                    exit;
                }
                
                $donor_name = $_POST['donor_name'] ?? '';
                $donor_email = $_POST['donor_email'] ?? '';
                $resource_type = $_POST['resource_type'] ?? '';
                $title = $_POST['title'] ?? '';
                $description = $_POST['description'] ?? '';
                $file_path = $_POST['file_path'] ?? '';
                $external_link = $_POST['external_link'] ?? '';
                $school_id = $_POST['school_id'] ?? null;
                $donation_date = $_POST['donation_date'] ?? date('Y-m-d');
                $purpose = $_POST['purpose'] ?? '';
                $status = $_POST['status'] ?? 'pending';
                $notes = $_POST['notes'] ?? '';
                
                if (empty($donor_name) || empty($donor_email) || empty($resource_type) || empty($title) || empty($school_id)) {
                    $_SESSION['error'] = 'Please fill in all required fields';
                    header('Location: donations.php');
                    exit;
                }
                
                if (updateDonation($id, $donor_name, $donor_email, $resource_type, $title, $description, $file_path, $external_link, $school_id, $donation_date, $purpose, $status, $notes)) {
                    $_SESSION['message'] = 'Donation updated successfully';
                } else {
                    $_SESSION['error'] = 'Failed to update donation. Please try again.';
                }
                header('Location: donations.php');
                exit;
                break;
                
            case 'delete':
                $id = $_POST['id'] ?? null;
                if (!$id) {
                    $_SESSION['error'] = 'No donation ID provided';
                    header('Location: donations.php');
                    exit;
                }
                
                if (deleteDonation($id)) {
                    $_SESSION['message'] = 'Donation deleted successfully';
                } else {
                    $_SESSION['error'] = 'Failed to delete donation. Please try again.';
                }
                header('Location: donations.php');
                exit;
                break;
        }
    }
}

// Get all donations and schools for display
$donations = getAllDonations();
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

                    <!-- Resource Statistics -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header bg-primary-custom text-white">
                                    <h5 class="mb-0">Resource Statistics</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3 mb-3">
                                            <div class="p-3 text-center donation-stats">
                                                <h3 class="donation-amount"><?php echo count($donations); ?></h3>
                                                <p class="mb-0">Total Resources</p>
                                            </div>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <div class="p-3 text-center donation-stats">
                                                <h3 class="donation-amount"><?php echo count(array_unique(array_column($donations, 'donor_email'))); ?></h3>
                                                <p class="mb-0">Total Donors</p>
                                            </div>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <div class="p-3 text-center donation-stats">
                                                <h3 class="donation-amount"><?php echo count(array_filter($donations, function($d) { return $d['status'] === 'completed'; })); ?></h3>
                                                <p class="mb-0">Completed</p>
                                            </div>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <div class="p-3 text-center donation-stats">
                                                <h3 class="donation-amount"><?php echo count(array_filter($donations, function($d) { return $d['status'] === 'pending'; })); ?></h3>
                                                <p class="mb-0">Pending</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Resource Filters -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-body">
                                    <form class="row g-3" id="filterForm">
                                        <div class="col-md-3">
                                            <label for="resourceType" class="form-label">Resource Type</label>
                                            <select class="form-select" id="resourceType" name="type">
                                                <option value="">All Types</option>
                                                <option value="document">Document</option>
                                                <option value="video">Video</option>
                                                <option value="link">Link</option>
                                                <option value="other">Other</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label for="resourceDate" class="form-label">Date Range</label>
                                            <select class="form-select" id="resourceDate" name="date">
                                                <option value="">All Time</option>
                                                <option value="today">Today</option>
                                                <option value="week">This Week</option>
                                                <option value="month">This Month</option>
                                                <option value="year">This Year</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="searchResource" class="form-label">Search</label>
                                            <input type="text" class="form-control" id="searchResource" name="search" placeholder="Title, description, or donor">
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
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h6 class="m-0 font-weight-bold">Resource Records</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="donationsTable" class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Title</th>
                                            <th>Donor</th>
                                            <th>Type</th>
                                            <th>School</th>
                                            <th>Purpose</th>
                                            <th>Date</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($donations as $donation): ?>
                                            <tr>
                                                <td><?php echo $donation['id']; ?></td>
                                                <td><?php echo htmlspecialchars($donation['title']); ?></td>
                                                <td><?php echo htmlspecialchars($donation['donor_name']); ?></td>
                                                <td><?php echo htmlspecialchars($donation['resource_type']); ?></td>
                                                <td><?php echo htmlspecialchars($donation['school_name']); ?></td>
                                                <td><?php echo htmlspecialchars($donation['purpose']); ?></td>
                                                <td><?php echo htmlspecialchars($donation['donation_date']); ?></td>
                                                <td>
                                                    <button class="btn btn-sm btn-primary edit-donation" 
                                                        data-id="<?php echo $donation['id']; ?>"
                                                        data-donor-name="<?php echo htmlspecialchars($donation['donor_name']); ?>"
                                                        data-donor-email="<?php echo htmlspecialchars($donation['donor_email']); ?>"
                                                        data-resource-type="<?php echo htmlspecialchars($donation['resource_type']); ?>"
                                                        data-title="<?php echo htmlspecialchars($donation['title']); ?>"
                                                        data-description="<?php echo htmlspecialchars($donation['description']); ?>"
                                                        data-file-path="<?php echo htmlspecialchars($donation['file_path']); ?>"
                                                        data-external-link="<?php echo htmlspecialchars($donation['external_link']); ?>"
                                                        data-school-id="<?php echo $donation['school_id']; ?>"
                                                        data-donation-date="<?php echo $donation['donation_date']; ?>"
                                                        data-purpose="<?php echo htmlspecialchars($donation['purpose']); ?>"
                                                        data-notes="<?php echo htmlspecialchars($donation['notes']); ?>"
                                                        data-bs-toggle="tooltip" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-danger delete-donation" 
                                                        data-id="<?php echo $donation['id']; ?>"
                                                        data-title="<?php echo htmlspecialchars($donation['title']); ?>"
                                                        data-bs-toggle="tooltip" title="Delete">
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
    <div class="modal fade" id="addDonationModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Resource</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <input type="hidden" name="action" value="add">
                    <div class="modal-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="donorName" class="form-label">Donor Name</label>
                                <input type="text" class="form-control" name="donor_name" required>
                            </div>
                            <div class="col-md-6">
                                <label for="donorEmail" class="form-label">Donor Email</label>
                                <input type="email" class="form-control" name="donor_email" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="resourceType" class="form-label">Resource Type</label>
                                <select class="form-select" name="resource_type" required>
                                    <option value="document">Document</option>
                                    <option value="video">Video</option>
                                    <option value="link">Link</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="school" class="form-label">Recipient School</label>
                                <select class="form-select" name="school_id" required>
                                    <?php foreach ($schools as $school): ?>
                                        <option value="<?php echo $school['id']; ?>"><?php echo htmlspecialchars($school['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="title" class="form-label">Resource Title</label>
                            <input type="text" class="form-control" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="2" required></textarea>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="filePath" class="form-label">File Path</label>
                                <input type="text" class="form-control" name="file_path">
                            </div>
                            <div class="col-md-6">
                                <label for="externalLink" class="form-label">External Link</label>
                                <input type="url" class="form-control" name="external_link">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="donationDate" class="form-label">Donation Date</label>
                                <input type="date" class="form-control" name="donation_date" required>
                            </div>
                            <div class="col-md-6">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" name="status" required>
                                    <option value="pending">Pending</option>
                                    <option value="in_progress">In Progress</option>
                                    <option value="completed">Completed</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="purpose" class="form-label">Purpose</label>
                            <textarea class="form-control" name="purpose" rows="2" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="notes" class="form-label">Additional Notes</label>
                            <textarea class="form-control" name="notes" rows="3"></textarea>
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
    <div class="modal fade" id="editDonationModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Resource</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" id="editId">
                    <div class="modal-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="editDonorName" class="form-label">Donor Name</label>
                                <input type="text" class="form-control" id="editDonorName" name="donor_name" required>
                            </div>
                            <div class="col-md-6">
                                <label for="editDonorEmail" class="form-label">Donor Email</label>
                                <input type="email" class="form-control" id="editDonorEmail" name="donor_email" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="editResourceType" class="form-label">Resource Type</label>
                                <select class="form-select" id="editResourceType" name="resource_type" required>
                                    <option value="document">Document</option>
                                    <option value="video">Video</option>
                                    <option value="link">Link</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="editSchool" class="form-label">Recipient School</label>
                                <select class="form-select" id="editSchool" name="school_id" required>
                                    <?php foreach ($schools as $school): ?>
                                        <option value="<?php echo $school['id']; ?>"><?php echo htmlspecialchars($school['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="editTitle" class="form-label">Resource Title</label>
                            <input type="text" class="form-control" id="editTitle" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label for="editDescription" class="form-label">Description</label>
                            <textarea class="form-control" id="editDescription" name="description" rows="2" required></textarea>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="editFilePath" class="form-label">File Path</label>
                                <input type="text" class="form-control" id="editFilePath" name="file_path">
                            </div>
                            <div class="col-md-6">
                                <label for="editExternalLink" class="form-label">External Link</label>
                                <input type="url" class="form-control" id="editExternalLink" name="external_link">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="editDonationDate" class="form-label">Donation Date</label>
                                <input type="date" class="form-control" id="editDonationDate" name="donation_date" required>
                            </div>
                            <div class="col-md-6">
                                <label for="editStatus" class="form-label">Status</label>
                                <select class="form-select" id="editStatus" name="status" required>
                                    <option value="pending">Pending</option>
                                    <option value="in_progress">In Progress</option>
                                    <option value="completed">Completed</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="editPurpose" class="form-label">Purpose</label>
                            <textarea class="form-control" id="editPurpose" name="purpose" rows="2" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="editNotes" class="form-label">Additional Notes</label>
                            <textarea class="form-control" id="editNotes" name="notes" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Resource</button>
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
                        <p>Are you sure you want to delete this resource? This action cannot be undone.</p>
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
            var table = $('#donationsTable').DataTable({
                responsive: true,
                lengthMenu: [10, 25, 50, 100],
                pageLength: 10,
                order: [[5, 'desc']] // Default sort by donation date
            });
            
            // Initialize tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            });

            // Handle filter form submission
            $('#filterForm').on('submit', function(e) {
                e.preventDefault();
                var type = $('#resourceType').val();
                var date = $('#resourceDate').val();
                var search = $('#searchResource').val();

                // Clear previous search
                table.search('').columns().search('');

                // Apply type filter
                if (type) {
                    table.column(3).search(type);
                }

                // Apply date filter
                if (date) {
                    var today = new Date();
                    var filterDate = new Date();
                    
                    switch(date) {
                        case 'today':
                            table.column(5).search(today.toISOString().split('T')[0]);
                            break;
                        case 'week':
                            filterDate.setDate(today.getDate() - 7);
                            table.column(5).search(filterDate.toISOString().split('T')[0] + '|' + today.toISOString().split('T')[0]);
                            break;
                        case 'month':
                            filterDate.setMonth(today.getMonth() - 1);
                            table.column(5).search(filterDate.toISOString().split('T')[0] + '|' + today.toISOString().split('T')[0]);
                            break;
                        case 'year':
                            filterDate.setFullYear(today.getFullYear() - 1);
                            table.column(5).search(filterDate.toISOString().split('T')[0] + '|' + today.toISOString().split('T')[0]);
                            break;
                    }
                }

                // Apply search filter
                if (search) {
                    table.search(search);
                }

                // Redraw the table
                table.draw();
            });

            // Handle reset button
            $('#resetFilters').click(function() {
                $('#filterForm')[0].reset();
                table.search('').columns().search('').draw();
            });

            // Handle edit button click
            $('.edit-donation').click(function() {
                var id = $(this).data('id');
                var donorName = $(this).data('donor-name');
                var donorEmail = $(this).data('donor-email');
                var resourceType = $(this).data('resource-type');
                var title = $(this).data('title');
                var description = $(this).data('description');
                var filePath = $(this).data('file-path');
                var externalLink = $(this).data('external-link');
                var schoolId = $(this).data('school-id');
                var donationDate = $(this).data('donation-date');
                var purpose = $(this).data('purpose');
                var status = $(this).data('status');
                var notes = $(this).data('notes');

                $('#editId').val(id);
                $('#editDonorName').val(donorName);
                $('#editDonorEmail').val(donorEmail);
                $('#editResourceType').val(resourceType);
                $('#editTitle').val(title);
                $('#editDescription').val(description);
                $('#editFilePath').val(filePath);
                $('#editExternalLink').val(externalLink);
                $('#editSchool').val(schoolId);
                $('#editDonationDate').val(donationDate);
                $('#editPurpose').val(purpose);
                $('#editStatus').val(status);
                $('#editNotes').val(notes);

                $('#editDonationModal').modal('show');
            });

            // Handle delete button click
            $('.delete-donation').click(function() {
                var id = $(this).data('id');
                var title = $(this).data('title');
                
                $('#deleteId').val(id);
                $('#deleteTitle').text(title);
                $('#deleteDonationModal').modal('show');
            });
        });
    </script>
</body>
</html>