<?php
require_once 'config.php';
if (!isLoggedIn()) redirect('login.php');
$user_id = $_SESSION['user_id'];
$message = '';

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $stmt = $pdo->prepare("UPDATE users SET full_name=?, phone=?, address=? WHERE id=?");
    $stmt->execute([$full_name, $phone, $address, $user_id]);
    $message = "Cập nhật thành công!";
    // Reload lại thông tin
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Trang tài khoản</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            background: #f5f5f5;
            font-family: 'Segoe UI', Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        .account-header {
            background: #fff;
            border-bottom: 1px solid #eee;
            padding: 24px 0 16px 0;
        }
        .account-header .container {
            max-width: 1200px;
            margin: auto;
            padding: 0 16px;
        }
        .account-title {
            font-size: 2rem;
            font-weight: bold;
            color: #222;
        }
        .account-main {
            max-width: 1200px;
            margin: 32px auto 0 auto;
            display: flex;
            gap: 32px;
            padding: 0 16px;
        }
        .account-sidebar {
            width: 260px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            padding: 24px 18px;
            min-height: 320px;
        }
        .account-sidebar h4 {
            font-size: 1.1rem;
            margin-bottom: 18px;
            color: #222;
        }
        .account-sidebar ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .account-sidebar li {
            margin-bottom: 14px;
        }
        .account-sidebar a {
            color: #333;
            text-decoration: none;
            font-size: 1rem;
            transition: color 0.2s;
        }
        .account-sidebar a:hover {
            color: #4CAF50;
        }
        .account-content {
            flex: 1;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            padding: 32px 28px 24px 28px;
            min-height: 320px;
        }
        .account-content h3 {
            font-size: 1.2rem;
            margin-bottom: 18px;
            color: #222;
        }
        .account-info {
            margin-bottom: 24px;
        }
        .account-info label {
            font-weight: 600;
            color: #555;
            margin-right: 8px;
        }
        .account-info span {
            color: #222;
        }
        .message {
            background: #d4edda;
            color: #155724;
            padding: 10px 16px;
            border-radius: 5px;
            margin-bottom: 18px;
            border: 1px solid #c3e6cb;
            font-size: 15px;
        }
        .edit-form {
            margin-top: 18px;
        }
        .edit-form input[type="text"] {
            width: 100%;
            padding: 9px 12px;
            border: 1px solid #b2dfdb;
            border-radius: 6px;
            font-size: 15px;
            margin-bottom: 12px;
            background: #f7fafc;
        }
        .edit-form button {
            background: #4CAF50;
            color: #fff;
            border: none;
            padding: 10px 28px;
            border-radius: 6px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }
        .edit-form button:hover {
            background: #388e3c;
        }
        @media (max-width: 900px) {
            .account-main { flex-direction: column; }
            .account-sidebar { width: 100%; margin-bottom: 18px; }
        }
    </style>
</head>
<body>
    <div class="account-header">
        <div class="container">
            <span class="account-title">Trang tài khoản</span>
        </div>
    </div>
    <div class="account-main">
        <div class="account-sidebar">
            <h4>Xin chào, <?= htmlspecialchars($user['full_name']) ?>!</h4>
            <ul>
                <li><a href="profile.php">Thông tin tài khoản</a></li>
                <li><a href="orders.php">Đơn hàng của bạn</a></li>
                <li><a href="change_password.php">Đổi mật khẩu</a></li>
                <li><a href="logout.php">Đăng xuất</a></li>
            </ul>
        </div>
        <div class="account-content">
            <h3>Thông tin tài khoản</h3>
            <?php if ($message): ?>
                <div class="message"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>
            <div class="account-info">
                <p><label>Họ tên:</label> <span><?= htmlspecialchars($user['full_name']) ?></span></p>
                <p><label>Email:</label> <span><?= htmlspecialchars($user['email']) ?></span></p>
                <p><label>Số điện thoại:</label> <span><?= htmlspecialchars($user['phone']) ?></span></p>
                <p><label>Địa chỉ:</label> <span><?= htmlspecialchars($user['address']) ?></span></p>
            </div>
            <form method="post" class="edit-form">
                <h3>Cập nhật thông tin</h3>
                <input type="text" name="full_name" placeholder="Họ tên" value="<?= htmlspecialchars($user['full_name']) ?>" required>
                <input type="text" name="phone" placeholder="Số điện thoại" value="<?= htmlspecialchars($user['phone']) ?>">
                <input type="text" name="address" placeholder="Địa chỉ" value="<?= htmlspecialchars($user['address']) ?>">
                <button type="submit">Cập nhật</button>
            </form>
        </div>
    </div>
</body>
</html>