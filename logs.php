<?php
// logs.php
define('PAGE_TITLE', 'Logs');
require_once 'includes/config.php';
require_once 'includes/auth.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];

// تعداد رکورد در هر صفحه
$perPage = 20;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $perPage;

// فیلتر مانیتور (اختیاری)
$monitorFilter = $_GET['monitor_id'] ?? '';
$where = "m.user_id = ?";
$params = [$userId];

if (!empty($monitorFilter) && is_numeric($monitorFilter)) {
    $where .= " AND l.monitor_id = ?";
    $params[] = (int)$monitorFilter;
}

// تعداد کل لاگ‌ها
$countStmt = $pdo->prepare("
    SELECT COUNT(*) 
    FROM monitor_logs l
    JOIN monitors m ON l.monitor_id = m.id
    WHERE $where
");
$countStmt->execute($params);
$totalLogs = $countStmt->fetchColumn();
$totalPages = ceil($totalLogs / $perPage);

// دریافت لاگ‌ها با join
$logStmt = $pdo->prepare("
    SELECT l.id, l.monitor_id, m.name AS monitor_name, l.status, l.response_time, l.checked_at
    FROM monitor_logs l
    JOIN monitors m ON l.monitor_id = m.id
    WHERE $where
    ORDER BY l.checked_at DESC
    LIMIT $perPage OFFSET $offset
");
$logStmt->execute($params);
$logs = $logStmt->fetchAll();

// دریافت لیست مانیتورها برای dropdown فیلتر
$monitorStmt = $pdo->prepare("SELECT id, name FROM monitors WHERE user_id = ? ORDER BY name");
$monitorStmt->execute([$userId]);
$monitors = $monitorStmt->fetchAll();
?>
<?php include 'templates/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Logs</h1>
</div>

<!-- فیلتر -->
<form class="row g-2 mb-3" method="get">
    <div class="col-auto">
        <select name="monitor_id" class="form-select">
            <option value="">All Monitors</option>
            <?php foreach ($monitors as $m): ?>
                <option value="<?php echo $m['id']; ?>" <?php echo ($monitorFilter == $m['id']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($m['name']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-auto">
        <button type="submit" class="btn btn-primary">Filter</button>
        <?php if (!empty($monitorFilter)): ?>
            <a href="logs.php" class="btn btn-outline-secondary">Clear</a>
        <?php endif; ?>
    </div>
</form>

<!-- جدول لاگ‌ها -->
<div class="table-responsive">
    <table class="table table-hover align-middle">
        <thead class="table-light">
            <tr>
                <th>Monitor</th>
                <th>Status</th>
                <th>Response Time</th>
                <th>Checked At</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($logs)): ?>
                <tr>
                    <td colspan="4" class="text-center text-muted">No logs found.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($logs as $log): ?>
                <tr>
                    <td><?php echo htmlspecialchars($log['monitor_name']); ?></td>
                    <td>
                        <span class="badge <?php echo $log['status'] === 'up' ? 'bg-success' : 'bg-danger'; ?>">
                            <?php echo ucfirst($log['status']); ?>
                        </span>
                    </td>
                    <td>
                        <?php if ($log['response_time'] !== null): ?>
                            <?php echo $log['response_time']; ?> ms
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
                    <td><small><?php echo date('Y-m-d H:i:s', strtotime($log['checked_at'])); ?></small></td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- صفحه‌بندی ساده -->
<?php if ($totalPages > 1): ?>
<nav>
    <ul class="pagination justify-content-center">
        <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
            <a class="page-link" href="?page=<?php echo $page-1; ?>&monitor_id=<?php echo urlencode($monitorFilter); ?>">Previous</a>
        </li>
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                <a class="page-link" href="?page=<?php echo $i; ?>&monitor_id=<?php echo urlencode($monitorFilter); ?>"><?php echo $i; ?></a>
            </li>
        <?php endfor; ?>
        <li class="page-item <?php echo ($page >= $totalPages) ? 'disabled' : ''; ?>">
            <a class="page-link" href="?page=<?php echo $page+1; ?>&monitor_id=<?php echo urlencode($monitorFilter); ?>">Next</a>
        </li>
    </ul>
</nav>
<?php endif; ?>

<?php include 'templates/footer.php'; ?>