<?php
require_once 'models/UserModel.php';

$userModel = new UserModel();
$params = [];
if (!empty($_GET['keyword'])) $params['keyword'] = $_GET['keyword'];
$users = $userModel->getUsers($params);

// Redis
$redis = new Redis();
$redis->connect('web-redis', 6379);

// Lấy sessionId từ cookie
$sessionId = $_COOKIE['session_id'] ?? null;
$userSession = null;

if ($sessionId) {
    $key = "session_user_" . $sessionId;
    if ($redis->exists($key)) {
        $userSession = json_decode($redis->get($key), true);
    }
}
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
        <!-- Hiển thị user hiện tại từ Redis -->
        <div class="alert alert-info">
            <?php if ($userSession && isset($userSession['username'])): ?>
                Xin chào <strong><?php echo htmlspecialchars($userSession['username']); ?></strong> (lấy từ Redis)!
            <?php else: ?>
                Không tìm thấy session trong Redis.
            <?php endif; ?>
        </div>

        <!-- Hiển thị danh sách user -->
        <?php if (!empty($users)) { ?>
            <div class="alert alert-warning" role="alert">
                List of users! <br>
                Hacker: http://php.local/list_users.php?keyword=ASDF%25%22%3BTRUNCATE+banks%3B%23%23
            </div>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th scope="col">ID</th>
                        <th scope="col">Username</th>
                        <th scope="col">Fullname</th>
                        <th scope="col">Type</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user) { ?>
                        <tr>
                            <th scope="row"><?php echo $user['id'] ?></th>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                            <td><?php echo htmlspecialchars($user['type'] ?? ''); ?></td>
                            <td>
                                <a href="form_user.php?id=<?php echo $user['id'] ?>"><i class="fa fa-pencil-square-o" title="Update"></i></a>
                                <a href="view_user.php?id=<?php echo $user['id'] ?>"><i class="fa fa-eye" title="View"></i></a>
                                <a href="delete_user.php?id=<?php echo $user['id'] ?>"><i class="fa fa-eraser" title="Delete"></i></a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } else { ?>
            <div class="alert alert-dark" role="alert">
                Không có user nào!
            </div>
        <?php } ?>
    </div>

    <script>
        // Chỉ đọc localStorage, không ghi demoUser nữa
        const username = localStorage.getItem("username");
        if (username) {
            const div = document.createElement('div');
            div.className = 'alert alert-success';
            div.innerHTML = "Xin chào " + username + " (LocalStorage)";
            document.body.insertBefore(div, document.querySelector('.container'));
        }
    </script>
</body>
</html>
