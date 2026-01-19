<?php
session_start();
require_once '../config.php';

$id = (int)$_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

$categories = $pdo->query("SELECT * FROM categories")->fetchAll();
$suppliers = $pdo->query("SELECT * FROM suppliers")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("UPDATE products SET name=?, description=?, price=?, sale_price=?, stock_quantity=?, image_url=?, category_id=?, supplier_id=?, status=? WHERE id=?");
    $stmt->execute([
        $_POST['name'],
        $_POST['description'],
        $_POST['price'],
        $_POST['sale_price'] ?: null,
        $_POST['stock_quantity'],
        $_POST['image_url'],
        $_POST['category_id'],
        $_POST['supplier_id'],
        $_POST['status'],
        $id
    ]);
    header('Location: products.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Sửa sản phẩm</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container my-5">
    <h2>Sửa sản phẩm</h2>
    <form method="post">
        <div class="mb-3">
            <label>Tên sản phẩm</label>
            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($product['name']) ?>" required>
        </div>
        <div class="mb-3">
            <label>Mô tả</label>
            <textarea name="description" class="form-control"><?= htmlspecialchars($product['description']) ?></textarea>
        </div>
        <div class="mb-3">
            <label>Giá</label>
            <input type="number" name="price" class="form-control" value="<?= $product['price'] ?>" required>
        </div>
        <div class="mb-3">
            <label>Giá khuyến mãi</label>
            <input type="number" name="sale_price" class="form-control" value="<?= $product['sale_price'] ?>">
        </div>
        <div class="mb-3">
            <label>Tồn kho</label>
            <input type="number" name="stock_quantity" class="form-control" value="<?= $product['stock_quantity'] ?>" required>
        </div>
        <div class="mb-3">
            <label>Ảnh (URL)</label>
            <input type="text" name="image_url" class="form-control" value="<?= htmlspecialchars($product['image_url']) ?>">
        </div>
        <div class="mb-3">
            <label>Danh mục</label>
            <select name="category_id" class="form-control" required>
                <?php foreach ($categories as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= $product['category_id']==$c['id']?'selected':'' ?>><?= htmlspecialchars($c['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label>Nhà cung cấp</label>
            <select name="supplier_id" class="form-control" required>
                <?php foreach ($suppliers as $s): ?>
                    <option value="<?= $s['id'] ?>" <?= $product['supplier_id']==$s['id']?'selected':'' ?>><?= htmlspecialchars($s['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label>Trạng thái</label>
            <select name="status" class="form-control">
                <option value="active" <?= $product['status']=='active'?'selected':'' ?>>Đang bán</option>
                <option value="inactive" <?= $product['status']=='inactive'?'selected':'' ?>>Ngừng bán</option>
            </select>
        </div>
        <button type="submit" class="btn btn-success">Lưu</button>
        <a href="products.php" class="btn btn-secondary">Quay lại</a>
    </form>
</div>
</body>
</html>