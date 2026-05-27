<?php
define('PAGE_TITLE', 'Monitors');
require_once 'includes/config.php';
require_once 'includes/auth.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];

// دریافت پارامترهای جستجو و فیلتر
$search  = trim($_GET['search'] ?? '');
$statusFilter = $_GET['status'] ?? 'all'; // all, up, down, pending

// ساخت کوئری پایه
$sql  = "SELECT * FROM monitors WHERE user_id = ?";
$params = [$userId];

// اعمال جستجو بر اساس نام
if (!empty($search)) {
    $sql .= " AND name LIKE ?";
    $params[] = "%$search%";
}

// اعمال فیلتر وضعیت
if (in_array($statusFilter, ['up', 'down', 'pending'])) {
    $sql .= " AND status = ?";
    $params[] = $statusFilter;
}

$sql .= " ORDER BY created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$monitors = $stmt->fetchAll();
?>
<?php include 'templates/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Monitors</h1>
    <a href="add-monitor.php" class="btn btn-primary"><i class="fas fa-plus"></i> Add Monitor</a>
</div>

<!-- نوار جستجو و فیلتر -->
<form class="row g-2 mb-3" method="get">
    <div class="col-md-5">
        <div class="input-group">
            <span class="input-group-text"><i class="fas fa-search"></i></span>
            <input type="text" class="form-control" name="search" placeholder="Search by name..." value="<?php echo htmlspecialchars($search); ?>">
        </div>
    </div>
    <div class="col-md-3">
        <select name="status" class="form-select">
            <option value="all" <?php echo $statusFilter === 'all' ? 'selected' : ''; ?>>All Status</option>
            <option value="up" <?php echo $statusFilter === 'up' ? 'selected' : ''; ?>>Up</option>
            <option value="down" <?php echo $statusFilter === 'down' ? 'selected' : ''; ?>>Down</option>
            <option value="pending" <?php echo $statusFilter === 'pending' ? 'selected' : ''; ?>>Pending</option>
        </select>
    </div>
    <div class="col-auto">
        <button type="submit" class="btn btn-primary">Filter</button>
        <?php if (!empty($search) || $statusFilter !== 'all'): ?>
            <a href="monitors.php" class="btn btn-outline-secondary">Clear</a>
        <?php endif; ?>
    </div>
</form>

<?php if (empty($monitors)): ?>
    <div class="alert alert-info">
        <i class="fas fa-info-circle"></i> No monitors found.
    </div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th style="width: 60px;">Status</th>
                    <th>Name</th>
                    <th>Type</th>
                    <th>Target</th>
                    <th>Uptime</th>
                    <th>Last Checked</th>
                    <th>Last Down</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($monitors as $monitor): ?>
                <tr>
                    <td class="text-center">
                        <?php
                        $status = $monitor['status'];
                        $circleColor = 'bg-secondary';
                        $arrowIcon = 'fa-arrow-right';
                        $animClass = '';

                        if ($status === 'up') {
                            $circleColor = 'bg-success';
                            $arrowIcon = 'fa-arrow-up';
                            $animClass = 'float-up';
                        } elseif ($status === 'down') {
                            $circleColor = 'bg-danger';
                            $arrowIcon = 'fa-arrow-down';
                            $animClass = 'pulse-down';
                        } elseif ($status === 'pending') {
                            $circleColor = 'bg-warning';
                            $arrowIcon = 'fa-arrow-right';
                            $animClass = 'spin-slow';
                        }
                        ?>
                        <span class="status-circle <?php echo $circleColor; ?> <?php echo $animClass; ?>">
                            <i class="fas <?php echo $arrowIcon; ?> text-white"></i>
                        </span>
                    </td>
                    <td><?php echo htmlspecialchars($monitor['name']); ?></td>
                    <td>
                        <span class="badge bg-secondary">
                            <?php echo strtoupper($monitor['type']); ?>
                            <?php if ($monitor['type'] === 'port'): ?>:<?php echo $monitor['port']; ?><?php endif; ?>
                            <?php if ($monitor['type'] === 'keyword'): ?>: <?php echo htmlspecialchars($monitor['keyword'] ?? ''); ?><?php endif; ?>
                            <?php if ($monitor['type'] === 'ssl'): ?> (443)<?php endif; ?>
                        </span>
                    </td>
                    <td><code><?php echo htmlspecialchars($monitor['target']); ?></code></td>
                    <td>
                        <div class="progress" style="height: 20px;" title="<?php echo $monitor['uptime_percent']; ?>%">
                            <div class="progress-bar <?php echo $monitor['uptime_percent'] > 99 ? 'bg-success' : 'bg-warning'; ?>" role="progressbar" style="width: <?php echo $monitor['uptime_percent']; ?>%" aria-valuenow="<?php echo $monitor['uptime_percent']; ?>" aria-valuemin="0" aria-valuemax="100">
                                <?php echo $monitor['uptime_percent']; ?>%
                            </div>
                        </div>
                    </td>
                    <td><?php echo $monitor['last_checked'] ? date('Y-m-d H:i', strtotime($monitor['last_checked'])) : 'Never'; ?></td>
                    <td>
                        <?php if ($monitor['last_down_at']): ?>
                            <span class="text-danger"><?php echo date('Y-m-d H:i', strtotime($monitor['last_down_at'])); ?></span>
                        <?php else: ?>
                            <span class="text-muted">Never</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="edit-monitor.php?id=<?php echo $monitor['id']; ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                        <a href="maintenance.php?id=<?php echo $monitor['id']; ?>" class="btn btn-sm btn-outline-warning"><i class="fas fa-tools"></i></a>
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDelete(<?php echo $monitor['id']; ?>, '<?php echo htmlspecialchars($monitor['name'], ENT_QUOTES); ?>')"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<form id="deleteForm" method="post" action="delete-monitor.php" style="display:none;">
    <?php echo csrfField(); ?>
    <input type="hidden" name="id" id="deleteId">
</form>

<script>
function confirmDelete(id, name) {
    if (confirm('Are you sure you want to delete monitor "' + name + '"? This action cannot be undone.')) {
        document.getElementById('deleteId').value = id;
        document.getElementById('deleteForm').submit();
    }
}
</script>

<?php include 'templates/footer.php'; ?>