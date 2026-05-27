<?php
define('PAGE_TITLE', 'Settings');
require_once 'includes/config.php';
require_once 'includes/auth.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];
$error = '';
$success = '';

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

// تغییر رمز عبور
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    if (!verifyCSRFToken()) {
        $error = 'Invalid security token.';
    } else {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (!password_verify($currentPassword, $user['password'])) {
            $error = 'Current password is incorrect.';
        } elseif (strlen($newPassword) < 6) {
            $error = 'New password must be at least 6 characters.';
        } elseif ($newPassword !== $confirmPassword) {
            $error = 'New passwords do not match.';
        } else {
            $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
            $updateStmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $updateStmt->execute([$hashed, $userId]);
            $success = 'Password changed successfully.';
        }
    }
}

// تنظیمات نمایش
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_preferences'])) {
    if (!verifyCSRFToken()) {
        $error = 'Invalid security token.';
    } else {
        $darkMode = isset($_POST['dark_mode']) ? 1 : 0;
        $updateStmt = $pdo->prepare("UPDATE users SET dark_mode = ? WHERE id = ?");
        $updateStmt->execute([$darkMode, $userId]);
        $success = 'Preferences saved.';
        $user['dark_mode'] = $darkMode;
        // حذف کوکی تا دیتابیس اولویت پیدا کند
        setcookie('theme', '', time() - 3600, '/');
    }
}
?>
<?php include 'templates/header.php'; ?>

<h1 class="mb-4">Settings</h1>

<?php if ($error): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
<?php elseif ($success): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>

<div class="row">
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header"><strong>Account Information</strong></div>
            <div class="card-body">
                <p><strong>Username:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                <p><strong>Member since:</strong> <?php echo date('F j, Y', strtotime($user['created_at'])); ?></p>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header"><strong>Change Password</strong></div>
            <div class="card-body">
                <form method="post">
                    <?php echo csrfField(); ?>
                    <input type="hidden" name="change_password" value="1">
                    <div class="mb-3">
                        <label class="form-label">Current Password</label>
                        <input type="password" name="current_password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">New Password</label>
                        <input type="password" name="new_password" class="form-control" required minlength="6">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Confirm New Password</label>
                        <input type="password" name="confirm_password" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Update Password</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header"><strong>Preferences</strong></div>
            <div class="card-body">
                <form method="post">
                    <?php echo csrfField(); ?>
                    <input type="hidden" name="save_preferences" value="1">
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="darkModeCheck" name="dark_mode" <?php echo ($user['dark_mode'] == 1) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="darkModeCheck">Default Dark Mode</label>
                    </div>
                    <p class="text-muted small">If set, dark mode will be used unless overridden by the toggle button (which saves a cookie).</p>
                    <button type="submit" class="btn btn-primary">Save Preferences</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'templates/footer.php'; ?>