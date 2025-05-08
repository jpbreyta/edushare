<?php
require_once '../auth/auth_functions.php';
require_once '../auth/db_connect.php';

if (!is_logged_in()) {
    redirect("../auth/login.php");
}

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("CALL GetUserBookmarks(?)");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$bookmarked_resources = $stmt->get_result();

include './includes/header.php';
?>

<div class="row mb-4">
    <div class="col-md-12">
        <div class="card bg-primary-custom text-white">
            <div class="card-body">
                <h2 class="card-title">My Bookmarked Resources</h2>
                <p class="card-text">
                    View and manage your bookmarked educational resources.
                </p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <?php if ($bookmarked_resources->num_rows > 0): ?>
        <?php while ($resource = $bookmarked_resources->fetch_assoc()): ?>
            <div class="col-md-4 mb-4 bookmark-item" data-resource-id="<?php echo $resource['id']; ?>">
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
                        <button class="btn btn-danger flex-fill remove-bookmark" data-resource-id="<?php echo $resource['id']; ?>">
                            <i class="fas fa-trash"></i> Remove
                        </button>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="col-md-12">
            <div class="alert alert-info text-center">
                You haven't bookmarked any resources yet. Browse our <a href="resources.php" class="alert-link">resources</a> to find materials you'd like to save.
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.remove-bookmark').forEach(button => {
        button.addEventListener('click', function() {
            const resourceId = this.dataset.resourceId;
            const card = this.closest('.col-md-4');
            
            Swal.fire({
                title: 'Remove Bookmark?',
                text: "Are you sure you want to remove this bookmark?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, remove it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('bookmark.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `resource_id=${resourceId}&action=remove`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            card.style.transition = 'all 0.3s ease';
                            card.style.opacity = '0';
                            card.style.transform = 'scale(0.8)';
                            
                            setTimeout(() => {
                                card.remove();
                                
                                if (document.querySelectorAll('.col-md-4').length === 0) {
                                    const container = document.querySelector('.row');
                                    container.innerHTML = `
                                        <div class="col-md-12">
                                            <div class="alert alert-info text-center">
                                                You haven't bookmarked any resources yet. 
                                                <a href="resources.php" class="alert-link">Browse resources</a> to find something interesting!
                                            </div>
                                        </div>
                                    `;
                                }
                            }, 300);

                            Swal.fire({
                                icon: 'success',
                                title: 'Removed!',
                                text: data.message,
                                timer: 2000,
                                showConfirmButton: false
                            });
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
                            text: 'Failed to remove bookmark. Please try again.'
                        });
                    });
                }
            });
        });
    });
});
</script>

<?php include './includes/footer.php'; ?> 