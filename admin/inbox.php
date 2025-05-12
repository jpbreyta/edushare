<?php
require_once '../auth/config.php';
require_once '../auth/db_connect.php';
require_once 'db_functions.php';

// Check if session is not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit();
}

$active_tab = 'inbox';
$message = '';
$error = '';

// Handle message deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $delete_id = $_POST['delete_id'];
    $user_id = $_SESSION['user_id'];
    
    // Call the DeleteMessage stored procedure
    $stmt = $conn->prepare("DELETE FROM messages WHERE id = ?");
    if (!$stmt) {
        $error = "Failed to prepare delete statement.";
    } else {
        $stmt->bind_param("i", $delete_id);
        if ($stmt->execute()) {
            $message = "Message deleted successfully.";
        } else {
            $error = "Failed to delete message.";
        }
        $stmt->close();
    }
}

// Get all admin user IDs
$admin_ids = [];
$result = $conn->query("SELECT id FROM users WHERE user_type = 'admin'");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $admin_ids[] = $row['id'];
    }
}

$messages = [];
if (!empty($admin_ids)) {
    // Prepare a statement to get all messages sent to any admin
    $in = implode(',', array_fill(0, count($admin_ids), '?'));
    $types = str_repeat('i', count($admin_ids));
    $stmt = $conn->prepare("SELECT m.id, m.subject, m.body, m.sent_at, u.name as sender FROM messages m JOIN users u ON m.sender_id = u.id WHERE m.receiver_id IN ($in) ORDER BY m.sent_at DESC");
    if ($stmt) {
        $stmt->bind_param($types, ...$admin_ids);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $messages[] = $row;
        }
        $stmt->close();
    } else {
        $error = "Failed to prepare inbox query.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Inbox - EduShare</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .message-card {
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .message-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        .message-content {
            font-size: 16px;
            line-height: 1.6;
            color: #333;
            display: none; /* Hide by default */
        }
        .message-meta {
            font-size: 14px;
            color: #666;
            margin-top: 10px;
        }
        .delete-btn {
            color: #dc3545;
            background: none;
            border: none;
            padding: 5px 10px;
            cursor: pointer;
            transition: color 0.3s;
        }
        .delete-btn:hover {
            color: #c82333;
        }
        .message-subject {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 10px;
            color: #333;
        }
        .view-btn {
            color: #007bff;
            background: none;
            border: none;
            padding: 5px 10px;
            cursor: pointer;
            transition: color 0.3s;
        }
        .view-btn:hover {
            color: #0056b3;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container-fluid">
        <div class="row">
            <?php include 'includes/sidebar.php'; ?>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Inbox</h1>
                </div>

                <?php if ($message): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo htmlspecialchars($message); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo htmlspecialchars($error); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <?php if (empty($messages)): ?>
                    <div class="alert alert-info">
                        No messages in your inbox.
                    </div>
                <?php else: ?>
                    <?php foreach ($messages as $msg): ?>
                        <div class="message-card">
                            <div class="message-header">
                                <div>
                                    <div class="message-subject">
                                        <?php echo htmlspecialchars($msg['subject']); ?>
                                    </div>
                                    <div>
                                        <strong>From:</strong> <?php echo htmlspecialchars($msg['sender']); ?>
                                    </div>
                                </div>
                                <div>
                                    <button type="button" class="view-btn" onclick="toggleMessage(<?php echo $msg['id']; ?>)">
                                        <i class="bi bi-eye"></i> View Message
                                    </button>
                                    <form method="POST" style="display: inline;" onsubmit="return confirmDelete(event, <?php echo $msg['id']; ?>);">
                                        <input type="hidden" name="delete_id" value="<?php echo $msg['id']; ?>">
                                        <button type="submit" class="delete-btn">
                                            <i class="bi bi-trash"></i> Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                            <div id="message-<?php echo $msg['id']; ?>" class="message-content">
                                <?php echo nl2br(htmlspecialchars($msg['body'])); ?>
                            </div>
                            <div class="message-meta">
                                <i class="bi bi-clock"></i> Sent on: <?php echo date('F j, Y g:i A', strtotime($msg['sent_at'])); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function toggleMessage(id) {
        var content = document.getElementById('message-' + id);
        if (content.style.display === 'block') {
            content.style.display = 'none';
        } else {
            content.style.display = 'block';
        }
    }

    function confirmDelete(event, id) {
        event.preventDefault();
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                // Find the form that contains the delete button for this message
                var form = event.target.closest('form');
                form.submit();
            }
        });
    }
    </script>
</body>
</html>
