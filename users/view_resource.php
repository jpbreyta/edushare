<?php
require_once '../auth/auth_functions.php';
require_once '../auth/db_connect.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    redirect("resources.php");
}

$resource_id = $_GET['id'];
$stmt = $conn->prepare("CALL GetResourceDetails(?)");
$stmt->bind_param("i", $resource_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    redirect("resources.php");
}

$resource = $result->fetch_assoc();
$stmt->close(); 
$conn->next_result(); 

if (is_logged_in()) {
    $user_id = $_SESSION['user_id'];
    $check_stmt = $conn->prepare("CALL CheckRecentResourceAccess(?, ?)");
    $check_stmt->bind_param("ii", $user_id, $resource_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows == 0) {
        $check_stmt->close();
        $conn->next_result(); 

        $log_stmt = $conn->prepare("CALL LogResourceAccess(?, ?)");
        $log_stmt->bind_param("ii", $user_id, $resource_id);
        $log_stmt->execute();
        $log_stmt->close();
        $conn->next_result(); 
    } else {
        $check_stmt->close();
        $conn->next_result();
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
    <style>
        :root {
            --primary-color: #4e73df;
            --primary-dark: #3a56b7;
            --secondary-color: #858796;
            --success-color: #1cc88a;
            --info-color: #36b9cc;
            --warning-color: #f6c23e;
            --danger-color: #e74a3b;
            --light-color: #f8f9fc;
            --dark-color: #5a5c69;
        }
        
        body {
            background-color: #f8f9fc;
            color: #444;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            padding-top: 20px;
            padding-bottom: 40px;
        }
        
        .container {
            max-width: 1200px;
            padding: 0 20px;
        }
        
        /* Card styling */
        .card {
            border: none;
            border-radius: 8px;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            margin-bottom: 25px;
            transition: all 0.3s ease;
        }
        
        .card:hover {
            box-shadow: 0 0.5rem 2rem 0 rgba(58, 59, 69, 0.2);
        }
        
        .card-header {
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            padding: 1rem 1.25rem;
        }
        
        .card-header.bg-primary-custom {
            background-color: var(--primary-color);
            color: white;
            border-radius: 8px 8px 0 0;
        }
        
        .card-body {
            padding: 1.5rem;
        }
        
        /* Breadcrumb styling */
        .breadcrumb {
            background-color: transparent;
            padding: 0.75rem 0;
            margin-bottom: 1.5rem;
        }
        
        .breadcrumb-item a {
            color: var(--primary-color);
            text-decoration: none;
            transition: color 0.2s;
        }
        
        .breadcrumb-item a:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }
        
        /* Button styling */
        .btn {
            border-radius: 6px;
            padding: 0.5rem 1.25rem;
            font-weight: 500;
            transition: all 0.2s;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
        }
        
        .btn-secondary {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }
        
        .btn-secondary:hover {
            background-color: #717484;
            border-color: #717484;
        }
        
        /* Back button styling */
        .back-btn {
            margin-bottom: 1.5rem;
            display: inline-block;
        }
        
        /* Resource preview styling */
        .resource-preview {
            background-color: #f8f9fc;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .preview-container {
            border: 1px solid rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 1.5rem;
        }
        
        /* File icon styling */
        .file-icon {
            color: var(--primary-color);
            font-size: 4rem;
            margin-bottom: 1rem;
        }
        
        /* List group styling */
        .list-group-item {
            padding: 0.75rem 1.25rem;
            border: none;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }
        
        .list-group-item:last-child {
            border-bottom: none;
        }
        
        .list-group-item i {
            color: var(--primary-color);
            width: 20px;
            text-align: center;
        }
        
        /* Description styling */
        .resource-description {
            background-color: #fff;
            border-radius: 8px;
            padding: 1.25rem;
            margin-bottom: 1.5rem;
            border-left: 4px solid var(--primary-color);
        }
        
        /* Media preview responsiveness */
        .embed-responsive {
            position: relative;
            width: 100%;
            overflow: hidden;
        }
        
        .embed-responsive::before {
            content: "";
            display: block;
            padding-top: 56.25%; /* 16:9 aspect ratio */
        }
        
        .embed-responsive embed,
        .embed-responsive iframe,
        .embed-responsive object,
        .embed-responsive video {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border: 0;
        }
        
        /* Audio player styling */
        audio {
            width: 100%;
            border-radius: 30px;
            outline: none;
        }
        
        /* Download button styling */
        .download-btn {
            margin-top: 1.5rem;
            padding: 0.75rem 2rem;
            font-weight: 600;
            letter-spacing: 0.5px;
        }
        
        /* Mobile optimizations */
        @media (max-width: 767.98px) {
            .card-header h4 {
                font-size: 1.25rem;
            }
            
            .resource-preview {
                padding: 1rem;
            }
            
            .mobile-back-btn {
                margin-top: 1.5rem;
                margin-bottom: 2rem;
            }
        }
    </style>
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