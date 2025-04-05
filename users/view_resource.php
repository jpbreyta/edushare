<?php
require_once '../auth/auth_functions.php';
require_once '../auth/db_connect.php';

// Check if resource ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    redirect("resources.php");
}

$resource_id = $_GET['id'];

// Get resource details
$stmt = $conn->prepare("SELECT r.*, u.name as uploader_name, u.user_type as uploader_type 
                       FROM resources r 
                       JOIN users u ON r.uploaded_by = u.id 
                       WHERE r.id = ?");
$stmt->bind_param("i", $resource_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    redirect("resources.php");
}

$resource = $result->fetch_assoc();

// Log access if user is logged in
if (is_logged_in()) {
    $user_id = $_SESSION['user_id'];
    
    // Check if already logged
    $check_stmt = $conn->prepare("SELECT id FROM resource_access WHERE user_id = ? AND resource_id = ? AND access_date > DATE_SUB(NOW(), INTERVAL 1 DAY)");
    $check_stmt->bind_param("ii", $user_id, $resource_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows == 0) {
        // Log new access
        $log_stmt = $conn->prepare("INSERT INTO resource_access (user_id, resource_id) VALUES (?, ?)");
        $log_stmt->bind_param("ii", $user_id, $resource_id);
        $log_stmt->execute();
    }
}

include 'header.php';
?>

<div class="row mb-4">
    <div class="col-md-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="../index.php">Home</a></li>
                <li class="breadcrumb-item"><a href="resources.php">Resources</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?php echo $resource['title']; ?></li>
            </ol>
        </nav>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header bg-primary-custom text-white">
                <h4 class="mb-0"><?php echo $resource['title']; ?></h4>
            </div>
            <div class="card-body">
                <div class="mb-4">
                    <h5>Description</h5>
                    <p><?php echo nl2br($resource['description']); ?></p>
                </div>
                
                <?php if ($resource['resource_type'] == 'file' && !empty($resource['file_path'])): ?>
                    <div class="mb-4">
                        <h5>Resource Preview</h5>
                        <?php
                        $file_extension = get_file_extension($resource['file_path']);
                        if (in_array($file_extension, ['jpg', 'jpeg', 'png'])) {
                            // Image preview
                            echo '<div class="text-center mb-3">';
                            echo '<img src="../' . $resource['file_path'] . '" class="img-fluid" alt="' . $resource['title'] . '" style="max-height: 400px;">';
                            echo '</div>';
                        } elseif (in_array($file_extension, ['pdf'])) {
                            // PDF preview
                            echo '<div class="ratio ratio-16x9 mb-3">';
                            echo '<embed src="../' . $resource['file_path'] . '" type="application/pdf">';
                            echo '</div>';
                        } elseif (in_array($file_extension, ['mp4'])) {
                            // Video preview
                            echo '<div class="ratio ratio-16x9 mb-3">';
                            echo '<video controls>';
                            echo '<source src="../' . $resource['file_path'] . '" type="video/mp4">';
                            echo 'Your browser does not support the video tag.';
                            echo '</video>';
                            echo '</div>';
                        } elseif (in_array($file_extension, ['mp3'])) {
                            // Audio preview
                            echo '<div class="mb-3">';
                            echo '<audio controls class="w-100">';
                            echo '<source src="../' . $resource['file_path'] . '" type="audio/mpeg">';
                            echo 'Your browser does not support the audio element.';
                            echo '</audio>';
                            echo '</div>';
                        } else {
                            // No preview available
                            echo '<div class="text-center mb-3">';
                            echo '<p><i class="fas fa-file fa-5x text-primary mb-3"></i></p>';
                            echo '<p>Preview not available for this file type.</p>';
                            echo '</div>';
                        }
                        ?>
                        
                        <div class="text-center">
                            <a href="../<?php echo $resource['file_path']; ?>" class="btn btn-primary" download>
                                <i class="fas fa-download me-2"></i> Download Resource
                            </a>
                        </div>
                    </div>
                <?php elseif ($resource['resource_type'] == 'link' && !empty($resource['external_link'])): ?>
                    <div class="mb-4 text-center">
                        <a href="<?php echo $resource['external_link']; ?>" class="btn btn-primary" target="_blank">
                            <i class="fas fa-external-link-alt me-2"></i> Access Resource
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">Resource Information</h5>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">
                        <strong><i class="fas fa-tag me-2"></i> Category:</strong> 
                        <?php echo ucfirst($resource['category']); ?>
                    </li>
                    <?php if (!empty($resource['target_audience'])): ?>
                    <li class="list-group-item">
                        <strong><i class="fas fa-users me-2"></i> Target Audience:</strong> 
                        <?php echo ucfirst($resource['target_audience']); ?>
                    </li>
                    <?php endif; ?>
                    <li class="list-group-item">
                        <strong><i class="fas fa-user me-2"></i> Uploaded By:</strong> 
                        <?php echo $resource['uploader_name']; ?> (<?php echo ucfirst($resource['uploader_type']); ?>)
                    </li>
                    <li class="list-group-item">
                        <strong><i class="fas fa-calendar-alt me-2"></i> Upload Date:</strong> 
                        <?php echo date('F d, Y', strtotime($resource['upload_date'])); ?>
                    </li>
                    <li class="list-group-item">
                        <strong><i class="fas fa-file me-2"></i> Resource Type:</strong> 
                        <?php echo $resource['resource_type'] == 'file' ? 'Uploaded File' : 'External Link'; ?>
                    </li>
                </ul>
            </div>
        </div>
        
        <?php if (is_logged_in() && ($resource['uploaded_by'] == $_SESSION['user_id'] || get_user_type() == 'admin')): ?>
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">Resource Management</h5>
            </div>
            <div class="card-body">
                <a href="edit_resource.php?id=<?php echo $resource['id']; ?>" class="btn btn-warning mb-2 w-100">
                    <i class="fas fa-edit me-2"></i> Edit Resource
                </a>
                <button type="button" class="btn btn-danger w-100" data-bs-toggle="modal" data-bs-target="#deleteModal">
                    <i class="fas fa-trash-alt me-2"></i> Delete Resource
                </button>
            </div>
        </div>
        
        <!-- Delete Modal -->
        <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteModalLabel">Confirm Deletion</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        Are you sure you want to delete this resource? This action cannot be undone.
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <a href="delete_resource.php?id=<?php echo $resource['id']; ?>" class="btn btn-danger">Delete</a>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'footer.php'; ?>