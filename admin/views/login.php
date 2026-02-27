<?php
// filepath: c:\xampp\htdocs\sportshop1\admin\login.php
session_start();
require_once '../../config/config.php';

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current = $_POST['currentPassword'];
    $new = $_POST['newPassword'];
    $confirm = $_POST['confirmPassword'];

    // Giả sử tài khoản admin có id = 1
    $stmt = $pdo->prepare("SELECT password FROM users WHERE id=1 AND role='admin'");
    $stmt->execute();
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$admin || !password_verify($current, $admin['password'])) {
        $msg = '<div class="alert alert-danger">Mật khẩu hiện tại không đúng!</div>';
    } elseif ($new !== $confirm) {
        $msg = '<div class="alert alert-danger">Xác nhận mật khẩu mới không khớp!</div>';
    } else {
        $stmt = $pdo->prepare("UPDATE users SET password=? WHERE id=1 AND role='admin'");
        $stmt->execute([password_hash($new, PASSWORD_DEFAULT)]);
        $msg = '<div class="alert alert-success">Đổi mật khẩu thành công!</div>';
    }
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Đổi mật khẩu Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #e0e7ff 0%, #f8fafc 100%);
            min-height: 100vh;
        }

        .change-password-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.08);
            padding: 32px 28px;
            margin-top: 80px;
        }

        .change-password-card h2 {
            color: #2563eb;
            font-weight: 700;
            margin-bottom: 24px;
        }

        .btn-primary {
            background: #2563eb;
            border: none;
        }

        .btn-primary:hover {
            background: #1e40af;
        }

        .form-label {
            font-weight: 500;
            color: #374151;
        }

        .form-control:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 0.2rem rgba(37, 99, 235, .15);
        }

        .alert {
            border-radius: 8px;
        }
    </style>
</head>

<body>
    <div class="container" style="max-width:430px;">
        <div class="change-password-card">
            <h2 class="mb-4 text-center">Đổi mật khẩu Admin</h2>
            <a href="../index.php" class="btn btn-secondary mb-3 w-100">Về trang chủ admin</a>
            <?= $msg ?>
            <form method="POST">
                <div class="mb-3">
                    <label for="currentPassword" class="form-label">Mật khẩu hiện tại</label>
                    <input type="password" class="form-control" id="currentPassword" name="currentPassword" required>
                </div>
                <div class="mb-3">
                    <label for="newPassword" class="form-label">Mật khẩu mới</label>
                    <input type="password" class="form-control" id="newPassword" name="newPassword" required>
                </div>
                <div class="mb-3">
                    <label for="confirmPassword" class="form-label">Xác nhận mật khẩu mới</label>
                    <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Đổi mật khẩu</button>
            </form>
        </div>
    </div>
</body>

</html>