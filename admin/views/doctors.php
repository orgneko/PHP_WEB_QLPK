<?php
session_start();
require_once '../../config/config.php';

// Thêm Bác sĩ
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add'])) {
    $stmt = $pdo->prepare("INSERT INTO doctors (name, email, phone, bio, specialty_id, image) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $_POST['name'],
        $_POST['email'],
        $_POST['phone'],
        $_POST['bio'],
        $_POST['specialty_id'] ?: null,
        $_POST['image']
    ]);
    header('Location: doctors.php');
    exit;
}

// Sửa Bác sĩ
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit'])) {
    $stmt = $pdo->prepare("UPDATE doctors SET name=?, email=?, phone=?, bio=?, specialty_id=?, image=? WHERE id=?");
    $stmt->execute([
        $_POST['name'],
        $_POST['email'],
        $_POST['phone'],
        $_POST['bio'],
        $_POST['specialty_id'] ?: null,
        $_POST['image'],
        $_POST['id']
    ]);
    header('Location: doctors.php');
    exit;
}

// Xóa Bác sĩ
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM doctors WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    header('Location: doctors.php');
    exit;
}

// --- PHÉP THUẬT JOIN Ở ĐÂY ---
// Nối bảng doctors với bảng specialties để lấy tên chuyên khoa thay vì chỉ lấy ID
$sql = "SELECT d.*, s.name as specialty_name 
        FROM doctors d 
        LEFT JOIN specialties s ON d.specialty_id = s.id 
        ORDER BY d.id DESC";
$doctors = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

// Lấy danh sách chuyên khoa để hiển thị ở cái Dropdown (thẻ select) lúc thêm/sửa
$specialties = $pdo->query("SELECT * FROM specialties")->fetchAll(PDO::FETCH_ASSOC);

// Nếu sửa, lấy thông tin Bác sĩ
$edit_doctor = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM doctors WHERE id=?");
    $stmt->execute([$_GET['edit']]);
    $edit_doctor = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Quản lý Danh sách Bác sĩ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container my-5">
        <h2>👨‍⚕️ Quản lý Danh sách Bác sĩ</h2>
        <a href="../index.php" class="btn btn-secondary mb-4">Về trang chủ Admin</a>

        <div class="card mb-4 shadow-sm border-0">
            <div class="card-header font-weight-bold bg-primary text-white">
                <?= $edit_doctor ? 'Sửa thông tin Bác sĩ' : 'Thêm Bác sĩ mới' ?>
            </div>
            <div class="card-body bg-light">
                <form method="post">
                    <?php if ($edit_doctor): ?>
                        <input type="hidden" name="id" value="<?= $edit_doctor['id'] ?>">
                    <?php endif; ?>

                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Tên Bác sĩ</label>
                            <input type="text" name="name" class="form-control" placeholder="VD: BS. Nguyễn Văn A" required value="<?= $edit_doctor['name'] ?? '' ?>">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Chuyên khoa</label>
                            <select name="specialty_id" class="form-select" required>
                                <option value="">-- Chọn Khoa --</option>
                                <?php foreach ($specialties as $spec): ?>
                                    <option value="<?= $spec['id'] ?>" <?= (isset($edit_doctor['specialty_id']) && $edit_doctor['specialty_id'] == $spec['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($spec['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Số điện thoại</label>
                            <input type="text" name="phone" class="form-control" placeholder="09xxxx..." value="<?= $edit_doctor['phone'] ?? '' ?>">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" placeholder="bacsi@phongkham.com" value="<?= $edit_doctor['email'] ?? '' ?>">
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Link Ảnh chân dung (URL)</label>
                            <input type="text" name="image" class="form-control" placeholder="https://..." value="<?= $edit_doctor['image'] ?? '' ?>">
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Tiểu sử / Kinh nghiệm</label>
                            <textarea name="bio" class="form-control" rows="3" placeholder="Ví dụ: 15 năm kinh nghiệm, Nguyên trưởng khoa..."><?= $edit_doctor['bio'] ?? '' ?></textarea>
                        </div>
                    </div>

                    <button type="submit" name="<?= $edit_doctor ? 'edit' : 'add' ?>" class="btn btn-success">
                        <?= $edit_doctor ? 'Cập nhật thay đổi' : 'Lưu Bác sĩ' ?>
                    </button>
                    <?php if ($edit_doctor): ?>
                        <a href="doctors.php" class="btn btn-secondary">Hủy</a>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <table class="table table-hover table-bordered mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="80">Ảnh</th>
                            <th>Tên Bác sĩ</th>
                            <th>Chuyên khoa</th>
                            <th>Liên hệ</th>
                            <th width="30%">Tiểu sử</th>
                            <th width="120" class="text-center">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($doctors) > 0): ?>
                            <?php foreach ($doctors as $d): ?>
                                <tr>
                                    <td class="text-center">
                                        <?php if ($d['image']): ?>
                                            <img src="<?= htmlspecialchars($d['image']) ?>" alt="avatar" style="width: 50px; height: 50px; object-fit: cover; border-radius: 50%;">
                                        <?php else: ?>
                                            <div style="width: 50px; height: 50px; background: #ddd; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                                👨‍⚕️
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="fw-bold text-primary"><?= htmlspecialchars($d['name']) ?></td>
                                    <td>
                                        <span class="badge bg-info text-dark">
                                            <?= htmlspecialchars($d['specialty_name'] ?? 'Chưa phân khoa') ?>
                                        </span>
                                    </td>
                                    <td>
                                        <small>
                                            📞 <?= htmlspecialchars($d['phone'] ?? 'N/A') ?><br>
                                            ✉️ <?= htmlspecialchars($d['email'] ?? 'N/A') ?>
                                        </small>
                                    </td>
                                    <td><small class="text-muted"><?= nl2br(htmlspecialchars($d['bio'] ?? '')) ?></small></td>
                                    <td class="text-center">
                                        <a href="doctors.php?edit=<?= $d['id'] ?>" class="btn btn-warning btn-sm">Sửa</a>
                                        <a href="doctors.php?delete=<?= $d['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Bạn có chắc chắn muốn xóa Bác sĩ này khỏi hệ thống?')">Xóa</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">Chưa có dữ liệu bác sĩ.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>

</html>