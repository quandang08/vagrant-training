<?php
// --- Redis ---
$redis = new Redis();
$redis->connect('web-redis', 6379);

// --- Lấy sessionId từ cookie ---
$sessionId = $_COOKIE['session_id'] ?? null;

if ($sessionId) {
    $key = "session_user_" . $sessionId;
    // Xoá session trên Redis
    $redis->del($key, $key . ':meta');

    // Xoá cookie
    setcookie('session_id', '', time() - 3600, '/');
}

// --- Xoá session PHP ---
session_start();
session_destroy();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Logout</title>
    <script>
        // Xoá localStorage
        localStorage.removeItem('session_id');
        localStorage.removeItem('user_id');
        localStorage.removeItem('username');
        window.location.href = 'login.php';
    </script>
</head>
<body>
    Logging out...
</body>
</html>
