<?php
// dashboard.php
define('PAGE_TITLE', 'Dashboard');
require_once 'includes/config.php';
require_once 'includes/auth.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];

// آمار کلی مانیتورهای کاربر
$statsStmt = $pdo->prepare("
    SELECT 
        COUNT(*) AS total,
        SUM(CASE WHEN status = 'up' THEN 1 ELSE 0 END) AS up,
        SUM(CASE WHEN status = 'down' THEN 1 ELSE 0 END) AS down,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) AS pending
    FROM monitors
    WHERE user_id = ?
");
$statsStmt->execute([$userId]);
$stats = $statsStmt->fetch();

// آخرین ۵ لاگ
$logsStmt = $pdo->prepare("
    SELECT m.name, l.status, l.response_time, l.checked_at
    FROM monitor_logs l
    JOIN monitors m ON l.monitor_id = m.id
    WHERE m.user_id = ?
    ORDER BY l.checked_at DESC
    LIMIT 5
");
$logsStmt->execute([$userId]);
$recentLogs = $logsStmt->fetchAll();

// داده‌های نمودار Pie
$chartLabels = ['Up', 'Down', 'Pending'];
$chartData   = [(int)$stats['up'], (int)$stats['down'], (int)$stats['pending']];
?>
<?php include 'templates/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Dashboard</h1>
    <span class="text-muted"><?php echo date('l, F j, Y'); ?></span>
</div>

<!-- کارت‌های آماری -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-md-3">
        <div class="card stat-card" style="--stat-color-1: #4facfe; --stat-color-2: #00f2fe;">
            <div class="card-body">
                <h5 class="card-title"><i class="fas fa-server"></i> Total</h5>
                <p class="card-text display-6"><?php echo $stats['total']; ?></p>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-md-3">
        <div class="card stat-card" style="--stat-color-1: #43e97b; --stat-color-2: #38f9d7;">
            <div class="card-body">
                <h5 class="card-title"><i class="fas fa-check-circle"></i> Up</h5>
                <p class="card-text display-6"><?php echo $stats['up']; ?></p>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-md-3">
        <div class="card stat-card" style="--stat-color-1: #fa709a; --stat-color-2: #fee140;">
            <div class="card-body">
                <h5 class="card-title"><i class="fas fa-exclamation-circle"></i> Down</h5>
                <p class="card-text display-6"><?php echo $stats['down']; ?></p>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-md-3">
        <div class="card stat-card" style="--stat-color-1: #f6d365; --stat-color-2: #fda085;">
            <div class="card-body">
                <h5 class="card-title"><i class="fas fa-clock"></i> Pending</h5>
                <p class="card-text display-6"><?php echo $stats['pending']; ?></p>
            </div>
        </div>
    </div>
</div>

<!-- نمودار Pie و لاگ‌های اخیر -->
<div class="row g-3">
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0">Current Status</h5>
            </div>
            <div class="card-body">
                <?php if ($stats['total'] > 0): ?>
                    <canvas id="statusChart" width="400" height="200"></canvas>
                <?php else: ?>
                    <p class="text-center text-muted">No monitors added yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Recent Activity</h5>
                <a href="logs.php" class="btn btn-sm btn-outline-secondary">View All</a>
            </div>
            <div class="card-body p-0">
                <?php if ($recentLogs): ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Monitor</th>
                                    <th>Status</th>
                                    <th>Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentLogs as $log): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($log['name']); ?></td>
                                    <td>
                                        <span class="badge <?php echo $log['status'] === 'up' ? 'bg-success' : 'bg-danger'; ?>">
                                            <?php echo ucfirst($log['status']); ?>
                                        </span>
                                    </td>
                                    <td><small><?php echo date('H:i:s', strtotime($log['checked_at'])); ?></small></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="p-3 text-muted">No logs yet. Run <code>cron.php?secret=...</code> to start monitoring.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
<?php if ($stats['total'] > 0): ?>
const ctx = document.getElementById('statusChart').getContext('2d');
new Chart(ctx, {
    type: 'doughnut',
    data: {
        labels: <?php echo json_encode($chartLabels); ?>,
        datasets: [{
            data: <?php echo json_encode($chartData); ?>,
            backgroundColor: ['#28a745', '#dc3545', '#ffc107'],
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});
<?php endif; ?>
</script>

<?php include 'templates/footer.php'; ?>