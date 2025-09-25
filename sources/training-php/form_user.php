<?php
header("X-Frame-Options: SAMEORIGIN");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: no-referrer-when-downgrade");
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'");

require_once 'models/UserModel.php';
require_once 'middleware/auth.php'; // auth.php đã expose $csrfToken và $currentUser

$userModel = new UserModel();

$user = null; // Add new user
$_id = $_GET['id'] ?? null;
if ($_id) {
    $user = $userModel->findUserById($_id); // Update existing user
}

if (!empty($_POST['submit'])) {
    // --- CSRF check ---
    $csrfProvided = $_POST['csrf_token'] ?? '';
    $csrfExpected = $csrfToken ?? '';
    if (!$csrfExpected || !hash_equals($csrfExpected, $csrfProvided)) {
        http_response_code(403);
        die('Invalid CSRF token');
    }

    // --- Xử lý dữ liệu ---
    if (!empty($_id)) {
        $userModel->updateUser($_POST);
    } else {
        $userModel->insertUser($_POST);
    }

    header('Location: list_users.php');
    exit();
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
        <?php if ($user || !$_id) { ?>
            <div class="alert alert-warning" role="alert">
                User form
            </div>
            <form method="POST">
                <!-- CSRF hidden field -->
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">

                <?php if ($_id): ?>
                    <input type="hidden" name="id" value="<?php echo (int)$_id; ?>">
                <?php endif; ?>

                <div class="form-group">
                    <label for="name">Name</label>
                    <input 
                        class="form-control" 
                        name="name" 
                        placeholder="Name" 
                        value="<?php echo htmlspecialchars($user[0]['name'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" name="password" class="form-control" placeholder="Password">
                </div>

                <button type="submit" name="submit" value="submit" class="btn btn-primary">Submit</button>
            </form>
        <?php } else { ?>
            <div class="alert alert-success" role="alert">
                User not found!
            </div>
        <?php } ?>
    </div>
</body>
</html>
