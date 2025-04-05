<?php
require_once '../auth/auth_functions.php';
require_once '../auth/db_connect.php';

// Get filter parameters
$category = isset($_GET['category']) ? sanitize_input($_GET['category']) : '';
$audience = isset($_GET['audience']) ? sanitize_input($_GET['audience']) : '';
$search = isset($_GET['search']) ? sanitize_input($_GET['search']) : '';

// Build query
$query = "SELECT r.*, u.name as uploader_name, u.user_type as uploader_type 
          FROM resources r 
          JOIN users u ON r.uploaded_by = u.id 
          WHERE 1=1";
$params = [];
$types = "";

if (!empty($category)) {
    $query .= " AND r.category = ?";
    $params[] = $category;
    $types .= "s";
}

if (!empty($audience)) {
    $query .= " AND r.target_audience = ?";
    $params[] = $audience;
    $types .= "s";
}

if (!empty($search)) {
    $query .= " AND (r.title LIKE ? OR r.description LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= "ss";
}

$query .= " ORDER BY r.upload_date DESC";

// Prepare and execute query
$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$resources = $stmt->get_result();

// Log access if user is logged in
if (is_logged_in() && isset($_GET['view']) && is_numeric($_GET['view'])) {
    $resource_id = $_GET['view'];
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

include './includes/header.php';
?>

<div class="row mb-4">
    <div class="col-md-12">
        <div class="card bg-primary-custom text-white">
            <div class="card-body">
                <h2 class="card-title">Educational Resources</h2>
                <p class="card-text">
                    Browse our collection of educational materials shared by donors and schools. 
                    These resources are designed to support quality education for underprivileged schools.
                </p>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <form method="GET" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="row g-3">
                    <div class="col-md-4">
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                            <input type="text" class="form-control" placeholder="Search resources..." name="search" value="<?php echo $search; ?>">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" name="category">
                            <option value="">All Categories</option>
                            <option value="textbook" <?php echo $category == 'textbook' ? 'selected' : ''; ?>>Textbook</option>
                            <option value="ebook" <?php echo $category == 'ebook' ? 'selected' : ''; ?>>E-Book</option>
                            <option value="video" <?php echo $category == 'video' ? 'selected' : ''; ?>>Video Tutorial</option>
                            <option value="audio" <?php echo $category == 'audio' ? 'selected' : ''; ?>>Audio Material</option>
                            <option value="presentation" <?php echo $category == 'presentation' ? 'selected' : ''; ?>>Presentation</option>
                            <option value="worksheet" <?php echo $category == 'worksheet' ? 'selected' : ''; ?>>Worksheet</option>
                            <option value="scholarship" <?php echo $category == 'scholarship' ? 'selected' : ''; ?>>Scholarship</option>
                            <option value="other" <?php echo $category == 'other' ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" name="audience">
                            <option value="">All Audiences</option>
                            <option value="elementary" <?php echo $audience == 'elementary' ? 'selected' : ''; ?>>Elementary School</option>
                            <option value="middle" <?php echo $audience == 'middle' ? 'selected' : ''; ?>>Middle School</option>
                            <option value="high" <?php echo $audience == 'high' ? 'selected' : ''; ?>>High School</option>
                            <option value="college" <?php echo $audience == 'college' ? 'selected' : ''; ?>>College/University</option>
                            <option value="teacher" <?php echo $audience == 'teacher' ? 'selected' : ''; ?>>Teachers</option>
                            <option value="all" <?php echo $audience == 'all' ? 'selected' : ''; ?>>All Levels</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <?php if ($resources->num_rows > 0): ?>
        <?php while ($resource = $resources->fetch_assoc()): ?>
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-header bg-light">
                        <?php 
                        $icon_class = 'fa-file-alt';
                        if ($resource['category'] == 'textbook' || $resource['category'] == 'ebook') {
                            $icon_class = 'fa-book';
                        } elseif ($resource['category'] == 'video') {
                            $icon_class = 'fa-video';
                        } elseif ($resource['category'] == 'audio') {
                            $icon_class = 'fa-headphones';
                        } elseif ($resource['category'] == 'presentation') {
                            $icon_class = 'fa-file-powerpoint';
                        } elseif ($resource['category'] == 'worksheet') {
                            $icon_class = 'fa-file-alt';
                        } elseif ($resource['category'] == 'scholarship') {
                            $icon_class = 'fa-graduation-cap';
                        }
                        ?>
                        <i class="fas <?php echo $icon_class; ?> me-2"></i>
                        <span class="badge bg-primary"><?php echo ucfirst($resource['category']); ?></span>
                        <?php if (!empty($resource['target_audience'])): ?>
                            <span class="badge bg-secondary"><?php echo ucfirst($resource['target_audience']); ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $resource['title']; ?></h5>
                        <p class="card-text"><?php echo substr($resource['description'], 0, 100); ?><?php echo strlen($resource['description']) > 100 ? '...' : ''; ?></p>
                        <p class="card-text"><small class="text-muted">
                            Uploaded by <?php echo $resource['uploader_name']; ?> (<?php echo ucfirst($resource['uploader_type']); ?>)<br>
                            on <?php echo date('M d, Y', strtotime($resource['upload_date'])); ?>
                        </small></p>
                    </div>
                    <div class="card-footer">
                        <a href="view_resource.php?id=<?php echo $resource['id']; ?>" class="btn btn-primary">
                            <i class="fas fa-eye"></i> View Details
                        </a>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="col-md-12">
            <div class="alert alert-info text-center">
                No resources found matching your criteria. Please try different filters or <a href="upload.php" class="alert-link">upload a resource</a>.
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include './includes/footer.php'; ?>