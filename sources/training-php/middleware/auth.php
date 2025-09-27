<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$redis = new Redis();
$redis->connect('web-redis', 6379);

$sessionId = $_COOKIE['session_id'] ?? null;
if (!$sessionId) {
    header('Location: login.php');
    exit();
}

$key = "session_user_" . $sessionId;
$data = $redis->get($key);
if (!$data) {
    // không có session
    setcookie('session_id', '', time() - 3600, '/');
    header('Location: login.php');
    exit();
}

$sessionData = json_decode($data, true);
if (!is_array($sessionData)) {
    // corruption -> destroy
    $redis->del($key);
    setcookie('session_id', '', time() - 3600, '/');
    header('Location: login.php');
    exit();
}

// kiểm tra user-agent hash nếu muốn chặt
$uaHashStored = $sessionData['ua_hash'] ?? '';
$uaHashNow = hash('sha256', $_SERVER['HTTP_USER_AGENT'] ?? '');
if ($uaHashStored === '' || !hash_equals($uaHashStored, $uaHashNow)) {
    // nghi ngờ -> huỷ session
    $redis->del($key);
    $redis->del($key . ':meta');
    setcookie('session_id', '', time() - 3600, '/');
    header('Location: login.php');
    exit();
}

// extend TTL (activity refresh)
$redis->expire($key, 1800);
$redis->expire($key . ':meta', 1800);

// expose current user to app
$currentUser = $sessionData;

// optionally load csrf for views
$csrfToken = $redis->hGet($key . ':meta', 'csrf') ?: null;
