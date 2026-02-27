<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) header('Location: login.php');
require_once '../../config/config.php';
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old = $_POST['old_password'];
    $new = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];
    // Ví dụ: kiểm tra mật khẩu cũ là 'admin123'
    if ($old !== 'admin123') $msg = 'Mật khẩu cũ không đúng!';
    elseif ($new !== $confirm) $msg = 'Xác nhận mật khẩu không khớp!';
    else $msg = 'Đổi mật khẩu thành công! (Bạn cần tự cập nhật lại trong DB)';
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Đổi mật khẩu Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container" style="max-width:400px;margin-top:80px;">
        <div class="card">
            <div class="card-header">Đổi mật khẩu</div>
            <div class="card-body">
                <?php if ($msg): ?><div class="alert alert-info"><?= $msg ?></div><?php endif; ?>
                <form method="post">
                    <div class="mb-3">
                        <label>Mật khẩu cũ</label>
                        <input type="password" name="old_password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Mật khẩu mới</label>
                        <input type="password" name="new_password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Xác nhận mật khẩu mới</label>
                        <input type="password" name="confirm_password" class="form-control" required>
                    </div>
                    <button class="btn btn-primary w-100">Đổi mật khẩu</button>
                </form>
            </div>
        </div>
    </div>
</body>

</html>