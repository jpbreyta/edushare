<?php
require_once '../auth/auth_functions.php';
require_once '../auth/db_connect.php';

// Check if user has permission to upload
check_permission('any');
if (get_user_type() != 'donor' && get_user_type() != 'school' && get_user_type() != 'admin') {
    redirect("index.php");
}

$error_message = "";
$success_message = "";

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $title = sanitize_input($_POST['title']);
    $description = sanitize_input($_POST['description']);
    $category = sanitize_input($_POST['category']);
    $resource_type = sanitize_input($_POST['resource_type']);
    $target_audience = sanitize_input($_POST['target_audience']);
    
    // Validate input
    if (empty($title) || empty($description) || empty($category) || empty($resource_type)) {
        $error_message = "All fields marked with * are required";
    } else {
        $file_path = "";
        $external_link = "";
        
        // Handle file upload or external link
        if ($resource_type == 'file') {
            if (isset($_FILES['resource_file']) && $_FILES['resource_file']['error'] == 0) {
                $file_name = $_FILES['resource_file']['name'];
                $file_tmp = $_FILES['resource_file']['tmp_name'];
                $file_size = $_FILES['resource_file']['size'];
                
                // Check file type
                if (!is_allowed_file_type($file_name)) {
                    $error_message = "File type not allowed. Please upload PDF, DOC, DOCX, PPT, PPTX, XLS, XLSX, JPG, JPEG, PNG, MP4, or MP3 files.";
                } 
                // Check file size (10MB max)
                elseif ($file_size > 10485760) {
                    $error_message = "File size too large. Maximum file size is 10MB.";
                } else {
                    // Generate unique filename
                    $new_file_name = generate_unique_filename($file_name);
                    $upload_dir = "../uploads/";
                    
                    // Create directory if it doesn't exist
                    if (!file_exists($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }
                    
                    $file_path = $upload_dir . $new_file_name;
                    
                    // Move uploaded file
                    if (move_uploaded_file($file_tmp, $file_path)) {
                        $file_path = "uploads/" . $new_file_name;
                    } else {
                        $error_message = "Error uploading file. Please try again.";
                    }
                }
            } else {
                $error_message = "Please select a file to upload.";
            }
        } else {
            $external_link = sanitize_input($_POST['external_link']);
            if (empty($external_link)) {
                $error_message = "Please provide an external link.";
            } elseif (!filter_var($external_link, FILTER_VALIDATE_URL)) {
                $error_message = "Please provide a valid URL.";
            }
        }
        
        // If no errors, insert into database
        if (empty($error_message)) {
            $user_id = $_SESSION['user_id'];
            
            $stmt = $conn->prepare("INSERT INTO resources (title, description, category, resource_type, file_path, external_link, target_audience, uploaded_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssssi", $title, $description, $category, $resource_type, $file_path, $external_link, $target_audience, $user_id);
            
            if ($stmt->execute()) {
                $success_message = "Resource uploaded successfully!";
            } else {
                $error_message = "Error: " . $stmt->error;
            }
        }
    }
}

include './includes/header.php';
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary-custom text-white">
                <h4 class="mb-0">Upload Educational Resource</h4>
            </div>
            <div class="card-body">
                <?php if (!empty($error_message)): ?>
                    <div class="alert alert-danger"><?php echo $error_message; ?></div>
                <?php endif; ?>
                
                <?php if (!empty($success_message)): ?>
                    <div class="alert alert-success"><?php echo $success_message; ?></div>
                <?php endif; ?>
                
                <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="title" class="form-label">Title *</label>
                        <input type="text" class="form-control" id="title" name="title" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description *</label>
                        <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="category" class="form-label">Category *</label>
                                <select class="form-select" id="category" name="category" required>
                                    <option value="" selected disabled>Select category</option>
                                    <option value="textbook">Textbook</option>
                                    <option value="ebook">E-Book</option>
                                    <option value="video">Video Tutorial</option>
                                    <option value="audio">Audio Material</option>
                                    <option value="presentation">Presentation</option>
                                    <option value="worksheet">Worksheet</option>
                                    <option value="scholarship">Scholarship Information</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="target_audience" class="form-label">Target Audience</label>
                                <select class="form-select" id="target_audience" name="target_audience">
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
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Resource Type *</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="resource_type" id="type_file" value="file" checked>
                            <label class="form-check-label" for="type_file">
                                Upload File
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="resource_type" id="type_link" value="link">
                            <label class="form-check-label" for="type_link">
                                External Link
                            </label>
                        </div>
                    </div>
                    
                    <div id="file_upload_section" class="mb-3">
                        <label for="resource_file" class="form-label">Upload File (Max 10MB)</label>
                        <input class="form-control" type="file" id="resource_file" name="resource_file">
                        <small class="text-muted">Allowed file types: PDF, DOC, DOCX, PPT, PPTX, XLS, XLSX, JPG, JPEG, PNG, MP4, MP3</small>
                    </div>
                    
                    <div id="link_section" class="mb-3" style="display: none;">
                        <label for="external_link" class="form-label">External Link</label>
                        <input type="url" class="form-control" id="external_link" name="external_link" placeholder="https://example.com/resource">
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Upload Resource</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const typeFile = document.getElementById('type_file');
    const typeLink = document.getElementById('type_link');
    const fileSection = document.getElementById('file_upload_section');
    const linkSection = document.getElementById('link_section');
    
    typeFile.addEventListener('change', function() {
        if (this.checked) {
            fileSection.style.display = 'block';
            linkSection.style.display = 'none';
        }
    });
    
    typeLink.addEventListener('change', function() {
        if (this.checked) {
            fileSection.style.display = 'none';
            linkSection.style.display = 'block';
        }
    });
});
</script>

<?php include './includes/footer.php'; ?>