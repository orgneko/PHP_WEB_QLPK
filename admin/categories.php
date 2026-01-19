<?php
// filepath: c:\xampp\htdocs\sportshop1\admin\categories.php
session_start();
require_once '../config.php';

// Thêm danh mục
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add'])) {
    $stmt = $pdo->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
    $stmt->execute([$_POST['name'], $_POST['description']]);
    header('Location: categories.php');
    exit;
}

// Sửa danh mục
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit'])) {
    $stmt = $pdo->prepare("UPDATE categories SET name=?, description=? WHERE id=?");
    $stmt->execute([$_POST['name'], $_POST['description'], $_POST['id']]);
    header('Location: categories.php');
    exit;
}

// Xóa danh mục
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    header('Location: categories.php');
    exit;
}

// Lấy danh sách danh mục
$categories = $pdo->query("SELECT * FROM categories ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);

// Nếu sửa, lấy thông tin danh mục
$edit_category = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE id=?");
    $stmt->execute([$_GET['edit']]);
    $edit_category = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý danh mục</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container my-5">
    <h2>Quản lý danh mục</h2>
    <a href="index.php" class="btn btn-secondary mb-3">Về trang chủ admin</a>
    <!-- Form thêm/sửa danh mục -->
    <div class="card mb-4">
        <div class="card-header"><?= $edit_category ? 'Sửa danh mục' : 'Thêm danh mục mới' ?></div>
        <div class="card-body">
            <form method="post">
                <?php if ($edit_category): ?>
                    <input type="hidden" name="id" value="<?= $edit_category['id'] ?>">
                <?php endif; ?>
                <div class="row">
                    <div class="col">
                        <input type="text" name="name" class="form-control" placeholder="Tên danh mục" required value="<?= $edit_category['name'] ?? '' ?>">
                    </div>
                    <div class="col">
                        <input type="text" name="description" class="form-control" placeholder="Mô tả" value="<?= $edit_category['description'] ?? '' ?>">
                    </div>
                    <div class="col-auto">
                        <button type="submit" name="<?= $edit_category ? 'edit' : 'add' ?>" class="btn btn-success">
                            <?= $edit_category ? 'Cập nhật' : 'Thêm' ?>
                        </button>
                        <?php if ($edit_category): ?>
                            <a href="categories.php" class="btn btn-secondary">Hủy</a>
                        <?php endif; ?>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- Danh sách danh mục -->
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Tên</th>
                <th>Mô tả</th>
                <th>Thao tác</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($categories as $c): ?>
            <tr>
                <td><?= htmlspecialchars($c['name']) ?></td>
                <td><?= htmlspecialchars($c['description']) ?></td>
                <td>
                    <a href="categories.php?edit=<?= $c['id'] ?>" class="btn btn-warning btn-sm">Sửa</a>
                    <a href="categories.php?delete=<?= $c['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Xóa danh mục này?')">Xóa</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>