<?php
// filepath: c:\xampp\htdocs\sportshop1\admin\product_action.php
session_start();
require_once '../config.php';

header('Content-Type: application/json');

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$id = $_POST['id'] ?? $_GET['id'] ?? 0;

if ($action === 'add') {
    $stmt = $pdo->prepare("INSERT INTO products (name, description, price, sale_price, stock_quantity, image_url, category_id, supplier_id, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
    $stmt->execute([
        $_POST['name'],
        $_POST['description'],
        $_POST['price'],
        $_POST['sale_price'] ?? null,
        $_POST['stock_quantity'],
        $_POST['image_url'] ?? '',
        $_POST['category_id'],
        $_POST['supplier_id'],
        $_POST['status']
    ]);
    echo json_encode(['success' => true, 'message' => 'Thêm sản phẩm thành công!']);
    exit;
}

if ($action === 'get' && $id) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$id]);
    echo json_encode($stmt->fetch());
    exit;
}

if ($action === 'update' && $id) {
    $stmt = $pdo->prepare("UPDATE products SET name=?, description=?, price=?, sale_price=?, stock_quantity=?, image_url=?, category_id=?, supplier_id=?, status=? WHERE id=?");
    $stmt->execute([
        $_POST['name'],
        $_POST['description'],
        $_POST['price'],
        $_POST['sale_price'] ?? null,
        $_POST['stock_quantity'],
        $_POST['image_url'] ?? '',
        $_POST['category_id'],
        $_POST['supplier_id'],
        $_POST['status'],
        $id
    ]);
    echo json_encode(['success' => true, 'message' => 'Cập nhật sản phẩm thành công!']);
    exit;
}

if ($action === 'delete' && $id) {
    $stmt = $pdo->prepare("DELETE FROM products WHERE id=?");
    $stmt->execute([$id]);
    echo json_encode(['success' => true, 'message' => 'Xóa sản phẩm thành công!']);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Yêu cầu không hợp lệ!']);