<!-- Sidebar -->
<div class="col-md-3 col-lg-2 d-md-block sidebar collapse">
    <div class="position-sticky pt-3">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo $active_tab == 'dashboard' ? 'active' : ''; ?>" href="index.php">
                    <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $active_tab == 'users' ? 'active' : ''; ?>" href="users.php">
                    <i class="fas fa-users me-2"></i> Users
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $active_tab == 'resources' ? 'active' : ''; ?>" href="resources.php">
                    <i class="fas fa-book me-2"></i> Resources
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $active_tab == 'schools' ? 'active' : ''; ?>" href="schools.php">
                    <i class="fas fa-school me-2"></i> Schools
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $active_tab == 'donations' ? 'active' : ''; ?>" href="donations.php">
                    <i class="fas fa-hand-holding-heart me-2"></i> Donations
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $active_tab == 'inbox' ? 'active' : ''; ?>" href="inbox.php">
                    <i class="fas fa-inbox me-2"></i> Inbox
                </a>
            </li>
            <li class="nav-item mt-3">
                <a class="nav-link" href="#">
                    <i class="fas fa-cog me-2"></i> Settings
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#">
                    <i class="fas fa-question-circle me-2"></i> Help
                </a>
            </li>
        </ul>
    </div>
</div> 