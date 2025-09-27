<?php
header("X-Frame-Options: SAMEORIGIN");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: no-referrer-when-downgrade");
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'");

require_once 'models/UserModel.php';

$userModel = new UserModel();
$errors = '';
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    $user = $userModel->auth($username, $password);

    if (!empty($user)) {
        $redis = new Redis();
        $redis->connect('web-redis', 6379);

        // Tạo sessionId mới
        $sessionId = bin2hex(random_bytes(16));
        $sessionKey = "session_user_" . $sessionId;

        // lưu payload (để kiểm tra metadata)
        $payload = [
            'id' => $user['id'],
            'username' => $user['username'],
            'ua_hash' => hash('sha256', $_SERVER['HTTP_USER_AGENT'] ?? ''),
            'ip' => $_SERVER['REMOTE_ADDR'] ?? '',
        ];

        // lưu payload
        $redis->setex($sessionKey, 1800, json_encode($payload)); // TTL 30 phút

        // Cookie server
        // setcookie("session_id", $sessionId, time() + 1800, "/");

        // set cookie an toàn (HttpOnly, Secure, SameSite)
        setcookie('session_id', $sessionId, [
            'expires' => time() + 1800,
            'path' => '/',
            'httponly' => true,
            'samesite' => 'Lax',
            'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'
        ]);
        // lưu 1 token CSRF kèm session:
        $csrf = bin2hex(random_bytes(16));
        $redis->hSet($sessionKey . ':meta', 'csrf', $csrf);
        $redis->expire($sessionKey . ':meta', 1800);

        // JS lưu localStorage
        echo "<script>
            localStorage.setItem('username', " . json_encode($user['username']) . ");
            window.location.href = 'list_users.php';
            </script>";
        exit();
    } else {
        $errors = 'Sai username hoặc password';
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>User form</title>
    <?php include 'views/meta.php' ?>
</head>

<body>
    <?php include 'views/header.php' ?>

    <div class="container">
        <?php if ($errors): ?>
            <div style="color:red;"><?php echo htmlspecialchars($errors); ?></div>
        <?php endif; ?>

        <div id="loginbox" style="margin-top:50px;" class="mainbox col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2">
            <div class="panel panel-info">
                <div class="panel-heading">
                    <div class="panel-title">Login</div>
                    <div style="float:right; font-size: 80%; position: relative; top:-10px"><a href="#">Forgot password?</a></div>
                </div>

                <div style="padding-top:30px" class="panel-body">
                    <form method="post" action="login.php" class="form-horizontal" role="form">
                        <div class="margin-bottom-25 input-group">
                            <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
                            <input id="login-username" type="text" class="form-control" name="username" value="<?php echo htmlspecialchars($username); ?>" placeholder="username or email">
                        </div>

                        <div class="margin-bottom-25 input-group">
                            <span class="input-group-addon"><i class="glyphicon glyphicon-lock"></i></span>
                            <input id="login-password" type="password" class="form-control" name="password" placeholder="password">
                        </div>

                        <div class="margin-bottom-25">
                            <input type="checkbox" tabindex="3" name="remember" id="remember">
                            <label for="remember"> Remember Me</label>
                        </div>

                        <div class="margin-bottom-25 input-group">
                            <div class="col-sm-12 controls">
                                <button type="submit" name="submit" value="submit" class="btn btn-primary">Submit</button>
                                <a id="btn-fblogin" href="#" class="btn btn-primary">Login with Facebook</a>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-md-12 control">
                                Don't have an account!
                                <a href="form_user.php">Sign Up Here</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>

</html>