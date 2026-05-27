<?php
// index.php - Landing Page
define('PAGE_TITLE', 'UpMonitor - Reliable Uptime Monitoring');
require_once 'includes/config.php';
require_once 'includes/auth.php';
// بدون نیاز به لاگین، همه می‌توانند ببینند
?>
<?php include 'templates/header.php'; ?>

<!-- Hero Section -->
<div class="container-fluid px-0">
    <div class="row align-items-center min-vh-75" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff;">
        <div class="col-lg-6 mx-auto text-center py-5">
            <h1 class="display-3 fw-bold mb-3">Monitor Everything. <br class="d-none d-md-block">Stay in Control.</h1>
            <p class="lead mb-4">Track uptime for websites, servers, and ports with a beautiful, modern dashboard. Get real-time status and historical uptime logs.</p>
            <div class="d-flex justify-content-center gap-3 flex-wrap">
                <?php if (!isLoggedIn()): ?>
                    <a href="register.php" class="btn btn-light btn-lg px-4 py-2 shadow">Get Started Free</a>
                    <a href="login.php" class="btn btn-outline-light btn-lg px-4 py-2">Login</a>
                <?php else: ?>
                    <a href="dashboard.php" class="btn btn-light btn-lg px-4 py-2 shadow">Go to Dashboard</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Features Section -->
<div class="container py-5">
    <div class="row text-center mb-5">
        <div class="col">
            <h2 class="fw-bold">Why UpMonitor?</h2>
            <p class="text-muted">Simple, powerful, and open-source monitoring for everyone.</p>
        </div>
    </div>
    <div class="row g-4">
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center p-4">
                    <div class="rounded-circle bg-primary bg-opacity-10 p-3 d-inline-flex mb-3">
                        <i class="fas fa-globe fa-2x text-primary"></i>
                    </div>
                    <h4>HTTP/HTTPS</h4>
                    <p class="text-muted">Monitor your websites and APIs with simple URL checks.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center p-4">
                    <div class="rounded-circle bg-success bg-opacity-10 p-3 d-inline-flex mb-3">
                        <i class="fas fa-network-wired fa-2x text-success"></i>
                    </div>
                    <h4>Ping & Port</h4>
                    <p class="text-muted">Check server availability via ICMP ping or TCP port connectivity.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center p-4">
                    <div class="rounded-circle bg-warning bg-opacity-10 p-3 d-inline-flex mb-3">
                        <i class="fas fa-chart-line fa-2x text-warning"></i>
                    </div>
                    <h4>Detailed Logs</h4>
                    <p class="text-muted">View response times, uptime percentages, and historical logs.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center p-4">
                    <div class="rounded-circle bg-info bg-opacity-10 p-3 d-inline-flex mb-3">
                        <i class="fas fa-moon fa-2x text-info"></i>
                    </div>
                    <h4>Dark Mode</h4>
                    <p class="text-muted">Built-in light/dark themes with user preference saving.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center p-4">
                    <div class="rounded-circle bg-danger bg-opacity-10 p-3 d-inline-flex mb-3">
                        <i class="fas fa-tools fa-2x text-danger"></i>
                    </div>
                    <h4>Maintenance Windows</h4>
                    <p class="text-muted">Schedule downtimes without affecting your uptime stats.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center p-4">
                    <div class="rounded-circle bg-purple bg-opacity-10 p-3 d-inline-flex mb-3" style="background-color: rgba(111,66,193,0.1);">
                        <i class="fas fa-lock fa-2x" style="color: #6f42c1;"></i>
                    </div>
                    <h4>Secure</h4>
                    <p class="text-muted">CSRF protection, secure password hashing, and private data.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Call to Action -->
<div class="container-fluid bg-light py-5">
    <div class="text-center">
        <h2 class="fw-bold">Start Monitoring in Seconds</h2>
        <p class="text-muted mb-4">Free, self-hosted, and always under your control.</p>
        <?php if (!isLoggedIn()): ?>
            <a href="register.php" class="btn btn-primary btn-lg px-5 py-3">Create Your Free Account</a>
        <?php else: ?>
            <a href="dashboard.php" class="btn btn-primary btn-lg px-5 py-3">Go to Dashboard</a>
        <?php endif; ?>
    </div>
</div>

<?php include 'templates/footer.php'; ?>