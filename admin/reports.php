<?php
// filepath: c:\xampp\htdocs\sportshop1\admin\reports.php
session_start();
require_once '../config.php';

// Tổng số Dịch vụ
$total_products = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
// Tổng số đơn hàng
$total_orders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
// Tổng doanh thu
$total_revenue = $pdo->query("SELECT SUM(total_amount) FROM orders WHERE status='completed'")->fetchColumn();
// Đơn hàng hôm nay
$today_orders = $pdo->query("SELECT COUNT(*) FROM orders WHERE DATE(created_at) = CURDATE()")->fetchColumn();
// Doanh thu hôm nay
$today_revenue = $pdo->query("SELECT SUM(total_amount) FROM orders WHERE status='completed' AND DATE(created_at) = CURDATE()")->fetchColumn();
// Top 5 Dịch vụ bán chạy
$top_products = $pdo->query("
    SELECT p.name, SUM(oi.quantity) as total_sold
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    JOIN orders o ON oi.order_id = o.id
    WHERE o.status='completed'
    GROUP BY oi.product_id
    ORDER BY total_sold DESC
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);
// Top 5 khách hàng mua nhiều nhất
$top_customers = $pdo->query("
    SELECT u.full_name, COUNT(o.id) as orders_count, SUM(o.total_amount) as spent
    FROM orders o
    JOIN users u ON o.user_id = u.id
    WHERE o.status='completed'
    GROUP BY o.user_id
    ORDER BY spent DESC
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Báo cáo thống kê</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .stat-card { background: #f8f9fa; border-radius: 10px; padding: 24px; text-align: center; margin-bottom: 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.04);}
        .stat-value { font-size: 2rem; font-weight: bold; color: #007bff;}
        .section-title { margin-top: 32px; margin-bottom: 16px; }
    </style>
</head>
<body>
<div class="container my-5">
    <h2 class="mb-4">Báo cáo thống kê</h2>
    <a href="index.php" class="btn btn-secondary mb-3">Về trang chủ admin</a>
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-label">Tổng Dịch vụ</div>
                <div class="stat-value"><?= $total_products ?></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-label">Tổng đơn hàng</div>
                <div class="stat-value"><?= $total_orders ?></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-label">Tổng doanh thu</div>
                <div class="stat-value"><?= number_format($total_revenue,0,',','.') ?>đ</div>
            </div>
        </div>
    </div>
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="stat-card">
                <div class="stat-label">Đơn hàng hôm nay</div>
                <div class="stat-value"><?= $today_orders ?></div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="stat-card">
                <div class="stat-label">Doanh thu hôm nay</div>
                <div class="stat-value"><?= number_format($today_revenue,0,',','.') ?>đ</div>
            </div>
        </div>
    </div>
    <h4 class="section-title">Top 5 Dịch vụ bán chạy</h4>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Dịch vụ</th>
                <th>Số lượng bán</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($top_products as $p): ?>
            <tr>
                <td><?= htmlspecialchars($p['name']) ?></td>
                <td><?= $p['total_sold'] ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <h4 class="section-title">Top 5 khách hàng mua nhiều nhất</h4>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Khách hàng</th>
                <th>Số đơn</th>
                <th>Tổng chi tiêu</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($top_customers as $c): ?>
            <tr>
                <td><?= htmlspecialchars($c['full_name']) ?></td>
                <td><?= $c['orders_count'] ?></td>
                <td><?= number_format($c['spent'],0,',','.') ?>đ</td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>