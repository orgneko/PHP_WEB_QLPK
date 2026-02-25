<?php
session_start();
include '../config/config.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Lấy thông tin đơn hàng
$order_id = isset($_GET['order_id']) ? $_GET['order_id'] : 0;
$user_id = $_SESSION['user_id'];

$order_sql = "SELECT * FROM orders WHERE id = ? AND user_id = ?";
$stmt = $pdo->prepare($order_sql);
$stmt->execute([$order_id, $user_id]);
$order = $stmt->fetch();

if (!$order) {
    header('Location: index.php');
    exit();
}

// Lấy chi tiết đơn hàng
$details_sql = "SELECT oi.*, p.name AS ten_san_pham, p.image_url AS hinh_anh 
                FROM order_items oi
                JOIN products p ON oi.product_id = p.id
                WHERE oi.order_id = ?";
$stmt = $pdo->prepare($details_sql);
$stmt->execute([$order_id]);
$order_details = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt hàng thành công - SportShop</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            line-height: 1.6;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }

        .success-card {
            background: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
            margin-bottom: 30px;
        }

        .success-icon {
            font-size: 80px;
            color: #28a745;
            margin-bottom: 20px;
        }

        .success-title {
            font-size: 28px;
            color: #333;
            margin-bottom: 10px;
        }

        .success-message {
            font-size: 16px;
            color: #666;
            margin-bottom: 20px;
        }

        .order-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .order-details {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .section-title {
            font-size: 20px;
            font-weight: bold;
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #007bff;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding: 5px 0;
        }

        .info-label {
            font-weight: bold;
            color: #666;
        }

        .info-value {
            color: #333;
        }

        .order-item {
            display: flex;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #eee;
        }

        .order-item:last-child {
            border-bottom: none;
        }

        .item-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 4px;
            margin-right: 15px;
        }

        .item-info {
            flex: 1;
        }

        .item-name {
            font-weight: bold;
            margin-bottom: 5px;
        }

        .item-price,
        .item-quantity {
            color: #666;
            font-size: 14px;
        }

        .item-total {
            font-weight: bold;
            color: #e74c3c;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding: 5px 0;
        }

        .summary-total {
            border-top: 2px solid #eee;
            padding-top: 15px;
            margin-top: 15px;
            font-size: 18px;
            font-weight: bold;
            color: #e74c3c;
        }

        .action-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 30px;
        }

        .btn {
            padding: 12px 30px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            text-decoration: none;
            display: inline-block;
            transition: background-color 0.3s;
        }

        .btn-primary {
            background-color: #007bff;
            color: white;
        }

        .btn-primary:hover {
            background-color: #0056b3;
        }

        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background-color: #545b62;
        }

        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-cho-xac-nhan {
            background-color: #ffc107;
            color: #212529;
        }

        .shipping-info {
            background: #e3f2fd;
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
        }

        .shipping-title {
            font-weight: bold;
            color: #1976d2;
            margin-bottom: 10px;
        }

        .shipping-text {
            color: #1976d2;
            font-size: 14px;
        }

        @media (max-width: 768px) {
            .action-buttons {
                flex-direction: column;
            }

            .btn {
                width: 100%;
                text-align: center;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="success-card">
            <div class="success-icon">✓</div>
            <h1 class="success-title">Đặt hàng thành công!</h1>
            <p class="success-message">
                Cảm ơn bạn đã mua hàng tại SportShop. Đơn hàng của bạn đã được tiếp nhận và sẽ được xử lý sớm nhất.
            </p>
            <div class="order-info">
                <div class="info-row">
                    <span class="info-label">Mã đơn hàng:</span>
                    <span class="info-value">#<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Ngày đặt:</span>
                    <span class="info-value"><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Trạng thái:</span>
                    <span class="info-value">
                        <span class="status-badge status-cho-xac-nhan"><?php echo htmlspecialchars($order['status']); ?></span>
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">Tổng tiền:</span>
                    <span class="info-value" style="font-weight: bold; color: #e74c3c; font-size: 18px;">
                        <span><?php echo number_format($order['total_amount'], 0, ',', '.'); ?>đ</span>
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">Địa chỉ giao hàng:</span>
                    <span class="info-value"><?php echo htmlspecialchars($order['delivery_address']); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Phương thức vận chuyển:</span>
                    <span class="info-value">
                        <?php
                        $shipping_methods = [
                            'post' => 'Gửi hàng qua bưu điện',
                            'express' => 'Chuyển phát nhanh trong nước',
                            'direct' => 'Đưa hàng trực tiếp'
                        ];
                        echo isset($shipping_methods[$order['delivery_method']]) ? $shipping_methods[$order['delivery_method']] : '';
                        ?>
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">Phương thức thanh toán:</span>
                    <span class="info-value">
                        <?php
                        $payment_methods = [
                            'atm' => 'Thanh toán bằng thẻ ATM',
                            'cash' => 'Thanh toán trực tiếp'
                        ];
                        echo isset($payment_methods[$order['payment_method']]) ? $payment_methods[$order['payment_method']] : '';
                        ?>
                    </span>
                </div>
            </div>
        </div>

        <div class="order-details">
            <div class="section-title">Chi tiết đơn hàng</div>
            <?php foreach ($order_details as $item): ?>
                <div class="order-item">
                    <img src="<?php echo htmlspecialchars($item['hinh_anh']); ?>"
                        alt="<?php echo htmlspecialchars($item['ten_san_pham']); ?>"
                        class="item-image">
                    <div class="item-info">
                        <div class="item-name"><?php echo htmlspecialchars($item['ten_san_pham']); ?></div>
                        <div class="item-price"><?php echo number_format($item['price'], 0, ',', '.'); ?>đ</div>
                        <div class="item-quantity">Số lượng: <?php echo $item['quantity']; ?></div>
                    </div>
                    <div class="item-total">
                        <?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?>đ
                    </div>
                </div>
            <?php endforeach; ?>
            <div class="summary-row summary-total">
                <span>Tổng cộng:</span>
                <span><?php echo number_format($order['total_amount'], 0, ',', '.'); ?>đ</span>
            </div>
        </div>

        <div class="shipping-info">
            <div class="shipping-title">Thông tin vận chuyển</div>
            <div class="shipping-text">
                • Đơn hàng sẽ được xử lý trong vòng 1-2 ngày làm việc<br>
                • Thời gian giao hàng: 3-5 ngày đối với nội thành, 5-7 ngày đối với các tỉnh khác<br>
                • Bạn sẽ nhận được thông báo khi đơn hàng được vận chuyển<br>
                • Liên hệ hotline: 1900-xxxx để được hỗ trợ
            </div>
        </div>

        <div class="action-buttons">
            <a href="index.php" class="btn btn-primary">Tiếp tục mua hàng</a>
            <a href="my_orders.php" class="btn btn-secondary">Xem đơn hàng của tôi</a>
        </div>
    </div>
</body>

</html>