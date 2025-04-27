<?php
require_once '../auth/auth_functions.php';
require_once '../auth/db_connect.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    redirect("resources.php");
}

$resource_id = $_GET['id'];

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

if (is_logged_in()) {
    $user_id = $_SESSION['user_id'];
    
    $check_stmt = $conn->prepare("SELECT id FROM resource_access WHERE user_id = ? AND resource_id = ? AND access_date > DATE_SUB(NOW(), INTERVAL 1 DAY)");
    $check_stmt->bind_param("ii", $user_id, $resource_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows == 0) {
        $log_stmt = $conn->prepare("INSERT INTO resource_access (user_id, resource_id) VALUES (?, ?)");
        $log_stmt->bind_param("ii", $user_id, $resource_id);
        $log_stmt->execute();
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $resource['title']; ?> - EduShare</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
</head>
<body>
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <a href="javascript:history.back()" class="btn btn-secondary back-btn">
                    <i class="fas fa-arrow-left me-2"></i> Back
                </a>
            </div>
        </div>

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
                        <div class="resource-description">
                            <h5 class="mb-3">Description</h5>
                            <p class="mb-0"><?php echo nl2br($resource['description']); ?></p>
                        </div>
                        
                        <?php if ($resource['resource_type'] == 'file' && !empty($resource['file_path'])): ?>
                            <div class="resource-preview">
                                <h5 class="mb-3">Resource Preview</h5>
                                <?php
                                $file_extension = get_file_extension($resource['file_path']);
                                if (in_array($file_extension, ['jpg', 'jpeg', 'png'])) {
                                    echo '<div class="preview-container text-center mb-3">';
                                    echo '<img src="../' . $resource['file_path'] . '" class="img-fluid" alt="' . $resource['title'] . '" style="max-height: 500px;">';
                                    echo '</div>';
                                } elseif (in_array($file_extension, ['pdf'])) {
                                    echo '<div class="preview-container embed-responsive mb-3" style="height: 600px;">';
                                    echo '<embed src="../' . $resource['file_path'] . '" type="application/pdf" width="100%" height="100%">';
                                    echo '</div>';
                                } else {
                                    echo '<div class="text-center p-5 mb-3 preview-container">';
                                    echo '<i class="fas fa-file file-icon"></i>';
                                    echo '<p class="mb-0">Preview not available for this file type.</p>';
                                    echo '</div>';
                                }
                                ?>
                                
                                <div class="text-center">
                                    <a href="../<?php echo $resource['file_path']; ?>" class="btn btn-primary download-btn" download>
                                        <i class="fas fa-download me-2"></i> Download Resource
                                    </a>
                                </div>
                            </div>
                        <?php elseif ($resource['resource_type'] == 'link' && !empty($resource['external_link'])): ?>
                            <div class="resource-preview text-center p-5">
                                <i class="fas fa-link file-icon"></i>
                                <p class="mb-4">This resource is available as an external link.</p>
                                <a href="<?php echo $resource['external_link']; ?>" class="btn btn-primary download-btn" target="_blank">
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
                        <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Resource Information</h5>
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item">
                                <strong><i class="fas fa-tag me-2"></i> Category:</strong> 
                                <span class="badge bg-primary rounded-pill ms-2"><?php echo ucfirst($resource['category']); ?></span>
                            </li>
                            <?php if (!empty($resource['target_audience'])): ?>
                            <li class="list-group-item">
                                <strong><i class="fas fa-users me-2"></i> Target Audience:</strong> 
                                <span class="badge bg-info rounded-pill ms-2"><?php echo ucfirst($resource['target_audience']); ?></span>
                            </li>
                            <?php endif; ?>
                            <li class="list-group-item">
                                <strong><i class="fas fa-user me-2"></i> Uploaded By:</strong> 
                                <div class="mt-1"><?php echo $resource['uploader_name']; ?> <span class="badge bg-secondary"><?php echo ucfirst($resource['uploader_type']); ?></span></div>
                            </li>
                            <li class="list-group-item">
                                <strong><i class="fas fa-calendar-alt me-2"></i> Upload Date:</strong> 
                                <div class="mt-1"><?php echo date('F d, Y', strtotime($resource['upload_date'])); ?></div>
                            </li>
                            <li class="list-group-item">
                                <strong><i class="fas fa-file me-2"></i> Resource Type:</strong> 
                                <div class="mt-1">
                                    <?php if ($resource['resource_type'] == 'file'): ?>
                                        <span class="badge bg-success"><i class="fas fa-file-alt me-1"></i> Uploaded File</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning"><i class="fas fa-link me-1"></i> External Link</span>
                                    <?php endif; ?>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
                
                <div class="d-block d-md-none mobile-back-btn">
                    <a href="javascript:history.back()" class="btn btn-secondary w-100">
                        <i class="fas fa-arrow-left me-2"></i> Back to Previous Page
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>