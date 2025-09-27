<?php
require_once 'middleware/auth.php';
require_once 'models/UserModel.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $csrf = $_POST['csrf_token'] ?? '';

    // check CSRF
    if ($csrf !== ($_SESSION['csrf_token'] ?? '')) {
        die("CSRF token invalid");
    }

    // chỉ cho admin xóa
    if ($currentUser['type'] !== 'admin') {
        die("Bạn không có quyền xóa user!");
    }

    // không cho tự xóa chính mình
    if ($id === (int)$currentUser['id']) {
        die("Không thể tự xóa chính mình");
    }

    $userModel = new UserModel();
    if ($userModel->deleteUserById($id)) {
        header("Location: list_users.php?deleted=1");
        exit;
    } else {
        die("Xóa user thất bại");
    }
}
?>
