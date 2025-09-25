<?php
header("X-Frame-Options: SAMEORIGIN");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: no-referrer-when-downgrade");
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'");

require_once 'middleware/auth.php';   // auth sẽ redirect nếu chưa login
require_once 'models/UserModel.php';

$userModel = new UserModel();
$params = [];
if (!empty($_GET['keyword'])) {
    // tốt nhất sanitize trước khi đưa vào query builder (your model should use prepared statements)
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
        <!-- Hiển thị user hiện tại từ Redis (được auth.php gắn $currentUser) -->
        <div class="alert alert-info">
            Xin chào <strong><?php echo htmlspecialchars($currentUser['username'] ?? ''); ?></strong> (đăng nhập).
        </div>

        <!-- if want localStorage greeting as extra UI -->
        <div id="local-greet"></div>

        <!-- Hiển thị danh sách user -->
        <?php if (!empty($users)) { ?>
            <div class="alert alert-warning" role="alert">
                List of users!
            </div>
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
                                <!-- remember to include CSRF for destructive actions -->
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
        // optional UI only: use localStorage to show name if present
        const username = localStorage.getItem("username");
        if (username) {
            const div = document.getElementById('local-greet');
            div.className = 'alert alert-success';
            div.innerHTML = "Xin chào " + username + " (LocalStorage)";
        }
    </script>
</body>
</html>
