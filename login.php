<?php
define('PAGE_TITLE', 'Login');
require_once 'includes/config.php';
require_once 'includes/auth.php';

$error = '';

if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // بررسی CSRF
    if (!verifyCSRFToken()) {
        $error = 'Invalid security token. Please refresh the page and try again.';
    } else {
        $login    = trim($_POST['login'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($login) || empty($password)) {
            $error = 'Please enter your username/email and password.';
        } else {
            $result = loginUser($pdo, $login, $password);
            if ($result['success']) {
                header('Location: dashboard.php');
                exit;
            } else {
                $error = $result['message'];
            }
        }
    }
}
?>
<?php include 'templates/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-5 col-lg-4">
        <h1 class="mb-4">Login</h1>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form method="post">
            <?php echo csrfField(); ?>
            <div class="mb-3">
                <label for="login" class="form-label">Username or Email</label>
                <input type="text" class="form-control" id="login" name="login" required autofocus>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Log in</button>
        </form>
        <p class="mt-3 text-center">Don't have an account? <a href="register.php">Register</a></p>
    </div>
</div>

<?php include 'templates/footer.php'; ?>