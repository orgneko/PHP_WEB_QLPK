<?php
session_start();

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Kết nối database
$host = 'localhost';
$dbname = 'phongkham';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Lỗi kết nối database: " . $e->getMessage());
}

$user_id = $_SESSION['user_id'];
$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$message = '';
$error = '';

// Kiểm tra order_id hợp lệ
if (!$order_id) {
    header('Location: my_orders.php');
    exit();
}

// Lấy thông tin đơn hàng
$stmt = $pdo->prepare("
    SELECT o.*, u.full_name, u.email, u.phone 
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    WHERE o.id = ? AND o.user_id = ?
");
$stmt->execute([$order_id, $user_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    $error = 'Không tìm thấy đơn hàng hoặc bạn không có quyền truy cập!';
}

// Lấy chi tiết Dịch vụ trong đơn hàng
$order_items = [];
$total_amount = 0;
if ($order) {
    $stmt = $pdo->prepare("
        SELECT oi.*, p.name as product_name, p.image_url as product_image 
        FROM order_items oi 
        LEFT JOIN products p ON oi.product_id = p.id 
        WHERE oi.order_id = ?
    ");
    $stmt->execute([$order_id]);
    $order_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Tính tổng tiền
    foreach ($order_items as $item) {
        $total_amount += $item['quantity'] * $item['price'];
    }
}

// Xử lý hủy đơn hàng
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_cancel'])) {
    if (!$order) {
        $error = 'Đơn hàng không tồn tại!';
    } elseif ($order['status'] !== 'pending') {
        $error = 'Chỉ có thể hủy đơn hàng đang chờ xác nhận!';
    } else {
        try {
            $pdo->beginTransaction();

            // Cập nhật trạng thái đơn hàng (KHÔNG lưu lý do hủy)
            $stmt = $pdo->prepare("
                UPDATE orders 
                SET status = 'cancelled'
                WHERE id = ? AND user_id = ?
            ");
            $stmt->execute([$order_id, $user_id]);

            // Hoàn lại số lượng Dịch vụ vào kho
            foreach ($order_items as $item) {
                $stmt = $pdo->prepare("
                    UPDATE products 
                    SET stock_quantity = stock_quantity + ? 
                    WHERE id = ?
                ");
                $stmt->execute([$item['quantity'], $item['product_id']]);
            }

            $pdo->commit();
            $message = 'Đơn hàng đã được hủy thành công!';

            // Refresh thông tin đơn hàng
            $order['status'] = 'cancelled';
        } catch (Exception $e) {
            $pdo->rollback();
            $error = 'Có lỗi xảy ra khi hủy đơn hàng: ' . $e->getMessage();
        }
    }
}

// Kiểm tra xem có thể hủy đơn hàng không
$can_cancel = $order && $order['status'] === 'pending';
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hủy đơn hàng - SportWear Shop</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 700px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            color: white;
            padding: 25px;
            text-align: center;
        }

        .header h1 {
            font-size: 28px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .header p {
            opacity: 0.9;
            font-size: 16px;
        }

        .content {
            padding: 30px;
        }

        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 500;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .alert-warning {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }

        .order-info {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 25px;
            border: 1px solid #e9ecef;
        }

        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            flex-wrap: wrap;
            gap: 10px;
        }

        .order-id {
            font-size: 20px;
            font-weight: 600;
            color: #333;
        }

        .order-status {
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            color: white;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-pending {
            background: #ffc107;
        }

        .status-confirmed {
            background: #17a2b8;
        }

        .status-shipping {
            background: #007bff;
        }

        .status-delivered {
            background: #28a745;
        }

        .status-cancelled {
            background: #dc3545;
        }

        .order-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .detail-item {
            text-align: center;
            padding: 12px;
            background: white;
            border-radius: 8px;
            border: 1px solid #e9ecef;
        }

        .detail-label {
            font-size: 12px;
            color: #666;
            margin-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .detail-value {
            font-size: 16px;
            font-weight: 600;
            color: #333;
        }

        .price {
            color: #e74c3c;
        }

        .products-section {
            margin-bottom: 25px;
        }

        .products-section h3 {
            margin-bottom: 15px;
            color: #333;
            font-size: 18px;
        }

        .product-item {
            display: flex;
            align-items: center;
            padding: 12px;
            background: white;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            margin-bottom: 10px;
        }

        .product-image {
            width: 60px;
            height: 60px;
            border-radius: 8px;
            object-fit: cover;
            margin-right: 15px;
            background: #f8f9fa;
        }

        .product-info {
            flex: 1;
        }

        .product-name {
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
        }

        .product-details {
            font-size: 14px;
            color: #666;
        }

        .cancel-form {
            background: #fff5f5;
            border: 1px solid #fed7d7;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 25px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }

        .form-control {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        textarea.form-control {
            resize: vertical;
            min-height: 100px;
        }

        .actions {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            min-width: 140px;
            justify-content: center;
        }

        .btn-danger {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            color: white;
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        }

        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .warning-box {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }

        .warning-box h4 {
            color: #856404;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .warning-box ul {
            color: #856404;
            margin-left: 20px;
        }

        .warning-box li {
            margin-bottom: 5px;
        }

        @media (max-width: 768px) {
            .container {
                margin: 10px;
            }

            .content {
                padding: 20px 15px;
            }

            .order-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .order-details {
                grid-template-columns: 1fr;
            }

            .product-item {
                flex-direction: column;
                text-align: center;
            }

            .product-image {
                margin-right: 0;
                margin-bottom: 10px;
            }

            .actions {
                flex-direction: column;
            }

            .btn {
                width: 100%;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>
                ❌ Hủy đơn hàng
            </h1>
            <p>Xác nhận hủy đơn hàng của bạn</p>
        </div>

        <div class="content">
            <?php if ($message): ?>
                <div class="alert alert-success">
                    ✅ <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-error">
                    ❌ <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if ($order): ?>
                <!-- Thông tin đơn hàng -->
                <div class="order-info">
                    <div class="order-header">
                        <div class="order-id">Đơn hàng #<?php echo htmlspecialchars($order['order_code'] ?? $order['id']); ?></div>
                        <div class="order-status status-<?php echo $order['status']; ?>">
                            <?php
                            $status_text = [
                                'pending' => 'Chờ xác nhận',
                                'confirmed' => 'Đã xác nhận',
                                'shipping' => 'Đang giao hàng',
                                'delivered' => 'Đã giao hàng',
                                'cancelled' => 'Đã hủy'
                            ];
                            echo $status_text[$order['status']] ?? 'Không xác định';
                            ?>
                        </div>
                    </div>

                    <div class="order-details">
                        <div class="detail-item">
                            <div class="detail-label">Ngày đặt hàng</div>
                            <div class="detail-value"><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Tổng tiền</div>
                            <div class="detail-value price"><?php echo number_format($total_amount, 0, ',', '.'); ?> VNĐ</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Phương thức thanh toán</div>
                            <div class="detail-value"><?php echo htmlspecialchars($order['payment_method'] ?? 'COD'); ?></div>
                        </div>
                    </div>
                </div>

                <!-- Danh sách Dịch vụ -->
                <?php if (!empty($order_items)): ?>
                    <div class="products-section">
                        <h3>📦 Dịch vụ trong đơn hàng</h3>
                        <?php foreach ($order_items as $item): ?>
                            <div class="product-item">
                                <?php if ($item['product_image']): ?>
                                    <img src="<?php echo htmlspecialchars($item['product_image']); ?>"
                                        alt="<?php echo htmlspecialchars($item['product_name']); ?>"
                                        class="product-image">
                                <?php else: ?>
                                    <div class="product-image" style="display: flex; align-items: center; justify-content: center; color: #999;">
                                        📷
                                    </div>
                                <?php endif; ?>
                                <div class="product-info">
                                    <div class="product-name"><?php echo htmlspecialchars($item['product_name'] ?? 'Dịch vụ không xác định'); ?></div>
                                    <div class="product-details">
                                        Số lượng: <?php echo $item['quantity']; ?> ×
                                        <?php echo number_format($item['price'], 0, ',', '.'); ?> VNĐ =
                                        <span class="price"><?php echo number_format($item['quantity'] * $item['price'], 0, ',', '.'); ?> VNĐ</span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if ($order['status'] === 'cancelled'): ?>
                    <div class="alert alert-warning">
                        ℹ️ Đơn hàng này đã được hủy trước đó.
                    </div>
                    <div class="actions">
                        <a href="my_orders.php" class="btn btn-primary">
                            ← Quay lại danh sách đơn hàng
                        </a>
                    </div>
                <?php elseif (!$can_cancel): ?>
                    <div class="alert alert-warning">
                        ⚠️ Không thể hủy đơn hàng này vì trạng thái hiện tại không cho phép.
                    </div>
                    <div class="actions">
                        <a href="my_orders.php" class="btn btn-primary">
                            ← Quay lại danh sách đơn hàng
                        </a>
                    </div>
                <?php else: ?>
                    <!-- Form hủy đơn hàng -->
                    <div class="warning-box">
                        <h4>⚠️ Lưu ý khi hủy đơn hàng:</h4>
                        <ul>
                            <li>Đơn hàng chỉ có thể hủy khi đang ở trạng thái "Chờ xác nhận"</li>
                            <li>Sau khi hủy, bạn không thể khôi phục lại đơn hàng</li>
                            <li>Nếu đã thanh toán, số tiền sẽ được hoàn lại trong 3-5 ngày làm việc</li>
                            <li>Dịch vụ sẽ được trả lại kho tự động</li>
                        </ul>
                    </div>

                    <form method="POST" class="cancel-form">
                        <div class="actions">
                            <button type="submit" name="confirm_cancel" class="btn btn-danger">
                                Xác nhận hủy đơn hàng
                            </button>
                            <a href="my_orders.php" class="btn btn-secondary">
                                Hủy bỏ
                            </a>
                        </div>
                    </form>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>