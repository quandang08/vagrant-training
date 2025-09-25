<?php
function secure_setcookie($name, $value, $ttl=1800) {
    setcookie($name, $value, [
       'expires' => time()+$ttl,
       'path' => '/',
       'httponly' => true,
       'samesite' => 'Lax',
       'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'
    ]);
}

function generate_csrf($redis, $sessionKey) {
    $csrf = bin2hex(random_bytes(16));
    $redis->hSet($sessionKey . ':meta', 'csrf', $csrf);
    return $csrf;
}
