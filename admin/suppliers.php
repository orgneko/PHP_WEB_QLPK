<?php
// filepath: c:\xampp\htdocs\sportshop1\admin\suppliers.php
session_start();
require_once '../config.php';

// Thêm nhà cung cấp
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add'])) {
    $stmt = $pdo->prepare("INSERT INTO suppliers (name, email, phone, address) VALUES (?, ?, ?, ?)");
    $stmt->execute([$_POST['name'], $_POST['email'], $_POST['phone'], $_POST['address']]);
    header('Location: suppliers.php');
    exit;
}

// Sửa nhà cung cấp
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit'])) {
    $stmt = $pdo->prepare("UPDATE suppliers SET name=?, email=?, phone=?, address=? WHERE id=?");
    $stmt->execute([$_POST['name'], $_POST['email'], $_POST['phone'], $_POST['address'], $_POST['id']]);
    header('Location: suppliers.php');
    exit;
}

// Xóa nhà cung cấp
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM suppliers WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    header('Location: suppliers.php');
    exit;
}

// Lấy danh sách nhà cung cấp
$suppliers = $pdo->query("SELECT * FROM suppliers")->fetchAll(PDO::FETCH_ASSOC);

// Nếu sửa, lấy thông tin nhà cung cấp
$edit_supplier = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM suppliers WHERE id=?");
    $stmt->execute([$_GET['edit']]);
    $edit_supplier = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý nhà cung cấp</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container my-5">
    <h2>Quản lý nhà cung cấp</h2>
    <a href="index.php" class="btn btn-secondary mb-3">Về trang chủ admin</a>
    <!-- Form thêm/sửa nhà cung cấp -->
    <form method="post" class="mb-4">
        <?php if ($edit_supplier): ?>
            <input type="hidden" name="id" value="<?= $edit_supplier['id'] ?>">
        <?php endif; ?>
        <div class="row">
            <div class="col">
                <input type="text" name="name" class="form-control" placeholder="Tên nhà cung cấp" required value="<?= $edit_supplier['name'] ?? '' ?>">
            </div>
            <div class="col">
                <input type="email" name="email" class="form-control" placeholder="Email" value="<?= $edit_supplier['email'] ?? '' ?>">
            </div>
            <div class="col">
                <input type="text" name="phone" class="form-control" placeholder="Điện thoại" value="<?= $edit_supplier['phone'] ?? '' ?>">
            </div>
            <div class="col">
                <input type="text" name="address" class="form-control" placeholder="Địa chỉ" value="<?= $edit_supplier['address'] ?? '' ?>">
            </div>
            <div class="col-auto">
                <button type="submit" name="<?= $edit_supplier ? 'edit' : 'add' ?>" class="btn btn-success">
                    <?= $edit_supplier ? 'Cập nhật' : 'Thêm' ?>
                </button>
                <?php if ($edit_supplier): ?>
                    <a href="suppliers.php" class="btn btn-secondary">Hủy</a>
                <?php endif; ?>
            </div>
        </div>
    </form>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Tên</th>
                <th>Email</th>
                <th>Điện thoại</th>
                <th>Địa chỉ</th>
                <th>Thao tác</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($suppliers as $s): ?>
            <tr>
                <td><?= htmlspecialchars($s['name']) ?></td>
                <td><?= htmlspecialchars($s['email']) ?></td>
                <td><?= htmlspecialchars($s['phone']) ?></td>
                <td><?= htmlspecialchars($s['address']) ?></td>
                <td>
                    <a href="suppliers.php?edit=<?= $s['id'] ?>" class="btn btn-warning btn-sm">Sửa</a>
                    <a href="suppliers.php?delete=<?= $s['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Xóa nhà cung cấp này?')">Xóa</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>