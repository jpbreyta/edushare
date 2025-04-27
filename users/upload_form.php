<?php
require_once '../auth/auth_functions.php';
require_once '../auth/db_connect.php';
$success_message = isset($_GET['success']) && !empty($_GET['success']) ? urldecode($_GET['success']) : "";
$error_message = isset($_GET['error']) && !empty($_GET['error']) ? urldecode($_GET['error']) : "";

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Educational Resource - EduShare</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <style>
        body {
            background-color: #f8f9fa;
            padding-top: 2rem;
            padding-bottom: 2rem;
        }
        .form-container {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            padding: 2rem;
            margin-bottom: 2rem;
        }
        .header {
            margin-bottom: 2rem;
        }
        .required-field::after {
            content: "*";
            color: red;
            margin-left: 4px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="header text-center">
                    <h1 class="mb-3">EduShare</h1>
                    <p class="lead">Upload educational resources to share with underprivileged schools</p>
                </div>
            
                <?php if (!empty($success_message)): ?>
                <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i> <?php echo $success_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i> <?php echo $error_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>
                
                <div class="form-container">
                    <h2 class="mb-4">Upload New Resource</h2>
                    
                    <form action="upload.php" method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="title" class="form-label required-field">Title</label>
                            <input type="text" class="form-control" id="title" name="title" required>
                            <div class="form-text">Enter a descriptive title for your resource</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label required-field">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="4" required></textarea>
                            <div class="form-text">Provide details about the resource and how it can be used</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="category" class="form-label required-field">Category</label>
                            <select class="form-select" id="category" name="category" required>
                                <option value="" selected disabled>Select a category</option>
                                <option value="textbook">Textbook</option>
                                <option value="ebook">eBook</option>
                                <option value="presentation">Presentation</option>
                                <option value="worksheet">Worksheet</option>
                            </select>
                        </div>
                        
                        <div class="mb-4">
                            <label for="resource_type" class="form-label required-field">Resource Type</label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="resource_type" id="type_file" value="file" required onclick="toggleResourceFields('file')">
                                <label class="form-check-label" for="type_file">
                                    <i class="bi bi-file-earmark me-2"></i>Upload File
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="resource_type" id="type_link" value="link" required onclick="toggleResourceFields('link')">
                                <label class="form-check-label" for="type_link">
                                    <i class="bi bi-link-45deg me-2"></i>External Link
                                </label>
                            </div>
                        </div>
                        
                        <div class="mb-3 d-none" id="file_input_group">
                            <label for="resource_file" class="form-label required-field">Upload File</label>
                            <input type="file" class="form-control" id="resource_file" name="resource_file" accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.jpg,.jpeg,.png,.mp4,.mp3">
                            <div class="form-text">
                                <i class="bi bi-info-circle me-1"></i> Accepted formats: PDF, Office documents, images, audio, and video<br>
                                <i class="bi bi-exclamation-triangle me-1"></i> Maximum file size: 10MB
                            </div>
                        </div>
                        
                        <div class="mb-3 d-none" id="link_input_group">
                            <label for="external_link" class="form-label required-field">External Link</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-link"></i></span>
                                <input type="url" class="form-control" id="external_link" name="external_link" placeholder="https://example.com/resource">
                            </div>
                            <div class="form-text">Enter the full URL to the external resource</div>
                        </div>
                        
                        <div class="mb-4">
                            <label for="target_audience" class="form-label required-field">Target Audience</label>
                            <select class="form-select" id="target_audience" name="target_audience" required>
                                <option value="" selected disabled>Select department</option>
                                <option value="cics">CICS - College of Information and Computing Sciences</option>
                                <option value="cte">CTE - College of Teacher Education</option>
                                <option value="cit">CIT - College of Industrial Technology</option>
                                <option value="cas">CAS - College of Arts and Sciences</option>
                                <option value="cabe">CABE - College of Architecture and Built Environment</option>
                            </select>
                        </div>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="reset" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle me-2"></i>Clear Form
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-cloud-upload me-2"></i>Upload Resource
                            </button>
                        </div>
                    </form>
                </div>
                
                <div class="text-center">
                    <a href="index.php" class="text-decoration-none">
                        <i class="bi bi-arrow-left me-2"></i>Back to Resources
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function toggleResourceFields(type) {
            const fileGroup = document.getElementById("file_input_group");
            const linkGroup = document.getElementById("link_input_group");
            const fileInput = document.getElementById("resource_file");
            const linkInput = document.getElementById("external_link");
            
            if (type === "file") {
                fileGroup.classList.remove("d-none");
                linkGroup.classList.add("d-none");
                fileInput.setAttribute("required", "");
                linkInput.removeAttribute("required");
            } else if (type === "link") {
                linkGroup.classList.remove("d-none");
                fileGroup.classList.add("d-none");
                linkInput.setAttribute("required", "");
                fileInput.removeAttribute("required");
            }
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                const alerts = document.querySelectorAll('.alert');
                alerts.forEach(function(alert) {
                    const bsAlert = new bootstrap.Alert(alert);
                    setTimeout(function() {
                        bsAlert.close();
                    }, 5000);
                });
            }, 100);
        });
    </script>
</body>
</html>

