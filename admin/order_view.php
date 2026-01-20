<?php
// filepath: c:\xampp\htdocs\sportshop1\admin\order_view.php
session_start();
require_once '../config.php';

$order_id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("SELECT o.*, u.full_name, u.email, u.phone, u.address FROM orders o JOIN users u ON o.user_id = u.id WHERE o.id=?");
$stmt->execute([$order_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

$items = [];
if ($order) {
    $stmt = $pdo->prepare("SELECT oi.*, p.name FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id=?");
    $stmt->execute([$order_id]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Chi tiết đơn hàng</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container my-5">
    <h2>Chi tiết đơn hàng #<?= htmlspecialchars($order_id) ?></h2>
    <?php if ($order): ?>
        <div class="mb-3">
            <strong>Khách hàng:</strong> <?= htmlspecialchars($order['full_name']) ?><br>
            <strong>Email:</strong> <?= htmlspecialchars($order['email']) ?><br>
            <strong>Điện thoại:</strong> <?= htmlspecialchars($order['phone']) ?><br>
            <strong>Địa chỉ:</strong> <?= htmlspecialchars($order['address']) ?><br>
            <strong>Ngày đặt:</strong> <?= date('d/m/Y H:i', strtotime($order['created_at'])) ?><br>
            <strong>Trạng thái:</strong> <?= htmlspecialchars($order['status']) ?><br>
            <strong>Tổng tiền:</strong> <?= number_format($order['total_amount'],0,',','.') ?>đ
        </div>
        <h5>Dịch vụ trong đơn hàng</h5>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Tên Dịch vụ</th>
                    <th>Số lượng</th>
                    <th>Giá</th>
                    <th>Thành tiền</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                <tr>
                    <td><?= htmlspecialchars($item['name']) ?></td>
                    <td><?= $item['quantity'] ?></td>
                    <td><?= number_format($item['price'],0,',','.') ?>đ</td>
                    <td><?= number_format($item['price'] * $item['quantity'],0,',','.') ?>đ</td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <a href="orders.php" class="btn btn-secondary">Quay lại</a>
    <?php else: ?>
        <div class="alert alert-danger">Không tìm thấy đơn hàng!</div>
        <a href="orders.php" class="btn btn-secondary">Quay lại</a>
    <?php endif; ?>
</div>
</body>
</html>