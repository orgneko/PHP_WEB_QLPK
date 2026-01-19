<?php
// filepath: c:\xampp\htdocs\sportshop1\admin\promotions.php
session_start();
require_once '../config.php';

// Thêm khuyến mãi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add'])) {
    $stmt = $pdo->prepare("INSERT INTO promotions (title, description, discount_percent, start_date, end_date, status) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $_POST['title'], $_POST['description'], $_POST['discount_percent'],
        $_POST['start_date'], $_POST['end_date'], $_POST['status']
    ]);
    header('Location: promotions.php');
    exit;
}

// Sửa khuyến mãi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit'])) {
    $stmt = $pdo->prepare("UPDATE promotions SET title=?, description=?, discount_percent=?, start_date=?, end_date=?, status=? WHERE id=?");
    $stmt->execute([
        $_POST['title'], $_POST['description'], $_POST['discount_percent'],
        $_POST['start_date'], $_POST['end_date'], $_POST['status'], $_POST['id']
    ]);
    header('Location: promotions.php');
    exit;
}

// Xóa khuyến mãi
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM promotions WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    header('Location: promotions.php');
    exit;
}

// Lấy danh sách khuyến mãi
$promotions = $pdo->query("SELECT * FROM promotions ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);

// Nếu sửa, lấy thông tin khuyến mãi
$edit_promotion = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM promotions WHERE id=?");
    $stmt->execute([$_GET['edit']]);
    $edit_promotion = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý khuyến mãi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container my-5">
    <h2>Quản lý chương trình khuyến mãi</h2>
    <a href="index.php" class="btn btn-secondary mb-3">Về trang chủ admin</a>
    <!-- Form thêm/sửa khuyến mãi -->
    <div class="card mb-4">
        <div class="card-header"><?= $edit_promotion ? 'Sửa khuyến mãi' : 'Thêm khuyến mãi mới' ?></div>
        <div class="card-body">
            <form method="post">
                <?php if ($edit_promotion): ?>
                    <input type="hidden" name="id" value="<?= $edit_promotion['id'] ?>">
                <?php endif; ?>
                <div class="row mb-2">
                    <div class="col">
                        <input type="text" name="title" class="form-control" placeholder="Tên chương trình" required value="<?= $edit_promotion['title'] ?? '' ?>">
                    </div>
                    <div class="col">
                        <input type="number" name="discount_percent" class="form-control" placeholder="Giảm (%)" min="0" max="100" required value="<?= $edit_promotion['discount_percent'] ?? '' ?>">
                    </div>
                    <div class="col">
                        <select name="status" class="form-control">
                            <option value="active" <?= (isset($edit_promotion['status']) && $edit_promotion['status'] == 'active') ? 'selected' : '' ?>>Kích hoạt</option>
                            <option value="inactive" <?= (isset($edit_promotion['status']) && $edit_promotion['status'] == 'inactive') ? 'selected' : '' ?>>Ẩn</option>
                        </select>
                    </div>
                </div>
                <div class="row mb-2">
                    <div class="col">
                        <input type="date" name="start_date" class="form-control" required value="<?= $edit_promotion['start_date'] ?? '' ?>">
                    </div>
                    <div class="col">
                        <input type="date" name="end_date" class="form-control" required value="<?= $edit_promotion['end_date'] ?? '' ?>">
                    </div>
                </div>
                <div class="mb-2">
                    <textarea name="description" class="form-control" placeholder="Mô tả"><?= $edit_promotion['description'] ?? '' ?></textarea>
                </div>
                <button class="btn btn-success" name="<?= $edit_promotion ? 'edit' : 'add' ?>">
                    <?= $edit_promotion ? 'Cập nhật' : 'Thêm mới' ?>
                </button>
                <?php if ($edit_promotion): ?>
                    <a href="promotions.php" class="btn btn-secondary">Hủy</a>
                <?php endif; ?>
            </form>
        </div>
    </div>
    <!-- Danh sách khuyến mãi -->
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Tên chương trình</th>
                <th>Giảm (%)</th>
                <th>Thời gian</th>
                <th>Trạng thái</th>
                <th>Mô tả</th>
                <th>Thao tác</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($promotions as $p): ?>
            <tr>
                <td><?= htmlspecialchars($p['title']) ?></td>
                <td><?= $p['discount_percent'] ?>%</td>
                <td><?= htmlspecialchars($p['start_date']) ?> - <?= htmlspecialchars($p['end_date']) ?></td>
                <td><?= $p['status'] == 'active' ? 'Kích hoạt' : 'Ẩn' ?></td>
                <td><?= htmlspecialchars($p['description']) ?></td>
                <td>
                    <a href="promotions.php?edit=<?= $p['id'] ?>" class="btn btn-warning btn-sm">Sửa</a>
                    <a href="promotions.php?delete=<?= $p['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Xóa chương trình này?')">Xóa</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>