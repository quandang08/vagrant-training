<?php
header("X-Frame-Options: SAMEORIGIN");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: no-referrer-when-downgrade");
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'");

require_once 'middleware/auth.php';   // auth sẽ redirect nếu chưa login
require_once 'models/UserModel.php';

// Khởi tạo CSRF token nếu chưa có
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$userModel = new UserModel();
$params = [];
if (!empty($_GET['keyword'])) {
    $params['keyword'] = $_GET['keyword'];
}
$users = $userModel->getUsers($params);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Home</title>
    <?php include 'views/meta.php' ?>
</head>
<body>
    <?php include 'views/header.php' ?>

    <div class="container">
        <div class="alert alert-info">
            Xin chào <strong><?php echo htmlspecialchars($currentUser['username'] ?? ''); ?></strong> (đăng nhập).
        </div>
        <div id="local-greet"></div>

        <?php if (!empty($users)) { ?>
            <div class="alert alert-warning" role="alert">List of users!</div>
            <table class="table table-striped">
                <thead> ... </thead>
                <tbody>
                <?php foreach ($users as $user) { ?>
                    <tr>
                        <th scope="row"><?php echo (int)$user['id'] ?></th>
                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                        <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                        <td><?php echo htmlspecialchars($user['type'] ?? ''); ?></td>
                        <td>
                            <a href="form_user.php?id=<?php echo (int)$user['id'] ?>">Edit</a>
                            <a href="view_user.php?id=<?php echo (int)$user['id'] ?>">
                                <i class="fa fa-eye" aria-hidden="true" title="View"></i>
                            </a>
                            <form method="POST" action="delete_user.php" style="display:inline;">
                                <input type="hidden" name="id" value="<?php echo (int)$user['id']; ?>">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                <button type="submit" class="btn btn-link p-0"
                                    onclick="return confirm('Bạn có chắc muốn xóa user này?');">
                                    <i class="fa fa-eraser" aria-hidden="true" title="Delete"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        <?php } else { ?>
            <div class="alert alert-dark" role="alert">Không có user nào!</div>
        <?php } ?>
    </div>

    <script>
        const username = localStorage.getItem("username");
        if (username) {
            const div = document.getElementById('local-greet');
            div.className = 'alert alert-success';
            div.innerHTML = "Xin chào " + username + " (LocalStorage)";
        }
    </script>
</body>
</html>
