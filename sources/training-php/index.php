<?php
session_start();

// Kiểm tra nếu chưa login → redirect về login.php
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Home Page</title>
</head>
<body>
    <h1>Đây là HomePage</h1>
    <p>Chào mừng, <?php echo htmlspecialchars($_SESSION['user']['name']); ?>!</p>
    <a href="logout.php">Logout</a>
</body>
</html>
