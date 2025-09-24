<?php
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
        $user['name'] = $user['username']; 
        $redis->setex($sessionKey, 1800, json_encode($user)); // TTL 30 phút

        // Cookie server
        setcookie("session_id", $sessionId, time() + 1800, "/");

        // JS lưu localStorage
        echo '<script>
            localStorage.clear(); // xóa dữ liệu cũ
            localStorage.setItem("session_id", "' . $sessionId . '");
            localStorage.setItem("user_id", "' . $user['id'] . '");
            localStorage.setItem("username", "' . addslashes($user['name']) . '");
            window.location.href = "list_users.php";
        </script>';
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