<?php
// filepath: c:\xampp\htdocs\sportshop1\admin\order_delete.php
session_start();
require_once '../config.php';

$order_id = $_GET['id'] ?? 0;

// Xóa các sản phẩm trong đơn hàng trước (nếu có bảng order_items)
$stmt = $pdo->prepare("DELETE FROM order_items WHERE order_id=?");
$stmt->execute([$order_id]);

// Xóa đơn hàng
$stmt = $pdo->prepare("DELETE FROM orders WHERE id=?");
$stmt->execute([$order_id]);

header('Location: orders.php');
exit;