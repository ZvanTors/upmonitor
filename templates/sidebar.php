<!-- Sidebar -->
<nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-body-tertiary sidebar collapse">
    <div class="position-sticky pt-3 d-flex flex-column h-100">
        <!-- منوی اصلی -->
        <ul class="nav flex-column flex-grow-1">
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>" href="dashboard.php">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'monitors.php' ? 'active' : ''; ?>" href="monitors.php">
                    <i class="fas fa-server"></i> Monitors
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'logs.php' ? 'active' : ''; ?>" href="logs.php">
                    <i class="fas fa-history"></i> Logs
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="donate.php">
                    <i class="fas fa-heart text-danger"></i> Support Us
                </a>
            </li>
        </ul>

        <!-- امضای پایین سایدبار -->
        <div class="sidebar-footer mt-auto p-3 text-center">
            <small class="text-muted">
                Made with <i class="fas fa-heart text-danger"></i> by <strong>AmooReza</strong>
            </small>
        </div>
    </div>
</nav>