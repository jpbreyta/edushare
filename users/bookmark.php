<?php
require_once '../auth/auth_functions.php';
require_once '../auth/db_connect.php';

// Check if user is logged in
if (!is_logged_in()) {
    echo json_encode([
        'success' => false,
        'message' => 'Please log in to bookmark resources'
    ]);
    exit;
}

// Validate resource_id
if (!isset($_POST['resource_id']) || !is_numeric($_POST['resource_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid resource ID'
    ]);
    exit;
}

$user_id = $_SESSION['user_id'];
$resource_id = (int)$_POST['resource_id'];
$action = isset($_POST['action']) ? $_POST['action'] : 'toggle';

try {
    if ($action === 'add') {
        // Check if bookmark already exists
        $check_stmt = $conn->prepare("SELECT id FROM bookmarks WHERE user_id = ? AND resource_id = ?");
        $check_stmt->bind_param("ii", $user_id, $resource_id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        
        if ($result->num_rows > 0) {
            echo json_encode([
                'success' => false,
                'message' => 'Resource is already bookmarked'
            ]);
            exit;
        }
        
        // Add new bookmark
        $stmt = $conn->prepare("INSERT INTO bookmarks (user_id, resource_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $user_id, $resource_id);
        
        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Resource bookmarked successfully'
            ]);
        } else {
            throw new Exception("Failed to add bookmark");
        }
        
    } elseif ($action === 'remove') {
        // Check if bookmark exists before removing
        $check_stmt = $conn->prepare("SELECT id FROM bookmarks WHERE user_id = ? AND resource_id = ?");
        $check_stmt->bind_param("ii", $user_id, $resource_id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        
        if ($result->num_rows === 0) {
            echo json_encode([
                'success' => false,
                'message' => 'Bookmark not found'
            ]);
            exit;
        }
        
        // Remove bookmark
        $stmt = $conn->prepare("DELETE FROM bookmarks WHERE user_id = ? AND resource_id = ?");
        $stmt->bind_param("ii", $user_id, $resource_id);
        
        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Bookmark removed successfully'
            ]);
        } else {
            throw new Exception("Failed to remove bookmark");
        }
        
    } else {
        // Toggle bookmark
        $check_stmt = $conn->prepare("SELECT id FROM bookmarks WHERE user_id = ? AND resource_id = ?");
        $check_stmt->bind_param("ii", $user_id, $resource_id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        
        if ($result->num_rows > 0) {
            // Remove bookmark
            $stmt = $conn->prepare("DELETE FROM bookmarks WHERE user_id = ? AND resource_id = ?");
            $stmt->bind_param("ii", $user_id, $resource_id);
            $action = 'remove';
        } else {
            // Add bookmark
            $stmt = $conn->prepare("INSERT INTO bookmarks (user_id, resource_id) VALUES (?, ?)");
            $stmt->bind_param("ii", $user_id, $resource_id);
            $action = 'add';
        }
        
        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => $action === 'add' ? 'Resource bookmarked successfully' : 'Bookmark removed successfully'
            ]);
        } else {
            throw new Exception("Failed to " . ($action === 'add' ? 'add' : 'remove') . " bookmark");
        }
    }
    
} catch (Exception $e) {
    error_log("Bookmark error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while processing your request'
    ]);
}

// Close statements
if (isset($check_stmt)) $check_stmt->close();
if (isset($stmt)) $stmt->close();
$conn->close();
?> 