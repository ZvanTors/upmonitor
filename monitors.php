<?php
define('PAGE_TITLE', 'Monitors');
require_once 'includes/config.php';
require_once 'includes/auth.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT * FROM monitors WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$userId]);
$monitors = $stmt->fetchAll();
?>
<?php include 'templates/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Monitors</h1>
    <a href="add-monitor.php" class="btn btn-primary"><i class="fas fa-plus"></i> Add Monitor</a>
</div>

<?php if (empty($monitors)): ?>
    <div class="alert alert-info">
        <i class="fas fa-info-circle"></i> No monitors yet. <a href="add-monitor.php">Add your first monitor</a>.
    </div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>Name</th>
                    <th>Type</th>
                    <th>Target</th>
                    <th>Status</th>
                    <th>Uptime</th>
                    <th>Last Checked</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($monitors as $monitor): ?>
                <tr>
                    <td><?php echo htmlspecialchars($monitor['name']); ?></td>
                    <td>
                        <span class="badge bg-secondary">
                            <?php echo strtoupper($monitor['type']); ?>
                            <?php if ($monitor['type'] === 'port'): ?>:<?php echo $monitor['port']; ?><?php endif; ?>
                        </span>
                    </td>
                    <td><code><?php echo htmlspecialchars($monitor['target']); ?></code></td>
                    <td>
                        <?php if ($monitor['status'] === 'up'): ?>
                            <span class="badge bg-success">Up</span>
                        <?php elseif ($monitor['status'] === 'down'): ?>
                            <span class="badge bg-danger">Down</span>
                        <?php else: ?>
                            <span class="badge bg-warning text-dark">Pending</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="progress" style="height: 20px;" title="<?php echo $monitor['uptime_percent']; ?>%">
                            <div class="progress-bar <?php echo $monitor['uptime_percent'] > 99 ? 'bg-success' : 'bg-warning'; ?>" role="progressbar" style="width: <?php echo $monitor['uptime_percent']; ?>%" aria-valuenow="<?php echo $monitor['uptime_percent']; ?>" aria-valuemin="0" aria-valuemax="100">
                                <?php echo $monitor['uptime_percent']; ?>%
                            </div>
                        </div>
                    </td>
                    <td><?php echo $monitor['last_checked'] ? date('Y-m-d H:i', strtotime($monitor['last_checked'])) : 'Never'; ?></td>
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

<!-- فرم مخفی حذف (با CSRF) -->
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