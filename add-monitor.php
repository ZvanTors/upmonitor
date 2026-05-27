<?php
define('PAGE_TITLE', 'Add Monitor');
require_once 'includes/config.php';
require_once 'includes/auth.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken()) {
        $error = 'Invalid security token.';
    } else {
        $name   = trim($_POST['name'] ?? '');
        $type   = $_POST['type'] ?? '';
        $target = trim($_POST['target'] ?? '');
        $port   = ($type === 'port') ? intval($_POST['port'] ?? 0) : null;

        if (empty($name) || empty($type) || empty($target)) {
            $error = 'Name, type and target are required.';
        } elseif (!in_array($type, ['http', 'https', 'ping', 'port'])) {
            $error = 'Invalid monitor type.';
        } elseif ($type === 'port' && ($port < 1 || $port > 65535)) {
            $error = 'Port must be between 1 and 65535.';
        } else {
            $stmt = $pdo->prepare("INSERT INTO monitors (user_id, name, type, target, port) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$_SESSION['user_id'], $name, $type, $target, $port]);
            $success = 'Monitor added successfully.';
            $_POST = [];
        }
    }
}
?>
<?php include 'templates/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <h1 class="mb-4">Add Monitor</h1>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php elseif ($success): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($success); ?>
                <br><a href="monitors.php">Back to monitors</a>
            </div>
        <?php endif; ?>
        <form method="post">
            <?php echo csrfField(); ?>
            <div class="mb-3">
                <label for="name" class="form-label">Monitor Name</label>
                <input type="text" class="form-control" id="name" name="name" required placeholder="e.g., My Website">
            </div>
            <div class="mb-3">
                <label for="type" class="form-label">Type</label>
                <select class="form-select" id="type" name="type" required>
                    <option value="">-- Select type --</option>
                    <option value="http">HTTP</option>
                    <option value="https">HTTPS</option>
                    <option value="ping">Ping</option>
                    <option value="port">Port</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="target" class="form-label">Target (URL or IP)</label>
                <input type="text" class="form-control" id="target" name="target" required placeholder="e.g., example.com or 192.168.1.1">
            </div>
            <div class="mb-3" id="portGroup" style="display:none;">
                <label for="port" class="form-label">Port</label>
                <input type="number" class="form-control" id="port" name="port" min="1" max="65535" placeholder="e.g., 3306">
            </div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Monitor</button>
            <a href="monitors.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>

<script>
document.getElementById('type').addEventListener('change', function() {
    const portGroup = document.getElementById('portGroup');
    const portInput = document.getElementById('port');
    if (this.value === 'port') {
        portGroup.style.display = 'block';
        portInput.required = true;
    } else {
        portGroup.style.display = 'none';
        portInput.required = false;
    }
});
</script>
<?php include 'templates/footer.php'; ?>