<?php
session_start();
require_once '../config.php';
$stmt = $pdo->query("SELECT o.*, u.full_name FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC");
$orders = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý đơn hàng</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container my-5">
    <h2>Quản lý đơn hàng</h2>
    <a href="index.php" class="btn btn-secondary mb-3">Về trang chủ admin</a>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Mã đơn hàng</th>
                <th>Khách hàng</th>
                <th>Tổng tiền</th>
                <th>Trạng thái</th>
                <th>Ngày tạo</th>
                <th>Thao tác</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($orders as $o): ?>
            <tr>
                <td><?= htmlspecialchars($o['id']) ?></td>
                <td><?= htmlspecialchars($o['full_name']) ?></td>
                <td><?= number_format($o['total_amount'],0,',','.') ?>đ</td>
                <td><?= htmlspecialchars($o['status']) ?></td>
                <td><?= date('d/m/Y', strtotime($o['created_at'])) ?></td>
                <td>
                    <a href="order_view.php?id=<?= $o['id'] ?>" class="btn btn-info btn-sm">Xem</a>
                    <a href="order_delete.php?id=<?= $o['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Xóa đơn hàng này?')">Xóa</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>