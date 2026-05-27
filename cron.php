<?php
// cron.php

define('CRON_SECRET', 'Gxpskgkaljsdkla@@13shdlkas'); // کلید محرمانه خودت

if (php_sapi_name() !== 'cli') {
    if (!isset($_GET['secret']) || $_GET['secret'] !== CRON_SECRET) {
        die('Access denied.');
    }
}

require_once 'includes/config.php';
require_once 'includes/monitor-checker.php';

// پاکسازی لاگ‌های قدیمی (بیش از ۳۰ روز)
$deleteStmt = $pdo->prepare("DELETE FROM monitor_logs WHERE checked_at < DATE_SUB(NOW(), INTERVAL 30 DAY)");
$deleteStmt->execute();
$deletedCount = $deleteStmt->rowCount();

// دریافت همه مانیتورها
$stmt = $pdo->query("SELECT * FROM monitors");
$monitors = $stmt->fetchAll();

$now = time();
$checkedCount = 0;
$skippedMaintenance = 0;

foreach ($monitors as $monitor) {
    $lastChecked = $monitor['last_checked'] ? strtotime($monitor['last_checked']) : 0;
    $interval    = (int)$monitor['interval_seconds'];

    if (($now - $lastChecked) < $interval) {
        continue; // هنوز نوبتش نرسیده
    }

    // بررسی وجود بازه Maintenance فعال برای این مانیتور
    $maintStmt = $pdo->prepare("
        SELECT 1 FROM monitor_maintenance 
        WHERE monitor_id = ? 
          AND start_time <= NOW() 
          AND end_time >= NOW()
        LIMIT 1
    ");
    $maintStmt->execute([$monitor['id']]);
    if ($maintStmt->fetch()) {
        // در بازه تعمیرات هستیم: فقط last_checked را آپدیت کن و رد شو
        $pdo->prepare("UPDATE monitors SET last_checked = NOW() WHERE id = ?")
            ->execute([$monitor['id']]);
        $skippedMaintenance++;
        continue;
    }

    // چک واقعی
    $type   = $monitor['type'];
    $target = $monitor['target'];
    $port   = $monitor['port'];

    switch ($type) {
        case 'http':
            $url = 'http://' . ltrim($target, 'http://');
            $url = rtrim($url, '/');
            $result = checkHttp($url);
            break;
        case 'https':
            $url = 'https://' . ltrim($target, 'https://');
            $url = rtrim($url, '/');
            $result = checkHttp($url);
            break;
        case 'ping':
            $result = checkPing($target);
            break;
        case 'port':
            $result = checkPort($target, $port);
            break;
        default:
            continue 2;
    }

    $status       = $result['status'];
    $responseTime = $result['response_time'] ?? null;

    // ثبت لاگ
    $logStmt = $pdo->prepare("INSERT INTO monitor_logs (monitor_id, status, response_time) VALUES (?, ?, ?)");
    $logStmt->execute([$monitor['id'], $status, $responseTime]);

    // به‌روزرسانی مانیتور
    $updateStmt = $pdo->prepare("UPDATE monitors SET status = ?, last_checked = NOW() WHERE id = ?");
    $updateStmt->execute([$status, $monitor['id']]);

    // محاسبه آپتایم ۲۴ ساعته
    $uptimeStmt = $pdo->prepare("
        SELECT 
            COUNT(*) AS total,
            SUM(CASE WHEN status = 'up' THEN 1 ELSE 0 END) AS up_count
        FROM monitor_logs
        WHERE monitor_id = ? 
          AND checked_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
    ");
    $uptimeStmt->execute([$monitor['id']]);
    $uptimeData = $uptimeStmt->fetch();

    $uptimePercent = 100.00;
    if ($uptimeData['total'] > 0) {
        $uptimePercent = round(($uptimeData['up_count'] / $uptimeData['total']) * 100, 2);
    }
    $pdo->prepare("UPDATE monitors SET uptime_percent = ? WHERE id = ?")
        ->execute([$uptimePercent, $monitor['id']]);

    $checkedCount++;
}

// خروجی
if (php_sapi_name() === 'cli') {
    echo "Deleted {$deletedCount} old logs.\n";
    echo "Checked {$checkedCount} monitors. Skipped {$skippedMaintenance} due to maintenance.\n";
} else {
    echo "Deleted {$deletedCount} old logs.<br>";
    echo "Checked {$checkedCount} monitors. Skipped {$skippedMaintenance} due to maintenance.<br>";
    echo "<a href='monitors.php'>Go to Monitors</a>";
}
?>