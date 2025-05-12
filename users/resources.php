<?php
require_once '../auth/auth_functions.php';
require_once '../auth/db_connect.php';

// Check if user is logged in
if (!is_logged_in()) {
    redirect_with_message('../auth/login.php', 'Please log in to access resources.', 'warning');
}

$category = isset($_GET['category']) ? sanitize_input($_GET['category']) : '';
$audience = isset($_GET['audience']) ? sanitize_input($_GET['audience']) : '';
$search = isset($_GET['search']) ? sanitize_input($_GET['search']) : '';

$category = $category !== '' ? $category : null;
$audience = $audience !== '' ? $audience : null;
$search = $search !== '' ? $search : null;

$stmt = $conn->prepare("CALL GetFilteredResources(?, ?, ?)");
$stmt->bind_param("sss", $category, $audience, $search);
$stmt->execute();
$resources = $stmt->get_result();
$stmt->close();
$conn->next_result(); // Clear the result set

// Get user's bookmarks if logged in
$bookmarked_resources = [];
if (is_logged_in()) {
    $user_id = $_SESSION['user_id'];
    $bookmark_stmt = $conn->prepare("SELECT resource_id FROM bookmarks WHERE user_id = ?");
    $bookmark_stmt->bind_param("i", $user_id);
    $bookmark_stmt->execute();
    $bookmark_result = $bookmark_stmt->get_result();
    while ($row = $bookmark_result->fetch_assoc()) {
        $bookmarked_resources[] = $row['resource_id'];
    }
    $bookmark_stmt->close();
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
                    <div class="col-md-2 d-flex align-items-start gap-2">
                        <button type="submit" class="btn btn-primary flex-fill">Filter</button>
                        <a href="upload_form.php" class="btn btn-success flex-fill">Upload</a>
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
                    <div class="card-footer d-flex justify-content-between gap-2">
                        <a href="view_resource.php?id=<?php echo $resource['id']; ?>" class="btn btn-primary flex-fill">
                            <i class="fas fa-eye"></i> View Details
                        </a>
                        <?php if (is_logged_in()): ?>
                            <button class="btn <?php echo in_array($resource['id'], $bookmarked_resources) ? 'btn-secondary' : 'btn-outline-secondary'; ?> flex-fill bookmark-btn" 
                                    data-resource-id="<?php echo $resource['id']; ?>" 
                                    title="<?php echo in_array($resource['id'], $bookmarked_resources) ? 'Remove bookmark' : 'Bookmark this resource'; ?>">
                                <i class="fas fa-bookmark"></i>
                            </button>
                        <?php endif; ?>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const bookmarkButtons = document.querySelectorAll('.bookmark-btn');
    
    bookmarkButtons.forEach(button => {
        button.addEventListener('click', function() {
            const resourceId = this.dataset.resourceId;
            const isBookmarked = this.classList.contains('btn-success');
            const action = isBookmarked ? 'remove' : 'add';
            
            fetch('bookmark.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `resource_id=${resourceId}&action=${action}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (action === 'add') {
                        this.classList.remove('btn-outline-success');
                        this.classList.add('btn-success');
                        this.title = 'Remove from Bookmarks';
                        Swal.fire({
                            icon: 'success',
                            title: 'Bookmarked!',
                            text: data.message,
                            timer: 2000,
                            showConfirmButton: false
                        });
                    } else {
                        this.classList.remove('btn-success');
                        this.classList.add('btn-outline-success');
                        this.title = 'Add to Bookmarks';
                        Swal.fire({
                            icon: 'info',
                            title: 'Removed!',
                            text: data.message,
                            timer: 2000,
                            showConfirmButton: false
                        });
                    }
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: data.message
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Failed to process your request. Please try again.'
                });
            });
        });
    });
});
</script>