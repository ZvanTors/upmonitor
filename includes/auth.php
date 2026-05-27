<?php
// includes/auth.php

/**
 * ثبت‌نام کاربر جدید
 * @param PDO $pdo
 * @param string $username
 * @param string $email
 * @param string $password
 * @return array نتیجه (success یا error)
 */
function registerUser($pdo, $username, $email, $password) {
    // بررسی یکتا بودن نام کاربری و ایمیل
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $email]);
    if ($stmt->fetch()) {
        return ['success' => false, 'message' => 'Username or email already exists.'];
    }

    // هش کردن رمز عبور
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // درج کاربر جدید
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
    $stmt->execute([$username, $email, $hashedPassword]);

    return ['success' => true, 'message' => 'Registration successful. You can now log in.'];
}

/**
 * ورود کاربر با نام کاربری یا ایمیل
 * @param PDO $pdo
 * @param string $login (نام کاربری یا ایمیل)
 * @param string $password
 * @return array نتیجه ورود
 */
function loginUser($pdo, $login, $password) {
    // جستجوی کاربر با نام کاربری یا ایمیل
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$login, $login]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password'])) {
        return ['success' => false, 'message' => 'Invalid username/email or password.'];
    }

    // ذخیره اطلاعات کاربر در نشست
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];

    return ['success' => true, 'user' => $user];
}

/**
 * بررسی لاگین بودن کاربر
 * @return bool
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * کاربر فعلی را برمی‌گرداند (یا null)
 * @param PDO $pdo
 * @return array|null
 */
function getCurrentUser($pdo) {
    if (!isLoggedIn()) return null;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}
?>