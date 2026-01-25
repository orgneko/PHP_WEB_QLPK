<?php
session_start();

// --- 1. KẾT NỐI DATABASE ---
$host = 'localhost';
$db   = 'phongkham';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
try {
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (\PDOException $e) {
    die("Lỗi kết nối Database: " . $e->getMessage());
}

// --- 2. XỬ LÝ DUYỆT / HỦY LỊCH (ĐÃ FIX LỖI) ---
if (isset($_POST['update_status'])) {
    $order_id = $_POST['order_id'];

    // [FIX] Lấy đúng giá trị từ nút bấm (confirmed hoặc cancelled)
    $new_status = $_POST['update_status'];

    $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->execute([$new_status, $order_id]);

    header("Location: admin_bookings.php");
    exit;
}

// --- 3. LẤY DANH SÁCH LỊCH HẸN ---
$sql = "SELECT o.*, s.name as doctor_name 
        FROM orders o 
        LEFT JOIN suppliers s ON o.doctor_id = s.id 
        ORDER BY o.created_at DESC";
$stmt = $pdo->query($sql);
$bookings = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Lịch hẹn - Admin BHH</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        body {
            background-color: #f4f6f9;
            min-height: 100vh;
            overflow-x: hidden;
        }

        .wrapper {
            display: flex;
            width: 100%;
            align-items: stretch;
        }

        .sidebar {
            min-width: 250px;
            max-width: 250px;
            min-height: 100vh;
            background: #343a40;
            color: white;
            transition: all 0.3s;
        }

        .sidebar .sidebar-header {
            padding: 20px;
            background: #007bff;
            font-weight: bold;
        }

        .sidebar ul.components {
            padding: 20px 0;
            border-bottom: 1px solid #47748b;
        }

        .sidebar ul li a {
            padding: 15px;
            font-size: 1.1em;
            display: block;
            color: #cfd8dc;
            text-decoration: none;
        }

        .sidebar ul li a:hover {
            color: #fff;
            background: #0069d9;
        }

        .sidebar ul li.active>a {
            color: #fff;
            background: #0069d9;
        }

        #content {
            width: 100%;
            padding: 20px;
            min-height: 100vh;
            transition: all 0.3s;
        }

        /* Màu badge trạng thái */
        .badge-pending {
            background-color: #ffc107;
            color: #000;
        }

        .badge-confirmed {
            background-color: #28a745;
            color: #fff;
        }

        .badge-cancelled {
            background-color: #dc3545;
            color: #fff;
        }
    </style>
</head>

<body>

    <div class="wrapper">
        <nav class="sidebar">
            <div class="sidebar-header">
                <i class="fas fa-user-shield"></i> ADMIN BHH
            </div>

            <ul class="list-unstyled components">
                <li><a href="index.php"><i class="fas fa-tachometer-alt mr-2"></i> Dashboard</a></li>
                <li class="active"><a href="admin_bookings.php"><i class="fas fa-calendar-check mr-2"></i> Quản lý Lịch hẹn</a></li>
                <li><a href="customers.php"><i class="fas fa-users mr-2"></i> Quản lý Bệnh nhân</a></li>
                <li><a href="inventory.php"><i class="fas fa-boxes mr-2"></i> Kho thuốc</a></li>
                <li><a href="orders.php"><i class="fas fa-shopping-cart mr-2"></i> Đơn hàng cũ</a></li>
            </ul>

            <div class="text-center mt-5">
                <a href="../index.php" class="btn btn-outline-light btn-sm">Về trang chủ Web</a>
            </div>
        </nav>

        <div id="content">
            <h3 class="mb-4 text-primary font-weight-bold">Danh sách Đặt lịch khám</h3>

            <div class="card shadow-sm border-0">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered mb-0">
                            <thead class="thead-light">
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
                                            <td><strong><?= htmlspecialchars($row['order_number'] ?? '---') ?></strong></td>
                                            <td><?= date('d/m H:i', strtotime($row['created_at'])) ?></td>
                                            <td>
                                                <div class="font-weight-bold"><?= htmlspecialchars($row['fullname']) ?></div>
                                                <small class="text-muted"><?= htmlspecialchars($row['note']) ?></small>
                                            </td>
                                            <td><?= htmlspecialchars($row['phone_number']) ?></td>
                                            <td class="text-info"><?= htmlspecialchars($row['doctor_name'] ?? 'Vãng lai') ?></td>
                                            <td><strong><?= date('d/m/Y H:i', strtotime($row['appointment_date'])) ?></strong></td>
                                            <td>
                                                <?php
                                                // Xử lý hiển thị màu sắc trạng thái
                                                $s = $row['status'];
                                                if ($s == 'pending') echo '<span class="badge badge-pending p-2">Chờ duyệt</span>';
                                                elseif ($s == 'confirmed') echo '<span class="badge badge-confirmed p-2">Đã duyệt</span>';
                                                elseif ($s == 'cancelled') echo '<span class="badge badge-cancelled p-2">Đã hủy</span>';
                                                elseif (empty($s)) echo '<span class="badge badge-danger p-2">Lỗi (Rỗng)</span>'; // Bắt trường hợp lỗi
                                                else echo '<span class="badge badge-secondary p-2">' . $s . '</span>';
                                                ?>
                                            </td>
                                            <td class="text-center">
                                                <form method="POST" style="display:inline-block;">
                                                    <input type="hidden" name="order_id" value="<?= $row['id'] ?>">

                                                    <?php if ($row['status'] == 'pending' || empty($row['status'])): ?>
                                                        <button type="submit" name="update_status" value="confirmed" class="btn btn-success btn-sm" title="Duyệt">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                        <button type="submit" name="update_status" value="cancelled" class="btn btn-danger btn-sm" title="Hủy" onclick="return confirm('Hủy lịch này?');">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    <?php else: ?>
                                                        <button type="button" class="btn btn-light btn-sm" disabled><i class="fas fa-lock"></i></button>
                                                    <?php endif; ?>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8" class="text-center py-5 text-muted">
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
    </div>

</body>

</html>