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

/**
 * چک کردن وجود کلمه کلیدی در محتوای صفحه
 * @param string $url
 * @param string $keyword
 * @param int $timeout
 * @return array
 */
function checkKeyword($url, $keyword, $timeout = 10) {
    $start = microtime(true);
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT        => $timeout,
        CURLOPT_SSL_VERIFYPEER => false,
    ]);
    $content = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error    = curl_error($ch);
    curl_close($ch);

    $responseTime = (microtime(true) - $start) * 1000;

    if (!empty($error) || $httpCode < 200 || $httpCode >= 400) {
        return [
            'status'        => 'down',
            'response_time' => round($responseTime),
            'error'         => $error ?: "HTTP $httpCode"
        ];
    }

    $found = (strpos($content, $keyword) !== false);
    return [
        'status'        => $found ? 'up' : 'down',
        'response_time' => round($responseTime),
        'error'         => $found ? null : "Keyword '$keyword' not found"
    ];
}

/**
 * بررسی گواهی SSL یک دامنه
 * @param string $host دامنه بدون پروتکل
 * @param int $port پورت (پیش‌فرض 443)
 * @param int $timeout
 * @return array
 */
function checkSSL($host, $port = 443, $timeout = 10) {
    $start = microtime(true);
    $context = stream_context_create([
        "ssl" => [
            "capture_peer_cert" => true,
            "verify_peer"       => false,
            "verify_peer_name"  => false,
        ]
    ]);
    $stream = @stream_socket_client(
        "ssl://$host:$port",
        $errno,
        $errstr,
        $timeout,
        STREAM_CLIENT_CONNECT,
        $context
    );
    $responseTime = (microtime(true) - $start) * 1000;

    if (!$stream) {
        return [
            'status'        => 'down',
            'response_time' => round($responseTime),
            'error'         => "SSL connection failed: $errstr"
        ];
    }

    $params = stream_context_get_params($stream);
    $cert = $params['options']['ssl']['peer_certificate'] ?? null;
    fclose($stream);

    if (!$cert) {
        return [
            'status'        => 'down',
            'response_time' => round($responseTime),
            'error'         => 'No SSL certificate found'
        ];
    }

    $info = openssl_x509_parse($cert);
    $validTo = $info['validTo_time_t'] ?? 0;
    $daysLeft = ($validTo - time()) / 86400;

    if ($daysLeft <= 30) {
        return [
            'status'        => 'down',
            'response_time' => round($responseTime),
            'error'         => 'SSL certificate expires in ' . round($daysLeft) . ' days'
        ];
    }

    return [
        'status'        => 'up',
        'response_time' => round($responseTime),
        'error'         => null
    ];
}
?>