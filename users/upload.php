<?php
require_once '../auth/auth_functions.php';
require_once '../auth/db_connect.php';

// Check if user has permission to upload

$error_message = "";
$success_message = "";

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize form inputs
    $title = sanitizeInput($_POST['title']);
    $description = sanitizeInput($_POST['description']);
    $category = sanitizeInput($_POST['category']);
    $resource_type = sanitizeInput($_POST['resource_type']);
    $target_audience = sanitizeInput($_POST['target_audience']);

    // Validate required fields
    if (empty($title) || empty($description) || empty($category) || empty($resource_type) || empty($target_audience)) {
        $error_message = "All fields marked with * are required.";
    } else {
        $file_path = null;
        $external_link = null;

        if ($resource_type === 'file') {
            if (isset($_FILES['resource_file']) && $_FILES['resource_file']['error'] === 0) {
                $file_name = $_FILES['resource_file']['name'];
                $file_tmp = $_FILES['resource_file']['tmp_name'];
                $file_size = $_FILES['resource_file']['size'];

                // File validation
                if (!isAllowedFileType($file_name)) {
                    $error_message = "Unsupported file type.";
                } elseif ($file_size > 10485760) {
                    $error_message = "File too large. Max size is 10MB.";
                } else {
                    $new_file_name = generateUniqueFilename($file_name);
                    $upload_dir = "../uploads/";

                    if (!file_exists($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }

                    $file_path = $upload_dir . $new_file_name;

                    if (move_uploaded_file($file_tmp, $file_path)) {
                        $file_path = "uploads/" . $new_file_name;
                    } else {
                        $error_message = "File upload failed.";
                    }
                }
            } else {
                $error_message = "No file selected for upload.";
            }
        } elseif ($resource_type === 'link') {
            $external_link = sanitizeInput($_POST['external_link']);
            if (empty($external_link) || !filter_var($external_link, FILTER_VALIDATE_URL)) {
                $error_message = "Valid external link required.";
            }
        }

        // Insert into DB if no errors
        if (empty($error_message)) {
            $user_id = $_SESSION['user_id'];
            $status = 'pending'; // Default status for new resources
            $is_visible = true; // Default visibility for new resources

            $stmt = $conn->prepare("INSERT INTO resources (title, description, category, resource_type, file_path, external_link, target_audience, uploaded_by, status, is_visible) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssssisi", $title, $description, $category, $resource_type, $file_path, $external_link, $target_audience, $user_id, $status, $is_visible);

            if ($stmt->execute()) {
                $success_message = "Resource uploaded successfully!";
            } else {
                $error_message = "Database error: " . $stmt->error;
            }
        }
    }
}

// Redirect back to the page with the form
header("Location: upload_form.php?success=" . urlencode($success_message) . "&error=" . urlencode($error_message));
exit;
?>