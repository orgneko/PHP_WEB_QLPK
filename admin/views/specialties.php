<?php
session_start();
require_once '../../config/config.php';

// Thêm Chuyên khoa
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add'])) {
    $stmt = $pdo->prepare("INSERT INTO specialties (name, description) VALUES (?, ?)");
    $stmt->execute([$_POST['name'], $_POST['description']]);
    header('Location: specialties.php');
    exit;
}

// Sửa Chuyên khoa
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit'])) {
    $stmt = $pdo->prepare("UPDATE specialties SET name=?, description=? WHERE id=?");
    $stmt->execute([$_POST['name'], $_POST['description'], $_POST['id']]);
    header('Location: specialties.php');
    exit;
}

// Xóa Chuyên khoa (Bọc lót lỗi Khóa ngoại)
if (isset($_GET['delete'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM specialties WHERE id = ?");
        $stmt->execute([$_GET['delete']]);
        header('Location: specialties.php');
        exit;
    } catch (\PDOException $e) {
        $error_message = "❌ Không thể xóa! Chuyên khoa này đang có Bác sĩ hoặc Gói khám trực thuộc. Vui lòng chuyển các Bác sĩ sang khoa khác trước khi xóa.";
    }
}

// Lấy danh sách Chuyên khoa
$specialties = $pdo->query("SELECT * FROM specialties ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);

// Nếu sửa, lấy thông tin Chuyên khoa
$edit_category = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM specialties WHERE id=?");
    $stmt->execute([$_GET['edit']]);
    $edit_category = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Quản lý Chuyên khoa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container my-5">
        <h2>📁 Quản lý Chuyên khoa</h2>
        <a href="../index.php" class="btn btn-secondary mb-4">Về trang chủ Admin</a>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger fw-bold shadow-sm"><?= $error_message ?></div>
        <?php endif; ?>

        <div class="card mb-4 shadow-sm border-0">
            <div class="card-header font-weight-bold bg-primary text-white">
                <?= $edit_category ? 'Sửa thông tin Chuyên khoa' : 'Thêm Chuyên khoa mới' ?>
            </div>
            <div class="card-body bg-light">
                <form method="post">
                    <?php if ($edit_category): ?>
                        <input type="hidden" name="id" value="<?= $edit_category['id'] ?>">
                    <?php endif; ?>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Tên Chuyên khoa</label>
                            <input type="text" name="name" class="form-control" placeholder="VD: Khoa Răng Hàm Mặt" required value="<?= $edit_category['name'] ?? '' ?>">
                        </div>
                        <div class="col-md-8 mb-3">
                            <label class="form-label fw-bold">Mô tả chi tiết</label>
                            <textarea name="description" class="form-control" rows="2" placeholder="Chức năng, nhiệm vụ của khoa..."><?= $edit_category['description'] ?? '' ?></textarea>
                        </div>
                        <div class="col-12">
                            <button type="submit" name="<?= $edit_category ? 'edit' : 'add' ?>" class="btn btn-success">
                                <?= $edit_category ? 'Cập nhật thay đổi' : 'Lưu Chuyên khoa' ?>
                            </button>
                            <?php if ($edit_category): ?>
                                <a href="specialties.php" class="btn btn-secondary">Hủy</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <table class="table table-hover table-bordered mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="60" class="text-center">STT</th>
                            <th width="250">Tên Chuyên khoa</th>
                            <th>Mô tả</th>
                            <th width="150" class="text-center">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($specialties) > 0): ?>
                            <?php $stt = 1; ?>
                            <?php foreach ($specialties as $c): ?>
                                <tr>
                                    <td class="text-center fw-bold"><?= $stt++ ?></td>
                                    <td class="fw-bold text-primary"><?= htmlspecialchars($c['name']) ?></td>
                                    <td><small class="text-muted"><?= nl2br(htmlspecialchars($c['description'])) ?></small></td>
                                    <td class="text-center">
                                        <a href="specialties.php?edit=<?= $c['id'] ?>" class="btn btn-warning btn-sm">Sửa</a>
                                        <a href="specialties.php?delete=<?= $c['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Bạn có chắc chắn muốn xóa Chuyên khoa này?')">Xóa</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">Chưa có dữ liệu chuyên khoa.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>

</html>