<?php
// includes/security.php

/**
 * تنظیمات امنیتی نشست
 */
ini_set('session.cookie_httponly', 1);   // جلوگیری از دسترسی JavaScript به کوکی نشست
ini_set('session.use_only_cookies', 1);  // فقط از کوکی استفاده کن، شناسه در URL نباشد
// اگر روی هاست واقعی با SSL هستی، خط زیر را فعال کن:
// ini_set('session.cookie_secure', 1);

/**
 * تولید توکن CSRF و ذخیره در نشست
 * @return string
 */
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * تولید فیلد مخفی HTML برای فرم
 * @return string
 */
function csrfField() {
    return '<input type="hidden" name="csrf_token" value="' . generateCSRFToken() . '">';
}

/**
 * بررسی توکن CSRF ارسالی
 * @param string|null $token
 * @return bool
 */
function verifyCSRFToken($token = null) {
    if ($token === null) {
        $token = $_POST['csrf_token'] ?? '';
    }
    if (!isset($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}