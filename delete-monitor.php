<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = intval($_POST['id']);
    $userId = $_SESSION['user_id'];

    // حذف فقط در صورتی که مانیتور متعلق به کاربر باشد
    $stmt = $pdo->prepare("DELETE FROM monitors WHERE id = ? AND user_id = ?");
    $stmt->execute([$id, $userId]);
}

header('Location: monitors.php');
exit;