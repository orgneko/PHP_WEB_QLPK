<?php
require_once '../config/config.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Phương thức không hợp lệ']);
    exit;
}

$user_id = $_SESSION['user_id'];
$product_id = (int)$_POST['product_id'];
$quantity = (int)($_POST['quantity'] ?? 1);
$size = sanitizeInput($_POST['size'] ?? '');
$color = sanitizeInput($_POST['color'] ?? '');

try {
    // Kiểm tra Dịch vụ có tồn tại không
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? AND status = 'active'");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();

    if (!$product) {
        echo json_encode(['success' => false, 'message' => 'Dịch vụ không tồn tại']);
        exit;
    }

    // Kiểm tra số lượng tồn kho
    if ($quantity > $product['stock_quantity']) {
        echo json_encode(['success' => false, 'message' => 'Số lượng vượt quá tồn kho']);
        exit;
    }

    // Kiểm tra xem Dịch vụ đã có trong giỏ hàng chưa
    $stmt = $pdo->prepare("SELECT * FROM cart WHERE user_id = ? AND product_id = ? AND size = ? AND color = ?");
    $stmt->execute([$user_id, $product_id, $size, $color]);
    $existing_item = $stmt->fetch();

    if ($existing_item) {
        // Cập nhật số lượng
        $new_quantity = $existing_item['quantity'] + $quantity;
        
        if ($new_quantity > $product['stock_quantity']) {
            echo json_encode(['success' => false, 'message' => 'Số lượng vượt quá tồn kho']);
            exit;
        }

        $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
        $stmt->execute([$new_quantity, $existing_item['id']]);
    } else {
        // Thêm mới vào giỏ hàng
        $stmt = $pdo->prepare("INSERT INTO cart (user_id, product_id, quantity, size, color) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $product_id, $quantity, $size, $color]);
    }

    echo json_encode(['success' => true, 'message' => 'Đã Đăng ký khám hàng']);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra: ' . $e->getMessage()]);
}
?>