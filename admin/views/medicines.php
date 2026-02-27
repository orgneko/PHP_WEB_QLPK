<?php
session_start();
require_once '../../config/config.php';

// Xử lý thêm Thuốc/Vật tư
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add'])) {
    $stmt = $pdo->prepare("INSERT INTO medicines (name, unit, price, stock, description) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$_POST['name'], $_POST['unit'], $_POST['price'], $_POST['stock'], $_POST['description']]);
    header('Location: medicines.php');
    exit;
}

// Xử lý sửa Thuốc/Vật tư
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit'])) {
    $stmt = $pdo->prepare("UPDATE medicines SET name=?, unit=?, price=?, stock=?, description=? WHERE id=?");
    $stmt->execute([$_POST['name'], $_POST['unit'], $_POST['price'], $_POST['stock'], $_POST['description'], $_POST['id']]);
    header('Location: medicines.php');
    exit;
}

// Xử lý xóa Thuốc/Vật tư
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM medicines WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    header('Location: medicines.php');
    exit;
}

// Lấy danh sách Thuốc
$medicines = [];
try {
    $medicines = $pdo->query("SELECT * FROM medicines ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
} catch (\PDOException $e) {
    $db_error = "⚠️ Vui lòng chạy lệnh SQL để tạo bảng `medicines` trước khi sử dụng chức năng này!";
}

// Lấy thông tin Thuốc đang sửa (nếu có)
$edit_med = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM medicines WHERE id=?");
    $stmt->execute([$_GET['edit']]);
    $edit_med = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Quản lý Kho Thuốc & Vật tư</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .low-stock {
            background-color: #fff3cd !important;
        }
    </style>
</head>

<body>
    <div class="container my-5">
        <h2>💊 Quản lý Kho Thuốc & Vật tư Y tế</h2>
        <a href="../index.php" class="btn btn-secondary mb-4">Về trang chủ Admin</a>

        <?php if (isset($db_error)): ?>
            <div class="alert alert-danger fw-bold shadow-sm"><?= $db_error ?></div>
        <?php endif; ?>

        <div class="card mb-4 shadow-sm border-0">
            <div class="card-header font-weight-bold bg-success text-white">
                <?= $edit_med ? 'Sửa thông tin Dược phẩm' : 'Nhập Dược phẩm mới' ?>
            </div>
            <div class="card-body bg-light">
                <form method="post">
                    <?php if ($edit_med): ?>
                        <input type="hidden" name="id" value="<?= $edit_med['id'] ?>">
                    <?php endif; ?>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Tên Thuốc / Vật tư</label>
                            <input type="text" name="name" class="form-control" placeholder="VD: Panadol Extra..." required value="<?= $edit_med['name'] ?? '' ?>">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Đơn vị</label>
                            <input type="text" name="unit" class="form-control" placeholder="Hộp, Vỉ, Viên..." required value="<?= $edit_med['unit'] ?? '' ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Giá bán (VNĐ)</label>
                            <input type="number" name="price" class="form-control" required value="<?= $edit_med['price'] ?? '' ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Số lượng nhập (Tồn kho)</label>
                            <input type="number" name="stock" class="form-control" required value="<?= $edit_med['stock'] ?? '' ?>">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-12">
                            <label class="form-label">Ghi chú / Liều dùng</label>
                            <textarea name="description" class="form-control" rows="2" placeholder="HDSD, cảnh báo..."><?= $edit_med['description'] ?? '' ?></textarea>
                        </div>
                    </div>

                    <button type="submit" name="<?= $edit_med ? 'edit' : 'add' ?>" class="btn btn-success">
                        <?= $edit_med ? 'Cập nhật Kho' : 'Nhập Kho' ?>
                    </button>
                    <?php if ($edit_med): ?>
                        <a href="medicines.php" class="btn btn-secondary">Hủy</a>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <table class="table table-hover table-bordered mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="60" class="text-center">ID</th>
                            <th>Tên Dược phẩm</th>
                            <th>Đơn vị</th>
                            <th>Giá bán</th>
                            <th class="text-center">Tồn kho</th>
                            <th>Trạng thái</th>
                            <th width="120" class="text-center">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($medicines) > 0): ?>
                            <?php foreach ($medicines as $m): ?>
                                <tr class="<?= $m['stock'] <= 10 ? 'low-stock' : '' ?>">
                                    <td class="text-center fw-bold"><?= $m['id'] ?></td>
                                    <td class="fw-bold text-success">
                                        <?= htmlspecialchars($m['name']) ?><br>
                                        <small class="text-muted fw-normal"><?= htmlspecialchars($m['description'] ?? '') ?></small>
                                    </td>
                                    <td><?= htmlspecialchars($m['unit']) ?></td>
                                    <td><?= number_format($m['price'], 0, ',', '.') ?>đ</td>
                                    <td class="text-center fw-bold fs-5">
                                        <?= $m['stock'] ?>
                                    </td>
                                    <td>
                                        <?php if ($m['stock'] == 0): ?>
                                            <span class="badge bg-danger">Hết hàng</span>
                                        <?php elseif ($m['stock'] <= 10): ?>
                                            <span class="badge bg-warning text-dark">Sắp hết (< 10)</span>
                                                <?php else: ?>
                                                    <span class="badge bg-primary">Còn hàng</span>
                                                <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <a href="medicines.php?edit=<?= $m['id'] ?>" class="btn btn-warning btn-sm">Sửa</a>
                                        <a href="medicines.php?delete=<?= $m['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Bạn có muốn xóa mã thuốc này khỏi kho?')">Xóa</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">Kho hiện đang trống.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>

</html>