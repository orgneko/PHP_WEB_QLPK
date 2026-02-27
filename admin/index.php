<?php
session_start();
// Gọi file cấu hình (lùi 1 bước vì index.php nằm ở ngay ngoài cùng thư mục admin)
require_once '../config/config.php';

// --- LẤY SỐ LIỆU THỐNG KÊ THỰC TẾ TỪ DATABASE ---

// 1. Tổng số bác sĩ
$total_doctors = $pdo->query("SELECT COUNT(*) FROM doctors")->fetchColumn();

// 2. Lịch hẹn khám trong ngày hôm nay
$today_bookings = $pdo->query("SELECT COUNT(*) FROM bookings WHERE DATE(appointment_date) = CURDATE()")->fetchColumn();

// 3. Tổng Bệnh nhân đã đăng ký
$total_patients = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'patient'")->fetchColumn();

// 4. Thuốc / Vật tư y tế sắp hết (tồn kho <= 10)
$low_stock_meds = 0;
try {
    $low_stock_meds = $pdo->query("SELECT COUNT(*) FROM medicines WHERE stock <= 10")->fetchColumn();
} catch (Exception $e) {
    // Bỏ qua nếu bảng medicines chưa có dữ liệu
}

// 5. Lấy 5 lịch hẹn mới nhất để hiển thị ra bảng
$recent_bookings = $pdo->query("SELECT * FROM bookings ORDER BY created_at DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Quản trị Phòng khám BHH</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        body {
            background-color: #f4f6f9;
            overflow-x: hidden;
        }

        .sidebar {
            min-height: 100vh;
            background-color: #2c3e50;
        }

        .sidebar a {
            color: #ecf0f1;
            text-decoration: none;
            padding: 12px 20px;
            display: block;
            border-radius: 5px;
            margin-bottom: 5px;
            font-weight: 500;
        }

        .sidebar a:hover,
        .sidebar a.active {
            background-color: #3498db;
            color: white;
            transition: 0.3s;
        }

        .card-stat {
            border-radius: 12px;
            border: none;
            transition: transform 0.2s;
        }

        .card-stat:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .icon-bg {
            position: absolute;
            right: 20px;
            top: 20px;
            font-size: 3rem;
            opacity: 0.3;
        }
    </style>
</head>

<body>
    <div class="d-flex">
        <div class="sidebar p-3 shadow-lg" style="width: 270px; flex-shrink: 0;">
            <h4 class="text-white text-center mb-4 mt-2 fw-bold"><i class="fas fa-hospital text-info"></i> BHH CLINIC</h4>
            <ul class="list-unstyled">
                <li><a href="index.php" class="active"><i class="fas fa-tachometer-alt fa-fw me-2"></i> Tổng quan (Dashboard)</a></li>
                <li><a href="views/admin_bookings.php"><i class="fas fa-calendar-check fa-fw me-2"></i> Quản lý Lịch hẹn</a></li>
                <li><a href="views/services.php"><i class="fas fa-stethoscope fa-fw me-2"></i> Gói khám / Dịch vụ</a></li>
                <li><a href="views/specialties.php"><i class="fas fa-cubes fa-fw me-2"></i> Chuyên khoa</a></li>
                <li><a href="views/doctors.php"><i class="fas fa-user-md fa-fw me-2"></i> Hồ sơ Bác sĩ</a></li>
                <li><a href="views/medicines.php"><i class="fas fa-pills fa-fw me-2"></i> Kho Thuốc / Vật tư</a></li>
                <li><a href="views/patients.php"><i class="fas fa-users fa-fw me-2"></i> Hồ sơ Bệnh nhân</a></li>
                <li><a href="views/promotions.php"><i class="fas fa-gift fa-fw me-2"></i> Khuyến mãi</a></li>
                <li><a href="views/reports.php"><i class="fas fa-chart-line fa-fw me-2"></i> Báo cáo doanh thu</a></li>
                <hr class="text-secondary">
                <li><a href="views/change_password.php"><i class="fas fa-key fa-fw me-2"></i> Đổi mật khẩu</a></li>
                <li><a href="actions/logout.php" class="text-danger"><i class="fas fa-sign-out-alt fa-fw me-2"></i> Đăng xuất</a></li>
            </ul>
        </div>

        <div class="flex-grow-1 p-4">
            <div class="d-flex justify-content-between align-items-center mb-4 bg-white p-3 rounded shadow-sm">
                <h3 class="mb-0 text-dark fw-bold">📊 Thống kê Tổng quan</h3>
                <div>
                    <span class="me-3 text-muted">Xin chào, <strong class="text-primary">Admin</strong></span>
                    <a href="../index.php" class="btn btn-outline-primary btn-sm"><i class="fas fa-home"></i> Xem trang chủ Bệnh nhân</a>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-3 mb-3">
                    <div class="card card-stat bg-primary text-white shadow h-100 position-relative overflow-hidden">
                        <div class="card-body">
                            <h6 class="card-title text-uppercase fw-bold text-white-50">Lịch khám hôm nay</h6>
                            <h1 class="display-5 fw-bold mb-0"><?= $today_bookings ?></h1>
                            <i class="fas fa-calendar-day icon-bg"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card card-stat bg-success text-white shadow h-100 position-relative overflow-hidden">
                        <div class="card-body">
                            <h6 class="card-title text-uppercase fw-bold text-white-50">Tổng Bệnh nhân</h6>
                            <h1 class="display-5 fw-bold mb-0"><?= $total_patients ?></h1>
                            <i class="fas fa-users icon-bg"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card card-stat bg-info text-white shadow h-100 position-relative overflow-hidden">
                        <div class="card-body">
                            <h6 class="card-title text-uppercase fw-bold text-white-50">Bác sĩ trực thuộc</h6>
                            <h1 class="display-5 fw-bold mb-0"><?= $total_doctors ?></h1>
                            <i class="fas fa-user-md icon-bg"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card card-stat bg-danger text-white shadow h-100 position-relative overflow-hidden">
                        <div class="card-body">
                            <h6 class="card-title text-uppercase fw-bold text-white-50">Cảnh báo Kho thuốc</h6>
                            <h1 class="display-5 fw-bold mb-0"><?= $low_stock_meds ?> <span class="fs-6 fw-normal">mã sắp hết</span></h1>
                            <i class="fas fa-exclamation-triangle icon-bg"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-8 mb-4">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-white fw-bold py-3 text-primary">
                            <i class="fas fa-clock"></i> Lịch hẹn chờ xử lý gần đây
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Bệnh nhân</th>
                                        <th>SĐT</th>
                                        <th>Giờ hẹn</th>
                                        <th>Trạng thái</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($recent_bookings) > 0): ?>
                                        <?php foreach ($recent_bookings as $b): ?>
                                            <tr>
                                                <td class="fw-bold"><?= htmlspecialchars($b['fullname']) ?></td>
                                                <td><?= htmlspecialchars($b['phone_number']) ?></td>
                                                <td class="text-danger fw-bold"><?= date('d/m/Y H:i', strtotime($b['appointment_date'])) ?></td>
                                                <td>
                                                    <?php
                                                    if ($b['status'] == 'pending') echo '<span class="badge bg-warning text-dark">Chờ duyệt</span>';
                                                    elseif ($b['status'] == 'confirmed') echo '<span class="badge bg-success">Đã duyệt</span>';
                                                    else echo '<span class="badge bg-secondary">Đã hủy</span>';
                                                    ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4" class="text-center py-3 text-muted">Chưa có dữ liệu lịch hẹn mới.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="card-footer bg-white text-end">
                            <a href="views/admin_bookings.php" class="btn btn-sm btn-outline-primary">Xem tất cả Lịch hẹn</a>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 mb-4">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-white fw-bold py-3 text-warning">
                            <i class="fas fa-bullhorn"></i> Ghi chú nội bộ
                        </div>
                        <div class="card-body">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item px-0 border-bottom-0"><i class="fas fa-check-circle text-success me-2"></i> Lịch họp giao ban thứ 2 lúc 8:00 AM</li>
                                <li class="list-group-item px-0 border-bottom-0"><i class="fas fa-exclamation-circle text-danger me-2"></i> Kiểm tra lại kho thuốc Panadol</li>
                                <li class="list-group-item px-0 border-bottom-0"><i class="fas fa-info-circle text-info me-2"></i> Vệ sinh khu vực chờ tầng 1</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</body>

</html>