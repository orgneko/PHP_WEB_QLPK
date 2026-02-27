<?php
session_start();
require_once '../../config/config.php';

// Xử lý giá trị NULL khi dùng hàm SUM (tránh lỗi number_format)
function getSum($pdo, $sql)
{
    $result = $pdo->query($sql)->fetchColumn();
    return $result ? $result : 0;
}

// Tổng số Gói khám / Dịch vụ
$total_services = $pdo->query("SELECT COUNT(*) FROM services")->fetchColumn();

// Tổng số Lịch hẹn
$total_bookings = $pdo->query("SELECT COUNT(*) FROM bookings")->fetchColumn();

// Tổng doanh thu (Chỉ tính những lịch đã duyệt/khám xong - 'confirmed')
$total_revenue = getSum($pdo, "SELECT SUM(total_amount) FROM bookings WHERE status='confirmed'");

// Lịch hẹn được đặt trong hôm nay
$today_bookings = $pdo->query("SELECT COUNT(*) FROM bookings WHERE DATE(created_at) = CURDATE()")->fetchColumn();

// Doanh thu dự kiến hôm nay
$today_revenue = getSum($pdo, "SELECT SUM(total_amount) FROM bookings WHERE status='confirmed' AND DATE(created_at) = CURDATE()");

// Top 5 Gói khám phổ biến nhất (Nối bảng bookings, booking_details và services)
$top_services = [];
try {
    $top_services = $pdo->query("
        SELECT p.name, SUM(oi.quantity) as total_sold
        FROM booking_details oi
        JOIN services p ON oi.service_id = p.id
        JOIN bookings o ON oi.booking_id = o.id
        WHERE o.status='confirmed'
        GROUP BY oi.service_id
        ORDER BY total_sold DESC
        LIMIT 5
    ")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // Dự phòng lỗi nếu bảng booking_details chưa có dữ liệu
}

// Top 5 Bệnh nhân chi tiêu nhiều nhất
$top_customers = $pdo->query("
    SELECT u.full_name, COUNT(o.id) as bookings_count, SUM(o.total_amount) as spent
    FROM bookings o
    JOIN users u ON o.user_id = u.id
    WHERE o.status='confirmed'
    GROUP BY o.user_id
    ORDER BY spent DESC
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Báo cáo Doanh thu & Thống kê</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        .stat-card {
            background: #fff;
            border-radius: 12px;
            padding: 24px;
            text-align: center;
            margin-bottom: 24px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            border: 1px solid #e0e0e0;
            transition: transform 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }

        .stat-value {
            font-size: 2.2rem;
            font-weight: 800;
        }

        .section-title {
            margin-top: 20px;
            margin-bottom: 20px;
            font-weight: bold;
            color: #2c3e50;
        }

        .icon-lg {
            font-size: 2.5rem;
            margin-bottom: 15px;
            opacity: 0.8;
        }
    </style>
</head>

<body class="bg-light">
    <div class="container my-5">
        <h2 class="mb-4 fw-bold text-primary"><i class="fas fa-chart-line me-2"></i> Báo cáo Doanh thu & Thống kê</h2>
        <a href="../index.php" class="btn btn-secondary mb-4"><i class="fas fa-arrow-left"></i> Về trang chủ Admin</a>

        <div class="row mb-2">
            <div class="col-md-4">
                <div class="stat-card border-bottom border-primary border-5">
                    <i class="fas fa-stethoscope icon-lg text-primary"></i>
                    <div class="text-muted fw-bold text-uppercase mb-2">Tổng Gói khám</div>
                    <div class="stat-value text-primary"><?= $total_services ?></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card border-bottom border-success border-5">
                    <i class="fas fa-calendar-check icon-lg text-success"></i>
                    <div class="text-muted fw-bold text-uppercase mb-2">Tổng Lịch hẹn</div>
                    <div class="stat-value text-success"><?= $total_bookings ?></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card border-bottom border-warning border-5">
                    <i class="fas fa-money-bill-wave icon-lg text-warning"></i>
                    <div class="text-muted fw-bold text-uppercase mb-2">Tổng Doanh thu</div>
                    <div class="stat-value text-warning"><?= number_format($total_revenue, 0, ',', '.') ?>đ</div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-6">
                <div class="stat-card border-bottom border-info border-5">
                    <div class="text-muted fw-bold text-uppercase mb-2">Lịch hẹn đặt hôm nay</div>
                    <div class="stat-value text-info"><?= $today_bookings ?></div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="stat-card border-bottom border-danger border-5">
                    <div class="text-muted fw-bold text-uppercase mb-2">Doanh thu hôm nay</div>
                    <div class="stat-value text-danger"><?= number_format($today_revenue, 0, ',', '.') ?>đ</div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-white text-primary fw-bold py-3">
                        <i class="fas fa-award me-2"></i> Top 5 Gói khám được đặt nhiều nhất
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Tên Gói khám</th>
                                    <th class="text-center">Số lượt đặt</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($top_services) > 0): ?>
                                    <?php foreach ($top_services as $p): ?>
                                        <tr>
                                            <td class="fw-bold text-dark"><?= htmlspecialchars($p['name']) ?></td>
                                            <td class="text-center fw-bold text-primary"><?= $p['total_sold'] ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="2" class="text-center py-4 text-muted">Chưa có dữ liệu thống kê.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-white text-success fw-bold py-3">
                        <i class="fas fa-crown me-2"></i> Top 5 Bệnh nhân chi tiêu cao nhất
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Họ và tên</th>
                                    <th class="text-center">Số lịch hẹn</th>
                                    <th class="text-end">Tổng chi phí</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($top_customers) > 0): ?>
                                    <?php foreach ($top_customers as $c): ?>
                                        <tr>
                                            <td class="fw-bold text-dark"><?= htmlspecialchars($c['full_name']) ?></td>
                                            <td class="text-center"><?= $c['bookings_count'] ?></td>
                                            <td class="text-end fw-bold text-success"><?= number_format($c['spent'], 0, ',', '.') ?>đ</td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="3" class="text-center py-4 text-muted">Chưa có dữ liệu thống kê.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>
</body>

</html>