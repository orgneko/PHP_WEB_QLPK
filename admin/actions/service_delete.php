<?php
session_start();
require_once '../../config/config.php';

// Kiểm tra quyền admin (nếu có phân quyền)
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../views/login.php');
    exit;
}

// Kiểm tra id hợp lệ
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id > 0) {
    $stmt = $pdo->prepare("DELETE FROM services WHERE id = ?");
    $stmt->execute([$id]);
}

header('Location: services.php');
exit;
