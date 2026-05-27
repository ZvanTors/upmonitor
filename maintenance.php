<?php
define('PAGE_TITLE', 'Maintenance Windows');
require_once 'includes/config.php';
require_once 'includes/auth.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];
$monitorId = intval($_GET['id'] ?? 0);

// بررسی وجود مانیتور و تعلق به کاربر
$stmt = $pdo->prepare("SELECT * FROM monitors WHERE id = ? AND user_id = ?");
$stmt->execute([$monitorId, $userId]);
$monitor = $stmt->fetch();
if (!$monitor) {
    header('Location: monitors.php');
    exit;
}

$error = '';
$success = '';

// ------------------ حذف یک پنجره ------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    if (!verifyCSRFToken()) {
        $error = 'Invalid security token.';
    } else {
        $deleteId = intval($_POST['delete_id']);
        $delStmt = $pdo->prepare("DELETE FROM monitor_maintenance WHERE id = ? AND monitor_id = ?");
        $delStmt->execute([$deleteId, $monitorId]);
        $success = 'Maintenance window deleted.';
    }
}

// ------------------ افزودن / ویرایش ------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
    if (!verifyCSRFToken()) {
        $error = 'Invalid security token.';
    } else {
        $editId      = $_POST['edit_id'] ?? 0;
        $startTime   = $_POST['start_time'] ?? '';
        $endTime     = $_POST['end_time'] ?? '';
        $description = trim($_POST['description'] ?? '');

        if (empty($startTime) || empty($endTime)) {
            $error = 'Start and end time are required.';
        } elseif ($startTime >= $endTime) {
            $error = 'End time must be after start time.';
        } else {
            if ($editId > 0) {
                // ویرایش
                $updStmt = $pdo->prepare("UPDATE monitor_maintenance SET start_time=?, end_time=?, description=? WHERE id=? AND monitor_id=?");
                $updStmt->execute([$startTime, $endTime, $description, $editId, $monitorId]);
                $success = 'Maintenance window updated.';
            } else {
                // افزودن جدید
                $insStmt = $pdo->prepare("INSERT INTO monitor_maintenance (monitor_id, start_time, end_time, description) VALUES (?, ?, ?, ?)");
                $insStmt->execute([$monitorId, $startTime, $endTime, $description]);
                $success = 'Maintenance window added.';
            }
        }
    }
}

// دریافت همه پنجره‌های این مانیتور (جدیدترین‌ها اول)
$maintStmt = $pdo->prepare("SELECT * FROM monitor_maintenance WHERE monitor_id = ? ORDER BY start_time DESC");
$maintStmt->execute([$monitorId]);
$windows = $maintStmt->fetchAll();

// اگر پارامتر edit وجود داشت، اطلاعات آن پنجره را بارگذاری کن
$editWindow = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $editId = intval($_GET['edit']);
    $editStmt = $pdo->prepare("SELECT * FROM monitor_maintenance WHERE id = ? AND monitor_id = ?");
    $editStmt->execute([$editId, $monitorId]);
    $editWindow = $editStmt->fetch();
}
?>
<?php include 'templates/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>
        <a href="monitors.php" class="btn btn-sm btn-outline-secondary me-2"><i class="fas fa-arrow-left"></i></a>
        Maintenance: <?php echo htmlspecialchars($monitor['name']); ?>
    </h1>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
<?php elseif ($success): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>

<!-- فرم افزودن / ویرایش -->
<div class="card mb-4">
    <div class="card-header"><strong><?php echo $editWindow ? 'Edit' : 'Add'; ?> Maintenance Window</strong></div>
    <div class="card-body">
        <form method="post">
            <?php echo csrfField(); ?>
            <input type="hidden" name="save" value="1">
            <input type="hidden" name="edit_id" value="<?php echo $editWindow ? $editWindow['id'] : 0; ?>">
            <div class="row g-3">
                <div class="col-md-5">
                    <label class="form-label">Start Time</label>
                    <input type="datetime-local" name="start_time" class="form-control" required 
                           value="<?php echo $editWindow ? date('Y-m-d\TH:i', strtotime($editWindow['start_time'])) : ''; ?>">
                </div>
                <div class="col-md-5">
                    <label class="form-label">End Time</label>
                    <input type="datetime-local" name="end_time" class="form-control" required
                           value="<?php echo $editWindow ? date('Y-m-d\TH:i', strtotime($editWindow['end_time'])) : ''; ?>">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-save"></i> <?php echo $editWindow ? 'Update' : 'Add'; ?>
                    </button>
                </div>
                <div class="col-12">
                    <label class="form-label">Description (optional)</label>
                    <input type="text" name="description" class="form-control" maxlength="255"
                           value="<?php echo $editWindow ? htmlspecialchars($editWindow['description'] ?? '') : ''; ?>">
                </div>
            </div>
            <?php if ($editWindow): ?>
                <a href="maintenance.php?id=<?php echo $monitorId; ?>" class="btn btn-secondary mt-3">Cancel Edit</a>
            <?php endif; ?>
        </form>
    </div>
</div>

<!-- لیست پنجره‌های موجود -->
<div class="card">
    <div class="card-header"><strong>Existing Windows</strong></div>
    <div class="card-body p-0">
        <?php if (empty($windows)): ?>
            <p class="p-3 text-muted">No maintenance windows defined.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Start</th>
                            <th>End</th>
                            <th>Description</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($windows as $win): ?>
                        <tr>
                            <td><?php echo date('Y-m-d H:i', strtotime($win['start_time'])); ?></td>
                            <td><?php echo date('Y-m-d H:i', strtotime($win['end_time'])); ?></td>
                            <td><?php echo htmlspecialchars($win['description'] ?? ''); ?></td>
                            <td>
                                <a href="maintenance.php?id=<?php echo $monitorId; ?>&edit=<?php echo $win['id']; ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form method="post" class="d-inline" onsubmit="return confirm('Delete this window?');">
                                    <?php echo csrfField(); ?>
                                    <input type="hidden" name="delete_id" value="<?php echo $win['id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'templates/footer.php'; ?>