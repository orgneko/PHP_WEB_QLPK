<?php
session_start();
require_once '../config.php';
$products = $pdo->query("SELECT * FROM products")->fetchAll();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Báo cáo tồn kho</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .low-stock { background: #fff3cd !important; }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h2 class="mb-4">Báo cáo tồn kho sản phẩm</h2>
        <a href="index.php" class="btn btn-secondary mb-3">Về trang chủ admin</a>
        <table class="table table-bordered table-hover">
            <thead class="table-dark">
                <tr>
                    <th>Mã SP</th>
                    <th>Tên sản phẩm</th>
                    <th>Danh mục</th>
                    <th>Tồn kho</th>
                    <th>Cảnh báo</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $p): ?>
                    <tr<?php if ($p['stock_quantity'] <= 5) echo ' class="low-stock"'; ?>>
                        <td><?= htmlspecialchars($p['code']) ?></td>
                        <td><?= htmlspecialchars($p['name']) ?></td>
                        <td><?= htmlspecialchars($p['category_id']) ?></td>
                        <td><?= (int)$p['stock_quantity'] ?></td>
                        <td>
                            <?php if ($p['stock_quantity'] <= 5): ?>
                                <span class="text-danger fw-bold">Sắp hết hàng!</span>
                            <?php else: ?>
                                <span class="text-success">OK</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>