<?php
// templates/header.php

// تعیین تم فعلی (اولویت: کوکی > دیتابیس کاربر > dark پیش‌فرض)
$theme = 'dark'; // پیش‌فرض
if (isset($_COOKIE['theme'])) {
    $theme = $_COOKIE['theme'];
} elseif (isLoggedIn()) {
    // getCurrentUser از auth.php در config.php لود شده
    $currentUser = getCurrentUser($pdo);
    if ($currentUser && isset($currentUser['dark_mode'])) {
        $theme = $currentUser['dark_mode'] ? 'dark' : 'light';
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="<?php echo $theme; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo defined('PAGE_TITLE') ? PAGE_TITLE . ' - ' . SITE_NAME : SITE_NAME; ?></title>
    <!-- Favicon -->
<link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><path d='M50 25 C50 15 35 5 25 15 C12 30 50 75 50 75 C50 75 88 30 75 15 C65 5 50 15 50 25 Z' fill='%2328a745'/></svg>">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<!-- Navbar -->
<nav class="navbar navbar-expand-lg sticky-top navbar-glass">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold" href="index.php">
            <i class="fas fa-heartbeat text-success"></i> UpMonitor
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-center gap-2">
                <!-- دکمه Donate -->
                <li class="nav-item">
                    <a href="donate.php" class="btn btn-warning btn-sm">
                        <i class="fas fa-mug-hot"></i> Donate
                    </a>
                </li>
                <!-- دکمه Dark Mode -->
                <li class="nav-item">
                    <button id="darkModeToggle" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-moon"></i>
                    </button>
                </li>
                <!-- نام کاربری و خروج (اگر لاگین باشد) -->
                <?php if (isLoggedIn()): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="profile.php"><i class="fas fa-cog"></i> Settings</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="btn btn-primary btn-sm" href="login.php">Login</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar (فقط برای صفحات لاگین‌شده) -->
        <?php if (isLoggedIn()): ?>
            <?php include 'sidebar.php'; ?>
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
        <?php else: ?>
            <main class="col-12 px-md-4 py-4">
        <?php endif; ?>
        <!-- محتوای صفحه در ادامه include می‌شود -->