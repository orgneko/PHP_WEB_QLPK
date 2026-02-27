<?php
// filepath: c:\xampp\htdocs\sportshop1\admin\order_delete.php
session_start();
require_once '../../config/config.php';

$order_id = $_GET['id'] ?? 0;

// Xóa các Dịch vụ trong đơn hàng trước (nếu có bảng booking_details)
$stmt = $pdo->prepare("DELETE FROM booking_details WHERE order_id=?");
$stmt->execute([$order_id]);

// Xóa đơn hàng
$stmt = $pdo->prepare("DELETE FROM orders WHERE id=?");
$stmt->execute([$order_id]);

header('Location: ../views/orders.php');
exit;
