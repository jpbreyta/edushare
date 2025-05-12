<?php
require_once '../includes/functions.php';
require_once '../auth/auth_functions.php';
require_once '../auth/db_connect.php';
require_once '../admin/db_functions.php';

if (!is_logged_in()) {
    redirect_with_message("../auth/login.php", "Please login to contact us", "warning");
}

$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);
    
    if (empty($subject)) {
        redirect_with_message("contact.php", "Please enter a subject", "error");
    } elseif (empty($message)) {
        redirect_with_message("contact.php", "Please enter your message", "error");
    } else {
        // Get the first admin user ID
        $stmt = $conn->prepare("SELECT id FROM users WHERE user_type = 'admin' LIMIT 1");
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $admin = $result->fetch_assoc();
            $admin_id = $admin['id'];
            
            // Insert message directly into the messages table
            $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, subject, body) VALUES (?, ?, ?, ?)");
            if (!$stmt) {
                redirect_with_message("contact.php", "Failed to prepare message statement", "error");
            } else {
                $stmt->bind_param("iiss", $user_id, $admin_id, $subject, $message);
                if ($stmt->execute()) {
                    redirect_with_message("contact.php", "Your message has been sent successfully!", "success");
                } else {
                    redirect_with_message("contact.php", "Failed to send message. Please try again.", "error");
                }
                $stmt->close();
            }
        } else {
            redirect_with_message("contact.php", "No admin user found. Please contact support.", "error");
        }
    }
}

include './includes/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - EduShare</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css">
    <style>
        .contact-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 2rem;
        }
        .contact-info {
            list-style: none;
            padding: 0;
            margin: 2rem 0;
        }
        .contact-info li {
            margin-bottom: 1rem;
            font-size: 1.1rem;
        }
        .contact-info i {
            width: 25px;
            color: #4CAF50;
        }
        form {
            background: #f8f9fa;
            padding: 2rem;
            border-radius: 8px;
        }
        label {
            font-weight: 500;
            margin-bottom: 0.5rem;
        }
        input, textarea {
            width: 100%;
            padding: 0.75rem;
            margin-bottom: 1rem;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        button {
            background-color: #4CAF50;
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
        }
        button:hover {
            background-color: #388E3C;
        }
    </style>
</head>
<body>
    <div class="contact-container">
        <h2>Contact Us</h2>
        <p>Have questions or want to partner with EduShare? We'd love to hear from you.</p>
        
        <ul class="contact-info">
            <li><i class="fas fa-envelope"></i> <strong>Email:</strong> edushare1@gmail.com</li>
            <li><i class="fas fa-phone"></i> <strong>Phone:</strong> +1 (234) 567-890</li>
            <li><i class="fas fa-map-marker-alt"></i> <strong>Address:</strong> 123 Learning Street, Education City, EC 45678</li>
        </ul>

        <form method="POST" action="">
            <div class="mb-3">
                <label for="subject">Subject</label>
                <input type="text" id="subject" name="subject" required placeholder="Enter message subject">
            </div>
            
            <div class="mb-3">
                <label for="message">Your Message</label>
                <textarea id="message" name="message" rows="6" required placeholder="Please type your message here"></textarea>
            </div>

            <button type="submit">Send Message</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>
    <script src="../includes/sweet_alerts.js"></script>
</body>
</html>