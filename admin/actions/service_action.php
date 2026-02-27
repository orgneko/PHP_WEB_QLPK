<?php
session_start();
require_once '../../config/config.php';

header('Content-Type: application/json');

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$id = $_POST['id'] ?? $_GET['id'] ?? 0;

if ($action === 'add') {
    $stmt = $pdo->prepare("INSERT INTO services (name, description, price, sale_price, stock_quantity, image_url, category_id, supplier_id, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
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
    echo json_encode(['success' => true, 'message' => 'Thêm Dịch vụ thành công!']);
    exit;
}

if ($action === 'get' && $id) {
    $stmt = $pdo->prepare("SELECT * FROM services WHERE id = ?");
    $stmt->execute([$id]);
    echo json_encode($stmt->fetch());
    exit;
}

if ($action === 'update' && $id) {
    $stmt = $pdo->prepare("UPDATE services SET name=?, description=?, price=?, sale_price=?, stock_quantity=?, image_url=?, category_id=?, supplier_id=?, status=? WHERE id=?");
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
    echo json_encode(['success' => true, 'message' => 'Cập nhật Dịch vụ thành công!']);
    exit;
}

if ($action === 'delete' && $id) {
    $stmt = $pdo->prepare("DELETE FROM services WHERE id=?");
    $stmt->execute([$id]);
    echo json_encode(['success' => true, 'message' => 'Xóa Dịch vụ thành công!']);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Yêu cầu không hợp lệ!']);
