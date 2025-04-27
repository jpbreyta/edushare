
<?php
require_once '../auth/auth_functions.php';
require_once '../auth/db_connect.php';

$category = isset($_GET['category']) ? sanitize_input($_GET['category']) : '';
$audience = isset($_GET['audience']) ? sanitize_input($_GET['audience']) : '';
$search = isset($_GET['search']) ? sanitize_input($_GET['search']) : '';

$query = "SELECT r.*, u.name as uploader_name, u.user_type as uploader_type 
          FROM resources r 
          JOIN users u ON r.uploaded_by = u.id 
          WHERE r.is_visible = 1";
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

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$resources = $stmt->get_result();

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
                            <option value="presentation" <?php echo $category == 'presentation' ? 'selected' : ''; ?>>Presentation</option>
                            <option value="worksheet" <?php echo $category == 'worksheet' ? 'selected' : ''; ?>>Worksheet</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" name="audience">
                            <option value="">All Audiences</option>
                            <option value="cics" <?php echo $audience == 'cics' ? 'selected' : ''; ?>>CICS</option>
                            <option value="cte" <?php echo $audience == 'cte' ? 'selected' : ''; ?>>CTE</option>
                            <option value="cit" <?php echo $audience == 'cit' ? 'selected' : ''; ?>>CIT</option>
                            <option value="cas" <?php echo $audience == 'cas' ? 'selected' : ''; ?>>CAS</option>
                            <option value="cabe" <?php echo $audience == 'cabe' ? 'selected' : ''; ?>>CABE</option>
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
                        } elseif ($resource['category'] == 'presentation') {
                            $icon_class = 'fa-file-powerpoint';
                        } elseif ($resource['category'] == 'worksheet') {
                            $icon_class = 'fa-file-alt';
                        }
                        ?>
                        <i class="fas <?php echo $icon_class; ?> me-2"></i>
                        <span class="badge bg-primary"><?php echo ucfirst($resource['category']); ?></span>
                        <?php if (!empty($resource['target_audience'])): ?>
                            <span class="badge bg-secondary"><?php echo strtoupper($resource['target_audience']); ?></span>
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
                No resources found matching your criteria. Please try different filters or <a href="upload_form.php" class="alert-link">upload a resource</a>.
            </div>
        </div>
    <?php endif; ?>
</div>