<?php
session_start();
require_once '../config.php';

// Xử lý xóa sản phẩm
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $pdo->prepare("DELETE FROM products WHERE id=?")->execute([$id]);
    header('Location: products.php');
    exit();
}

// Xử lý thêm sản phẩm
if (isset($_POST['add'])) {
    $stmt = $pdo->prepare("INSERT INTO products (name, code, category_id, supplier_id, description, price, sale_price, stock_quantity, sizes, colors, image_url, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $_POST['name'], $_POST['code'], $_POST['category_id'], $_POST['supplier_id'], $_POST['description'],
        $_POST['price'], $_POST['sale_price'], $_POST['stock_quantity'], $_POST['sizes'], $_POST['colors'],
        $_POST['image_url'], $_POST['status']
    ]);
    header('Location: products.php');
    exit();
}

// Xử lý sửa sản phẩm
if (isset($_POST['edit'])) {
    $stmt = $pdo->prepare("UPDATE products SET name=?, code=?, category_id=?, supplier_id=?, description=?, price=?, sale_price=?, stock_quantity=?, sizes=?, colors=?, image_url=?, status=? WHERE id=?");
    $stmt->execute([
        $_POST['name'], $_POST['code'], $_POST['category_id'], $_POST['supplier_id'], $_POST['description'],
        $_POST['price'], $_POST['sale_price'], $_POST['stock_quantity'], $_POST['sizes'], $_POST['colors'],
        $_POST['image_url'], $_POST['status'], $_POST['id']
    ]);
    header('Location: products.php');
    exit();
}

// Lấy danh sách sản phẩm
$products = $pdo->query("SELECT * FROM products ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
// Lấy danh mục và nhà cung cấp cho select box
$categories = $pdo->query("SELECT * FROM categories")->fetchAll(PDO::FETCH_ASSOC);
$suppliers = $pdo->query("SELECT * FROM suppliers")->fetchAll(PDO::FETCH_ASSOC);

// Nếu sửa, lấy thông tin sản phẩm
$edit_product = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id=?");
    $stmt->execute([$_GET['edit']]);
    $edit_product = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý sản phẩm</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container my-5">
    <h2>Quản lý sản phẩm</h2>
    <a href="index.php" class="btn btn-secondary mb-3">Về trang chủ admin</a>
    <!-- Form thêm/sửa sản phẩm -->
    <div class="card mb-4">
        <div class="card-header"><?= $edit_product ? 'Sửa sản phẩm' : 'Thêm sản phẩm mới' ?></div>
        <div class="card-body">
            <form method="post">
                <?php if ($edit_product): ?>
                    <input type="hidden" name="id" value="<?= $edit_product['id'] ?>">
                <?php endif; ?>
                <div class="row">
                    <div class="col-md-4 mb-2">
                        <label>Tên sản phẩm</label>
                        <input type="text" name="name" class="form-control" required value="<?= $edit_product['name'] ?? '' ?>">
                    </div>
                    <div class="col-md-2 mb-2">
                        <label>Mã SP</label>
                        <input type="text" name="code" class="form-control" required value="<?= $edit_product['code'] ?? '' ?>">
                    </div>
                    <div class="col-md-2 mb-2">
                        <label>Danh mục</label>
                        <select name="category_id" class="form-control" required>
                            <option value="">--Chọn--</option>
                            <?php foreach ($categories as $c): ?>
                                <option value="<?= $c['id'] ?>" <?= (isset($edit_product['category_id']) && $edit_product['category_id'] == $c['id']) ? 'selected' : '' ?>><?= $c['name'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2 mb-2">
                        <label>Nhà cung cấp</label>
                        <select name="supplier_id" class="form-control">
                            <option value="">--Chọn--</option>
                            <?php foreach ($suppliers as $s): ?>
                                <option value="<?= $s['id'] ?>" <?= (isset($edit_product['supplier_id']) && $edit_product['supplier_id'] == $s['id']) ? 'selected' : '' ?>><?= $s['name'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2 mb-2">
                        <label>Giá</label>
                        <input type="number" name="price" class="form-control" required value="<?= $edit_product['price'] ?? '' ?>">
                    </div>
                    <div class="col-md-2 mb-2">
                        <label>Giá KM</label>
                        <input type="number" name="sale_price" class="form-control" value="<?= $edit_product['sale_price'] ?? '' ?>">
                    </div>
                    <div class="col-md-2 mb-2">
                        <label>Tồn kho</label>
                        <input type="number" name="stock_quantity" class="form-control" required value="<?= $edit_product['stock_quantity'] ?? '' ?>">
                    </div>
                    <div class="col-md-2 mb-2">
                        <label>Kích cỡ</label>
                        <input type="text" name="sizes" class="form-control" value="<?= $edit_product['sizes'] ?? '' ?>">
                    </div>
                    <div class="col-md-2 mb-2">
                        <label>Màu sắc</label>
                        <input type="text" name="colors" class="form-control" value="<?= $edit_product['colors'] ?? '' ?>">
                    </div>
                    <div class="col-md-3 mb-2">
                        <label>Ảnh (URL)</label>
                        <input type="text" name="image_url" class="form-control" value="<?= $edit_product['image_url'] ?? '' ?>">
                    </div>
                    <div class="col-md-2 mb-2">
                        <label>Trạng thái</label>
                        <select name="status" class="form-control">
                            <option value="active" <?= (isset($edit_product['status']) && $edit_product['status'] == 'active') ? 'selected' : '' ?>>Hiển thị</option>
                            <option value="inactive" <?= (isset($edit_product['status']) && $edit_product['status'] == 'inactive') ? 'selected' : '' ?>>Ẩn</option>
                        </select>
                    </div>
                </div>
                <div class="mb-2">
                    <label>Mô tả</label>
                    <textarea name="description" class="form-control"><?= $edit_product['description'] ?? '' ?></textarea>
                </div>
                <button class="btn btn-success" name="<?= $edit_product ? 'edit' : 'add' ?>">
                    <?= $edit_product ? 'Cập nhật' : 'Thêm mới' ?>
                </button>
                <?php if ($edit_product): ?>
                    <a href="products.php" class="btn btn-secondary">Hủy</a>
                <?php endif; ?>
            </form>
        </div>
    </div>
    <!-- Danh sách sản phẩm -->
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Tên SP</th>
                <th>Mã</th>
                <th>Danh mục</th>
                <th>Nhà cung cấp</th>
                <th>Giá</th>
                <th>KM</th>
                <th>Tồn kho</th>
                <th>Trạng thái</th>
                <th>Ảnh</th>
                <th>Thao tác</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($products as $p): ?>
            <tr>
                <td><?= $p['id'] ?></td>
                <td><?= htmlspecialchars($p['name']) ?></td>
                <td><?= htmlspecialchars($p['code']) ?></td>
                <td>
                    <?php
                    $cat = array_filter($categories, fn($c) => $c['id'] == $p['category_id']);
                    echo $cat ? htmlspecialchars(array_values($cat)[0]['name']) : '';
                    ?>
                </td>
                <td>
                    <?php
                    $sup = array_filter($suppliers, fn($s) => $s['id'] == $p['supplier_id']);
                    echo $sup ? htmlspecialchars(array_values($sup)[0]['name']) : '';
                    ?>
                </td>
                <td><?= number_format($p['price'],0,',','.') ?>đ</td>
                <td><?= number_format($p['sale_price'],0,',','.') ?>đ</td>
                <td><?= $p['stock_quantity'] ?></td>
                <td><?= $p['status'] == 'active' ? 'Hiển thị' : 'Ẩn' ?></td>
                <td>
                    <?php if ($p['image_url']): ?>
                        <img src="<?= htmlspecialchars($p['image_url']) ?>" style="width:40px;height:40px;object-fit:cover;">
                    <?php endif; ?>
                </td>
                <td>
                    <a href="products.php?edit=<?= $p['id'] ?>" class="btn btn-warning btn-sm">Sửa</a>
                    <a href="products.php?delete=<?= $p['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Xóa sản phẩm này?')">Xóa</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>