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
        $name    = trim($_POST['name'] ?? '');
        $type    = $_POST['type'] ?? '';
        $target  = trim($_POST['target'] ?? '');
        $port    = ($type === 'port') ? intval($_POST['port'] ?? 0) : null;
        $keyword = ($type === 'keyword') ? trim($_POST['keyword'] ?? '') : null;

        if (empty($name) || empty($type) || empty($target)) {
            $error = 'Name, type and target are required.';
        } elseif (!in_array($type, ['http', 'https', 'ping', 'port', 'keyword', 'ssl'])) {
            $error = 'Invalid monitor type.';
        } elseif ($type === 'port' && ($port < 1 || $port > 65535)) {
            $error = 'Port must be between 1 and 65535.';
        } elseif ($type === 'keyword' && empty($keyword)) {
            $error = 'Keyword is required for keyword monitoring.';
        } else {
            $stmt = $pdo->prepare("INSERT INTO monitors (user_id, name, type, target, port, keyword) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$_SESSION['user_id'], $name, $type, $target, $port, $keyword]);
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
                <input type="text" class="form-control" id="name" name="name" required placeholder="e.g., My Website SSL">
            </div>
            <div class="mb-3">
                <label for="type" class="form-label">Type</label>
                <select class="form-select" id="type" name="type" required>
                    <option value="">-- Select type --</option>
                    <option value="http">HTTP</option>
                    <option value="https">HTTPS</option>
                    <option value="ping">Ping</option>
                    <option value="port">Port</option>
                    <option value="keyword">Keyword</option>
                    <option value="ssl">SSL Certificate</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="target" class="form-label">Target</label>
                <input type="text" class="form-control" id="target" name="target" required placeholder="e.g., example.com">
                <div class="form-text" id="targetHelp">For SSL, enter domain name only (port 443 is checked).</div>
            </div>
            <div class="mb-3" id="portGroup" style="display:none;">
                <label for="port" class="form-label">Port</label>
                <input type="number" class="form-control" id="port" name="port" min="1" max="65535" placeholder="e.g., 3306">
            </div>
            <div class="mb-3" id="keywordGroup" style="display:none;">
                <label for="keyword" class="form-label">Keyword to Find</label>
                <input type="text" class="form-control" id="keyword" name="keyword" placeholder="e.g., Welcome back">
                <div class="form-text">Monitor will check if this word exists on the page.</div>
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
    const keywordGroup = document.getElementById('keywordGroup');
    const keywordInput = document.getElementById('keyword');
    const targetHelp = document.getElementById('targetHelp');

    // پورت
    if (this.value === 'port') {
        portGroup.style.display = 'block';
        portInput.required = true;
    } else {
        portGroup.style.display = 'none';
        portInput.required = false;
    }

    // کلمه کلیدی
    if (this.value === 'keyword') {
        keywordGroup.style.display = 'block';
        keywordInput.required = true;
    } else {
        keywordGroup.style.display = 'none';
        keywordInput.required = false;
    }

    // راهنمای target برای SSL
    if (this.value === 'ssl') {
        targetHelp.textContent = 'Enter domain name (e.g., example.com). Port 443 is checked.';
    } else if (this.value === 'port') {
        targetHelp.textContent = 'Enter IP address or hostname.';
    } else {
        targetHelp.textContent = 'For SSL, enter domain name only (port 443 is checked).';
    }
});
</script>
<?php include 'templates/footer.php'; ?>