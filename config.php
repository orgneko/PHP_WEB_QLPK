<?php
// Cấu hình kết nối database
define('DB_HOST', 'localhost');
define('DB_NAME', 'sportswear_shop');
define('DB_USER', 'root');
define('DB_PASS', '');

// Kết nối đến database
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Lỗi kết nối database: " . $e->getMessage());
}

// Khởi tạo session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Hàm kiểm tra đăng nhập
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Hàm kiểm tra admin
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Hàm chuyển hướng
function redirect($url) {
    header("Location: " . $url);
    exit();
}

// Hàm làm sạch dữ liệu đầu vào
function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

// Hàm format tiền tệ
function formatPrice($price) {
    return number_format($price, 0, ',', '.') . ' VNĐ';
}

// Hàm tạo mã đơn hàng
function generateOrderNumber() {
    return 'DH' . date('Ymd') . rand(1000, 9999);
}
?>