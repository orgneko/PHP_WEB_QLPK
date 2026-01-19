<?php
session_start();
require_once '../config.php';

// Thêm khách hàng
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add'])) {
    $stmt = $pdo->prepare("INSERT INTO users (username, password, full_name, email, phone, address, role) VALUES (?, ?, ?, ?, ?, ?, 'customer')");
    $stmt->execute([
        $_POST['username'],
        password_hash($_POST['password'], PASSWORD_DEFAULT),
        $_POST['full_name'],
        $_POST['email'],
        $_POST['phone'],
        $_POST['address']
    ]);
    header('Location: customers.php');
    exit;
}

// Sửa khách hàng
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit'])) {
    $params = [
        $_POST['full_name'],
        $_POST['email'],
        $_POST['phone'],
        $_POST['address'],
        $_POST['id']
    ];
    $sql = "UPDATE users SET full_name=?, email=?, phone=?, address=? WHERE id=?";
    // Nếu có đổi mật khẩu
    if (!empty($_POST['password'])) {
        $sql = "UPDATE users SET full_name=?, email=?, phone=?, address=?, password=? WHERE id=?";
        $params = [
            $_POST['full_name'],
            $_POST['email'],
            $_POST['phone'],
            $_POST['address'],
            password_hash($_POST['password'], PASSWORD_DEFAULT),
            $_POST['id']
        ];
    }
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    header('Location: customers.php');
    exit;
}

// Xóa khách hàng (Cách 2: Xóa cả đơn hàng liên quan trước)
if (isset($_GET['delete'])) {
    $user_id = $_GET['delete'];
    // Xóa tất cả đơn hàng của khách hàng này
    $stmt = $pdo->prepare("DELETE FROM orders WHERE user_id=?");
    $stmt->execute([$user_id]);
    // Sau đó xóa khách hàng
    $stmt = $pdo->prepare("DELETE FROM users WHERE id=?");
    $stmt->execute([$user_id]);
    header('Location: customers.php');
    exit;
}

// Lấy danh sách khách hàng
$users = $pdo->query("SELECT * FROM users WHERE role='customer'")->fetchAll(PDO::FETCH_ASSOC);

// Nếu sửa, lấy thông tin khách hàng
$edit_user = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id=?");
    $stmt->execute([$_GET['edit']]);
    $edit_user = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý khách hàng</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container my-5">
    <h2>Quản lý khách hàng</h2>
    <a href="index.php" class="btn btn-secondary mb-3">Về trang chủ admin</a>
    <!-- Form thêm/sửa khách hàng -->
    <div class="card mb-4">
        <div class="card-header"><?= $edit_user ? 'Sửa khách hàng' : 'Thêm khách hàng mới' ?></div>
        <div class="card-body">
            <form method="post">
                <?php if ($edit_user): ?>
                    <input type="hidden" name="id" value="<?= $edit_user['id'] ?>">
                <?php endif; ?>
                <div class="row mb-2">
                    <div class="col">
                        <input type="text" name="username" class="form-control" placeholder="Tên đăng nhập" required value="<?= $edit_user['username'] ?? '' ?>" <?= $edit_user ? 'readonly' : '' ?>>
                    </div>
                    <div class="col">
                        <input type="password" name="password" class="form-control" placeholder="<?= $edit_user ? 'Đổi mật khẩu (bỏ qua nếu không đổi)' : 'Mật khẩu' ?>">
                    </div>
                    <div class="col">
                        <input type="text" name="full_name" class="form-control" placeholder="Họ tên" required value="<?= $edit_user['full_name'] ?? '' ?>">
                    </div>
                </div>
                <div class="row mb-2">
                    <div class="col">
                        <input type="email" name="email" class="form-control" placeholder="Email" required value="<?= $edit_user['email'] ?? '' ?>">
                    </div>
                    <div class="col">
                        <input type="text" name="phone" class="form-control" placeholder="Điện thoại" value="<?= $edit_user['phone'] ?? '' ?>">
                    </div>
                    <div class="col">
                        <input type="text" name="address" class="form-control" placeholder="Địa chỉ" value="<?= $edit_user['address'] ?? '' ?>">
                    </div>
                </div>
                <button class="btn btn-success" name="<?= $edit_user ? 'edit' : 'add' ?>">
                    <?= $edit_user ? 'Cập nhật' : 'Thêm mới' ?>
                </button>
                <?php if ($edit_user): ?>
                    <a href="customers.php" class="btn btn-secondary">Hủy</a>
                <?php endif; ?>
            </form>
        </div>
    </div>
    <!-- Danh sách khách hàng -->
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Tên đăng nhập</th>
                <th>Họ tên</th>
                <th>Email</th>
                <th>Điện thoại</th>
                <th>Địa chỉ</th>
                <th>Thao tác</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $u): ?>
            <tr>
                <td><?= htmlspecialchars($u['username']) ?></td>
                <td><?= htmlspecialchars($u['full_name']) ?></td>
                <td><?= htmlspecialchars($u['email']) ?></td>
                <td><?= htmlspecialchars($u['phone']) ?></td>
                <td><?= htmlspecialchars($u['address']) ?></td>
                <td>
                    <a href="customers.php?edit=<?= $u['id'] ?>" class="btn btn-warning btn-sm">Sửa</a>
                    <a href="customers.php?delete=<?= $u['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Xóa khách hàng này?')">Xóa</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>