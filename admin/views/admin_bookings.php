<?php
session_start();
require_once '../../config/config.php';

// Xử lý duyệt / hủy lịch
if (isset($_POST['update_status'])) {
    $order_id = $_POST['order_id'];
    $new_status = $_POST['update_status'];

    $stmt = $pdo->prepare("UPDATE bookings SET status = ? WHERE id = ?");
    $stmt->execute([$new_status, $order_id]);

    header("Location: admin_bookings.php");
    exit;
}

// Lấy danh sách lịch hẹn
$sql = "SELECT o.*, s.name as doctor_name 
        FROM bookings o 
        LEFT JOIN doctors s ON o.doctor_id = s.id 
        ORDER BY o.created_at DESC";
$stmt = $pdo->query($sql);
$bookings = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Lịch hẹn - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
</head>

<body>
    <div class="container my-5">
        <h2>📅 Quản lý Lịch hẹn Khám</h2>
        <a href="../index.php" class="btn btn-secondary mb-4">Về trang chủ Admin</a>

        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Mã phiếu</th>
                                <th>Ngày đặt</th>
                                <th>Bệnh nhân</th>
                                <th>SĐT</th>
                                <th>Bác sĩ</th>
                                <th>Ngày hẹn</th>
                                <th>Trạng thái</th>
                                <th class="text-center">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($bookings) > 0): ?>
                                <?php foreach ($bookings as $row): ?>
                                    <tr>
                                        <td class="fw-bold"><?= htmlspecialchars($row['booking_code'] ?? $row['order_number'] ?? '---') ?></td>
                                        <td><?= date('d/m H:i', strtotime($row['created_at'])) ?></td>
                                        <td>
                                            <div class="fw-bold text-primary"><?= htmlspecialchars($row['fullname']) ?></div>
                                            <small class="text-muted"><?= htmlspecialchars($row['note']) ?></small>
                                        </td>
                                        <td><?= htmlspecialchars($row['phone_number']) ?></td>
                                        <td class="text-info fw-bold"><?= htmlspecialchars($row['doctor_name'] ?? 'Vãng lai') ?></td>
                                        <td><strong class="text-danger"><?= date('d/m/Y H:i', strtotime($row['appointment_date'])) ?></strong></td>
                                        <td>
                                            <?php
                                            // Nâng cấp màu sắc trạng thái theo chuẩn Bootstrap 5
                                            $s = $row['status'];
                                            if ($s == 'pending') echo '<span class="badge bg-warning text-dark p-2">Chờ duyệt</span>';
                                            elseif ($s == 'confirmed') echo '<span class="badge bg-success p-2">Đã duyệt</span>';
                                            elseif ($s == 'cancelled') echo '<span class="badge bg-danger p-2">Đã hủy</span>';
                                            elseif (empty($s)) echo '<span class="badge bg-danger p-2">Lỗi (Rỗng)</span>';
                                            else echo '<span class="badge bg-secondary p-2">' . htmlspecialchars($s) . '</span>';
                                            ?>
                                        </td>
                                        <td class="text-center">
                                            <form method="POST" style="display:inline-block;">
                                                <input type="hidden" name="order_id" value="<?= $row['id'] ?>">

                                                <?php if ($row['status'] == 'pending' || empty($row['status'])): ?>
                                                    <button type="submit" name="update_status" value="confirmed" class="btn btn-success btn-sm" title="Duyệt">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                    <button type="submit" name="update_status" value="cancelled" class="btn btn-danger btn-sm" title="Hủy" onclick="return confirm('Bạn có chắc chắn muốn hủy lịch này?');">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                <?php else: ?>
                                                    <button type="button" class="btn btn-light btn-sm" disabled><i class="fas fa-lock text-muted"></i></button>
                                                <?php endif; ?>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-muted">
                                        Chưa có lịch đặt nào.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>

</html>