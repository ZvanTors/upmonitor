<?php
// includes/monitor-checker.php

/**
 * چک کردن HTTP یا HTTPS
 * @param string $url آدرس کامل شامل http:// یا https://
 * @param int $timeout زمان انتظار (ثانیه)
 * @return array ['status' => 'up'|'down', 'response_time' => میلی‌ثانیه]
 */
function checkHttp($url, $timeout = 10) {
    $start = microtime(true);
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => $timeout,
        CURLOPT_NOBODY         => true,          // فقط هدر، محتوای صفحه را دانلود نکن
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_SSL_VERIFYPEER => false,         // روی لوکال‌هاست مشکلی ندارد
        CURLOPT_SSL_VERIFYHOST => false,
    ]);
    curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error    = curl_error($ch);
    curl_close($ch);

    $responseTime = (microtime(true) - $start) * 1000; // تبدیل به میلی‌ثانیه
    $status = ($httpCode >= 200 && $httpCode < 400 && empty($error)) ? 'up' : 'down';
    return [
        'status'        => $status,
        'response_time' => round($responseTime),
        'http_code'     => $httpCode,
        'error'         => $error ?: null
    ];
}

/**
 * چک کردن Ping (با دستور سیستم‌عامل)
 * روی ویندوز: ping -n 1
 * @param string $host
 * @return array
 */
function checkPing($host) {
    $start = microtime(true);
    $output = [];
    $result = 1;
    // 1 بسته، timeout 2 ثانیه
    exec("ping -n 1 -w 2000 " . escapeshellarg($host), $output, $result);
    $responseTime = (microtime(true) - $start) * 1000;
    $status = ($result === 0) ? 'up' : 'down';
    return [
        'status'        => $status,
        'response_time' => round($responseTime),
        'error'         => $status === 'down' ? 'Host unreachable' : null
    ];
}

/**
 * چک کردن پورت TCP
 * @param string $host
 * @param int $port
 * @param int $timeout
 * @return array
 */
function checkPort($host, $port, $timeout = 5) {
    $start = microtime(true);
    $errno  = 0;
    $errstr = '';
    $fp = @fsockopen($host, $port, $errno, $errstr, $timeout);
    $responseTime = (microtime(true) - $start) * 1000;
    if (is_resource($fp)) {
        fclose($fp);
        return [
            'status'        => 'up',
            'response_time' => round($responseTime),
            'error'         => null
        ];
    } else {
        return [
            'status'        => 'down',
            'response_time' => null,
            'error'         => "Port closed ($errstr)"
        ];
    }
}
?>