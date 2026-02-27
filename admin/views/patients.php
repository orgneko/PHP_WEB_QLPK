<?php
session_start();
require_once '../../config/config.php';

// Thêm Bệnh nhân
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add'])) {
    $stmt = $pdo->prepare("INSERT INTO users (username, password, full_name, email, phone, address, date_of_birth, gender, role) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'patient')");
    $stmt->execute([
        $_POST['username'],
        password_hash($_POST['password'], PASSWORD_DEFAULT),
        $_POST['full_name'],
        $_POST['email'],
        $_POST['phone'],
        $_POST['address'],
        $_POST['date_of_birth'] ?: null, // Nếu trống thì lưu NULL
        $_POST['gender']
    ]);
    header('Location: patients.php');
    exit;
}

// Sửa Bệnh nhân
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit'])) {
    $params = [
        $_POST['full_name'],
        $_POST['email'],
        $_POST['phone'],
        $_POST['address'],
        $_POST['date_of_birth'] ?: null,
        $_POST['gender'],
        $_POST['id']
    ];
    $sql = "UPDATE users SET full_name=?, email=?, phone=?, address=?, date_of_birth=?, gender=? WHERE id=?";

    // Nếu có đổi mật khẩu
    if (!empty($_POST['password'])) {
        $sql = "UPDATE users SET full_name=?, email=?, phone=?, address=?, date_of_birth=?, gender=?, password=? WHERE id=?";
        $params = [
            $_POST['full_name'],
            $_POST['email'],
            $_POST['phone'],
            $_POST['address'],
            $_POST['date_of_birth'] ?: null,
            $_POST['gender'],
            password_hash($_POST['password'], PASSWORD_DEFAULT),
            $_POST['id']
        ];
    }
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    header('Location: patients.php');
    exit;
}

// Xóa Bệnh nhân (Xóa lịch hẹn trước để không bị lỗi khóa ngoại)
if (isset($_GET['delete'])) {
    $user_id = $_GET['delete'];

    // Xóa tất cả lịch hẹn của bệnh nhân này
    $stmt = $pdo->prepare("DELETE FROM bookings WHERE user_id=?");
    $stmt->execute([$user_id]);

    // Sau đó xóa hồ sơ bệnh nhân
    $stmt = $pdo->prepare("DELETE FROM users WHERE id=?");
    $stmt->execute([$user_id]);
    header('Location: patients.php');
    exit;
}

// Lấy danh sách Bệnh nhân (đã đổi role thành patient)
$users = $pdo->query("SELECT * FROM users WHERE role='patient' ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);

// Nếu sửa, lấy thông tin Bệnh nhân
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
    <title>Quản lý Hồ sơ Bệnh nhân</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container my-5">
        <h2>👥 Quản lý Hồ sơ Bệnh nhân</h2>
        <a href="../index.php" class="btn btn-secondary mb-4">Về trang chủ Admin</a>

        <div class="card mb-4 shadow-sm border-0">
            <div class="card-header font-weight-bold bg-primary text-white">
                <?= $edit_user ? 'Sửa thông tin Bệnh nhân' : 'Thêm Hồ sơ Bệnh nhân mới' ?>
            </div>
            <div class="card-body bg-light">
                <form method="post">
                    <?php if ($edit_user): ?>
                        <input type="hidden" name="id" value="<?= $edit_user['id'] ?>">
                    <?php endif; ?>

                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label class="form-label">Tên đăng nhập</label>
                            <input type="text" name="username" class="form-control" placeholder="Tài khoản đăng nhập" required value="<?= $edit_user['username'] ?? '' ?>" <?= $edit_user ? 'readonly bg-light' : '' ?>>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Mật khẩu</label>
                            <input type="password" name="password" class="form-control" placeholder="<?= $edit_user ? 'Bỏ qua nếu không đổi' : 'Mật khẩu' ?>" <?= $edit_user ? '' : 'required' ?>>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Họ và tên Bệnh nhân</label>
                            <input type="text" name="full_name" class="form-control" placeholder="VD: Nguyễn Văn A" required value="<?= $edit_user['full_name'] ?? '' ?>">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Giới tính</label>
                            <select name="gender" class="form-select">
                                <option value="">-- Chọn --</option>
                                <option value="Nam" <?= (isset($edit_user['gender']) && $edit_user['gender'] == 'Nam') ? 'selected' : '' ?>>Nam</option>
                                <option value="Nữ" <?= (isset($edit_user['gender']) && $edit_user['gender'] == 'Nữ') ? 'selected' : '' ?>>Nữ</option>
                            </select>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-3">
                            <label class="form-label">Ngày sinh</label>
                            <input type="date" name="date_of_birth" class="form-control" value="<?= $edit_user['date_of_birth'] ?? '' ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Số điện thoại</label>
                            <input type="text" name="phone" class="form-control" placeholder="09xxxx..." value="<?= $edit_user['phone'] ?? '' ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" placeholder="benhnhan@email.com" value="<?= $edit_user['email'] ?? '' ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Địa chỉ</label>
                            <input type="text" name="address" class="form-control" placeholder="Tỉnh/Thành phố..." value="<?= $edit_user['address'] ?? '' ?>">
                        </div>
                    </div>

                    <button class="btn btn-success" name="<?= $edit_user ? 'edit' : 'add' ?>">
                        <?= $edit_user ? 'Cập nhật thay đổi' : 'Lưu Hồ sơ' ?>
                    </button>
                    <?php if ($edit_user): ?>
                        <a href="patients.php" class="btn btn-secondary">Hủy</a>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <table class="table table-hover table-bordered mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Họ và tên</th>
                            <th>Giới tính</th>
                            <th>Ngày sinh</th>
                            <th>Liên hệ</th>
                            <th>Địa chỉ</th>
                            <th>Tài khoản</th>
                            <th width="120" class="text-center">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($users) > 0): ?>
                            <?php foreach ($users as $u): ?>
                                <tr>
                                    <td class="fw-bold text-primary"><?= htmlspecialchars($u['full_name']) ?></td>
                                    <td><?= htmlspecialchars($u['gender'] ?? '---') ?></td>
                                    <td>
                                        <?= !empty($u['date_of_birth']) ? date('d/m/Y', strtotime($u['date_of_birth'])) : '<span class="text-muted">Chưa cập nhật</span>' ?>
                                    </td>
                                    <td>
                                        <small>
                                            📞 <?= htmlspecialchars($u['phone']) ?><br>
                                            ✉️ <?= htmlspecialchars($u['email']) ?>
                                        </small>
                                    </td>
                                    <td><small><?= htmlspecialchars($u['address']) ?></small></td>
                                    <td><span class="badge bg-secondary"><?= htmlspecialchars($u['username']) ?></span></td>
                                    <td class="text-center">
                                        <a href="patients.php?edit=<?= $u['id'] ?>" class="btn btn-warning btn-sm">Sửa</a>
                                        <a href="patients.php?delete=<?= $u['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Xóa toàn bộ hồ sơ và lịch hẹn của bệnh nhân này?')">Xóa</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">Chưa có hồ sơ bệnh nhân nào.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>

</html>