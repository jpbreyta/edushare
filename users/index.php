<?php
require_once '../auth/auth_functions.php';
require_once '../auth/db_connect.php';

// Check if user is logged in
if (!is_logged_in()) {
    redirect("../auth/login.php");
}

// Get user statistics
$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'];

// Get uploaded resources count (for donors and schools)
$uploaded_count = 0;
if ($user_type == 'donor' || $user_type == 'school' || $user_type == 'admin') {
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM resources WHERE uploaded_by = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $uploaded_count = $row['count'];
    }
}

// Get downloaded/accessed resources count
$accessed_count = 0;
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM resource_access WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $accessed_count = $row['count'];
}

// Get recent resources
$stmt = $conn->prepare("SELECT r.*, u.name as uploader_name, u.user_type as uploader_type 
                       FROM resources r 
                       JOIN users u ON r.uploaded_by = u.id 
                       ORDER BY r.upload_date DESC LIMIT 5");
$stmt->execute();
$recent_resources = $stmt->get_result();

include 'header.php';
?>

<div class="row mb-4">
    <div class="col-md-12">
        <div class="card bg-primary-custom text-white">
            <div class="card-body">
                <h2 class="card-title">Welcome to EduShare, <?php echo $_SESSION['name']; ?>!</h2>
                <p class="card-text">
                    You're logged in as a <?php echo ucfirst($_SESSION['user_type']); ?>. 
                    EduShare connects schools with donors to share educational resources and support quality education.
                </p>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="card text-center h-100">
            <div class="card-body">
                <i class="fas fa-book fa-4x mb-3 text-primary"></i>
                <h5 class="card-title">Educational Resources</h5>
                <p class="card-text">Browse books, e-learning materials, and other educational resources.</p>
                <a href="resources.php" class="btn btn-primary">Browse Resources</a>
            </div>
        </div>
    </div>
    
    <?php if ($user_type == 'donor' || $user_type == 'school' || $user_type == 'admin'): ?>
    <div class="col-md-4">
        <div class="card text-center h-100">
            <div class="card-body">
                <i class="fas fa-upload fa-4x mb-3 text-primary"></i>
                <h5 class="card-title">Share Resources</h5>
                <p class="card-text">Upload educational materials to share with underprivileged schools.</p>
                <a href="upload.php" class="btn btn-primary">Upload Resources</a>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <div class="col-md-<?php echo ($user_type == 'donor' || $user_type == 'school' || $user_type == 'admin') ? '4' : '8'; ?>">
        <div class="card text-center h-100">
            <div class="card-body">
                <i class="fas fa-chart-line fa-4x mb-3 text-primary"></i>
                <h5 class="card-title">Your Activity</h5>
                <?php if ($user_type == 'donor' || $user_type == 'school' || $user_type == 'admin'): ?>
                <p class="card-text">You've uploaded <?php echo $uploaded_count; ?> resources and accessed <?php echo $accessed_count; ?> resources.</p>
                <?php else: ?>
                <p class="card-text">You've accessed <?php echo $accessed_count; ?> educational resources.</p>
                <?php endif; ?>
                <a href="#" class="btn btn-primary">View Activity</a>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary-custom text-white">
                <h5 class="mb-0">Recently Added Resources</h5>
            </div>
            <div class="card-body">
                <?php if ($recent_resources->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Category</th>
                                <th>Uploaded By</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($resource = $recent_resources->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $resource['title']; ?></td>
                                <td><?php echo ucfirst($resource['category']); ?></td>
                                <td><?php echo $resource['uploader_name']; ?> (<?php echo ucfirst($resource['uploader_type']); ?>)</td>
                                <td><?php echo date('M d, Y', strtotime($resource['upload_date'])); ?></td>
                                <td>
                                    <a href="view_resource.php?id=<?php echo $resource['id']; ?>" class="btn btn-sm btn-primary">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <p class="text-center">No resources available yet.</p>
                <?php endif; ?>
                
                <div class="text-center mt-3">
                    <a href="resources.php" class="btn btn-primary">View All Resources</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>