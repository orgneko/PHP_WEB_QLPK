<?php

require_once 'config.php';
if (!isLoggedIn()) redirect('login.php');
$user_id = $_SESSION['user_id'];
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old = $_POST['old_password'] ?? '';
    $new = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    // Lấy mật khẩu hiện tại
    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($old, $user['password'])) {
        $message = "Mật khẩu cũ không đúng!";
    } elseif (strlen($new) < 6) {
        $message = "Mật khẩu mới phải từ 6 ký tự!";
    } elseif ($new !== $confirm) {
        $message = "Xác nhận mật khẩu không khớp!";
    } else {
        $hash = password_hash($new, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password=? WHERE id=?");
        $stmt->execute([$hash, $user_id]);
        $message = "Đổi mật khẩu thành công!";
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đổi mật khẩu</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            background: linear-gradient(135deg, #e0e7ff 0%, #f8fafc 100%);
            font-family: 'Segoe UI', Arial, sans-serif;
            min-height: 100vh;
            margin: 0;
        }
        .change-password-container {
            max-width: 410px;
            margin: 60px auto;
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 6px 32px rgba(37,99,235,0.08);
            padding: 38px 32px 28px 32px;
        }
        h2 {
            text-align: center;
            margin-bottom: 28px;
            color: #2563eb;
            font-weight: 700;
            letter-spacing: 1px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            font-weight: 600;
            color: #374151;
            display: block;
            margin-bottom: 7px;
            letter-spacing: 0.2px;
        }
        input[type="password"] {
            width: 100%;
            padding: 10px 13px;
            border: 1.5px solid #b2dfdb;
            border-radius: 7px;
            font-size: 15px;
            background: #f7fafc;
            transition: border-color 0.2s;
        }
        input[type="password"]:focus {
            border-color: #2563eb;
            outline: none;
            background: #fff;
        }
        button {
            background: linear-gradient(90deg, #2563eb 60%, #4CAF50 100%);
            color: #fff;
            border: none;
            width: 100%;
            padding: 12px 0;
            border-radius: 7px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 8px;
            box-shadow: 0 2px 8px rgba(37,99,235,0.07);
            transition: background 0.2s;
        }
        button:hover {
            background: linear-gradient(90deg, #1e40af 60%, #388e3c 100%);
        }
        .message {
            background: #ffeeba;
            color: #856404;
            padding: 11px 18px;
            border-radius: 6px;
            margin-bottom: 20px;
            border: 1.5px solid #ffeeba;
            font-size: 15px;
            text-align: center;
        }
        .success {
            background: #d4edda;
            color: #155724;
            border-color: #c3e6cb;
        }
        .back-link {
            display: block;
            text-align: center;
            margin-top: 22px;
            color: #2563eb;
            text-decoration: none;
            font-size: 15px;
            font-weight: 500;
            transition: color 0.2s;
        }
        .back-link:hover {
            text-decoration: underline;
            color: #1e40af;
        }
        @media (max-width: 500px) {
            .change-password-container {
                padding: 18px 6vw 18px 6vw;
            }
        }
    </style>
</head>
<body>
    <div class="change-password-container">
        <h2>Đổi mật khẩu</h2>
        <?php if ($message): ?>
            <div class="message<?= $message === "Đổi mật khẩu thành công!" ? ' success' : '' ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>
        <form method="post">
            <div class="form-group">
                <label for="old_password">Mật khẩu cũ</label>
                <input type="password" name="old_password" id="old_password" required>
            </div>
            <div class="form-group">
                <label for="new_password">Mật khẩu mới</label>
                <input type="password" name="new_password" id="new_password" required>
            </div>
            <div class="form-group">
                <label for="confirm_password">Xác nhận mật khẩu mới</label>
                <input type="password" name="confirm_password" id="confirm_password" required>
            </div>
            <button type="submit">Đổi mật khẩu</button>
        </form>
        <a href="profile.php" class="back-link">← Quay lại tài khoản</a>
    </div>
</body>
</html>