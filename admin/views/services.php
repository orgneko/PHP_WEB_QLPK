<?php
session_start();
require_once '../../config/config.php';

// Xử lý xóa Dịch vụ (Gói khám)
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $pdo->prepare("DELETE FROM services WHERE id=?")->execute([$id]);
    header('Location: services.php');
    exit();
}

// Xử lý thêm Dịch vụ
if (isset($_POST['add'])) {
    $stmt = $pdo->prepare("INSERT INTO services (name, code, specialty_id, doctor_id, description, price, sale_price, duration, image_url, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $_POST['name'],
        $_POST['code'],
        $_POST['specialty_id'] ?: null,
        $_POST['doctor_id'] ?: null,
        $_POST['description'],
        $_POST['price'],
        $_POST['sale_price'] ?: null,
        $_POST['duration'],
        $_POST['image_url'],
        $_POST['status']
    ]);
    header('Location: services.php');
    exit();
}

// Xử lý sửa Dịch vụ
if (isset($_POST['edit'])) {
    $stmt = $pdo->prepare("UPDATE services SET name=?, code=?, specialty_id=?, doctor_id=?, description=?, price=?, sale_price=?, duration=?, image_url=?, status=? WHERE id=?");
    $stmt->execute([
        $_POST['name'],
        $_POST['code'],
        $_POST['specialty_id'] ?: null,
        $_POST['doctor_id'] ?: null,
        $_POST['description'],
        $_POST['price'],
        $_POST['sale_price'] ?: null,
        $_POST['duration'],
        $_POST['image_url'],
        $_POST['status'],
        $_POST['id']
    ]);
    header('Location: services.php');
    exit();
}

// Lấy danh sách Dịch vụ
$services = $pdo->query("SELECT * FROM services ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
// Lấy Chuyên khoa và Bác sĩ cho select box
$specialties = $pdo->query("SELECT * FROM specialties")->fetchAll(PDO::FETCH_ASSOC);
$doctors = $pdo->query("SELECT * FROM doctors")->fetchAll(PDO::FETCH_ASSOC);

// Nếu sửa, lấy thông tin Dịch vụ
$edit_product = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM services WHERE id=?");
    $stmt->execute([$_GET['edit']]);
    $edit_product = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Quản lý Dịch vụ / Gói khám</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container my-5">
        <h2>Quản lý Dịch vụ / Gói khám</h2>
        <a href="../index.php" class="btn btn-secondary mb-3">Về trang chủ admin</a>

        <div class="card mb-4 shadow-sm">
            <div class="card-header font-weight-bold text-white bg-primary"><?= $edit_product ? 'Sửa Gói khám' : 'Thêm Gói khám mới' ?></div>
            <div class="card-body">
                <form method="post">
                    <?php if ($edit_product): ?>
                        <input type="hidden" name="id" value="<?= $edit_product['id'] ?>">
                    <?php endif; ?>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Tên Gói khám</label>
                            <input type="text" name="name" class="form-control" required value="<?= $edit_product['name'] ?? '' ?>">
                        </div>
                        <div class="col-md-2 mb-3">
                            <label class="form-label fw-bold">Mã Gói</label>
                            <input type="text" name="code" class="form-control" required value="<?= $edit_product['code'] ?? '' ?>">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-bold">Chuyên khoa</label>
                            <select name="specialty_id" class="form-select">
                                <option value="">--Chọn khoa--</option>
                                <?php foreach ($specialties as $c): ?>
                                    <option value="<?= $c['id'] ?>" <?= (isset($edit_product['specialty_id']) && $edit_product['specialty_id'] == $c['id']) ? 'selected' : '' ?>><?= htmlspecialchars($c['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-bold">Bác sĩ phụ trách</label>
                            <select name="doctor_id" class="form-select">
                                <option value="">--Chọn bác sĩ--</option>
                                <?php foreach ($doctors as $s): ?>
                                    <option value="<?= $s['id'] ?>" <?= (isset($edit_product['doctor_id']) && $edit_product['doctor_id'] == $s['id']) ? 'selected' : '' ?>><?= htmlspecialchars($s['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-2 mb-3">
                            <label class="form-label fw-bold">Giá khám (VNĐ)</label>
                            <input type="number" name="price" class="form-control" required value="<?= $edit_product['price'] ?? '' ?>">
                        </div>
                        <div class="col-md-2 mb-3">
                            <label class="form-label fw-bold">Giá ưu đãi (nếu có)</label>
                            <input type="number" name="sale_price" class="form-control" value="<?= $edit_product['sale_price'] ?? '' ?>">
                        </div>
                        <div class="col-md-2 mb-3">
                            <label class="form-label fw-bold">Thời gian (Phút)</label>
                            <input type="number" name="duration" class="form-control" required value="<?= $edit_product['duration'] ?? '' ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Link Ảnh minh họa</label>
                            <input type="text" name="image_url" class="form-control" value="<?= $edit_product['image_url'] ?? '' ?>">
                        </div>
                        <div class="col-md-2 mb-3">
                            <label class="form-label fw-bold">Trạng thái</label>
                            <select name="status" class="form-select">
                                <option value="active" <?= (isset($edit_product['status']) && $edit_product['status'] == 'active') ? 'selected' : '' ?>>Hiển thị</option>
                                <option value="inactive" <?= (isset($edit_product['status']) && $edit_product['status'] == 'inactive') ? 'selected' : '' ?>>Ẩn</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Mô tả chi tiết gói khám</label>
                        <textarea name="description" class="form-control" rows="3"><?= $edit_product['description'] ?? '' ?></textarea>
                    </div>
                    <button class="btn btn-success" name="<?= $edit_product ? 'edit' : 'add' ?>">
                        <?= $edit_product ? 'Cập nhật thay đổi' : 'Lưu Gói khám' ?>
                    </button>
                    <?php if ($edit_product): ?>
                        <a href="services.php" class="btn btn-secondary">Hủy</a>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <table class="table table-bordered table-hover shadow-sm bg-white">
            <thead class="table-light">
                <tr>
                    <th width="50" class="text-center">STT</th>
                    <th>Tên Gói khám</th>
                    <th>Mã</th>
                    <th>Chuyên khoa</th>
                    <th>Bác sĩ</th>
                    <th>Giá</th>
                    <th>Thời lượng</th>
                    <th class="text-center">Trạng thái</th>
                    <th class="text-center">Ảnh</th>
                    <th width="120" class="text-center">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($services) > 0): ?>
                    <?php $stt = 1; // Khởi tạo biến đếm 
                    ?>
                    <?php foreach ($services as $p): ?>
                        <tr>
                            <td class="text-center fw-bold align-middle"><?= $stt++ ?></td>
                            <td class="fw-bold text-primary align-middle"><?= htmlspecialchars($p['name']) ?></td>
                            <td class="align-middle"><?= htmlspecialchars($p['code']) ?></td>
                            <td class="align-middle">
                                <?php
                                $cat = array_filter($specialties, fn($c) => $c['id'] == $p['specialty_id']);
                                echo $cat ? htmlspecialchars(array_values($cat)[0]['name']) : '<span class="text-muted">Chưa xếp</span>';
                                ?>
                            </td>
                            <td class="align-middle">
                                <?php
                                $sup = array_filter($doctors, fn($s) => $s['id'] == $p['doctor_id']);
                                echo $sup ? htmlspecialchars(array_values($sup)[0]['name']) : '<span class="text-muted">Chưa xếp</span>';
                                ?>
                            </td>
                            <td class="align-middle">
                                <?php if (!empty($p['sale_price']) && $p['sale_price'] < $p['price']): ?>
                                    <del class="text-muted small"><?= number_format($p['price'], 0, ',', '.') ?>đ</del><br>
                                    <strong class="text-danger"><?= number_format($p['sale_price'], 0, ',', '.') ?>đ</strong>
                                <?php else: ?>
                                    <strong><?= number_format($p['price'], 0, ',', '.') ?>đ</strong>
                                <?php endif; ?>
                            </td>
                            <td class="align-middle"><?= $p['duration'] ?> phút</td>
                            <td class="text-center align-middle">
                                <?php if ($p['status'] == 'active'): ?>
                                    <span class="badge bg-success">Hiển thị</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Đã ẩn</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center align-middle">
                                <?php if ($p['image_url']): ?>
                                    <img src="<?= htmlspecialchars($p['image_url']) ?>" style="width:50px;height:50px;object-fit:cover; border-radius: 5px; border: 1px solid #ddd;">
                                <?php endif; ?>
                            </td>
                            <td class="text-center align-middle">
                                <a href="services.php?edit=<?= $p['id'] ?>" class="btn btn-warning btn-sm">Sửa</a>
                                <a href="services.php?delete=<?= $p['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Bạn có chắc chắn muốn xóa Gói khám này?')">Xóa</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="10" class="text-center text-muted py-4">Chưa có gói khám nào trong hệ thống.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>

</html>