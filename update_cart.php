<?php
// filepath: c:\xampp\htdocs\sportshop1\update_cart.php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Bạn chưa đăng nhập!']);
    exit;
}

$user_id = $_SESSION['user_id'];
$action = $_POST['action'] ?? '';
$cart_id = $_POST['cart_id'] ?? 0;

if ($action === 'remove' && $cart_id) {
    $stmt = $pdo->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
    if ($stmt->execute([$cart_id, $user_id])) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Không thể xóa sản phẩm']);
    }
    exit;
}

if ($action === 'update' && $cart_id && isset($_POST['quantity'])) {
    $quantity = max(1, intval($_POST['quantity']));
    $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?");
    if ($stmt->execute([$quantity, $cart_id, $user_id])) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Không thể cập nhật số lượng']);
    }
    exit;
}

echo json_encode(['success' => false, 'message' => 'Yêu cầu không hợp lệ!']);