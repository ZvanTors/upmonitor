<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    // بررسی CSRF
    if (!verifyCSRFToken()) {
        // فقط برگرد به لیست
        header('Location: monitors.php');
        exit;
    }
    $id = intval($_POST['id']);
    $userId = $_SESSION['user_id'];
    $stmt = $pdo->prepare("DELETE FROM monitors WHERE id = ? AND user_id = ?");
    $stmt->execute([$id, $userId]);
}

header('Location: monitors.php');
exit;