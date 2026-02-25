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

// Lấy thông tin đơn hàng
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt->execute([$order_id, $user_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    header('Location: my_order.php');
    exit();
}

// Lấy chi tiết Dịch vụ
$stmt = $pdo->prepare("
    SELECT oi.*, p.name as product_name, p.image_url as product_image 
    FROM order_items oi 
    LEFT JOIN products p ON oi.product_id = p.id 
    WHERE oi.order_id = ?
");
$stmt->execute([$order_id]);
$order_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Tính tổng tiền
$total_amount = 0;
foreach ($order_items as $item) {
    $total_amount += $item['quantity'] * $item['price'];
}

$status_colors = [
    'pending' => '#ffc107',
    'confirmed' => '#17a2b8',
    'shipping' => '#007bff',
    'delivered' => '#28a745',
    'cancelled' => '#dc3545'
];
$status_text = [
    'pending' => 'Chờ xác nhận',
    'confirmed' => 'Đã xác nhận',
    'shipping' => 'Đang giao hàng',
    'delivered' => 'Đã giao hàng',
    'cancelled' => 'Đã hủy'
];
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết đơn hàng #<?php echo $order['order_code'] ?? $order['id']; ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 25px;
            text-align: center;
        }

        .header h1 {
            font-size: 24px;
            margin-bottom: 5px;
        }

        .content {
            padding: 25px;
        }

        .order-info {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
        }

        .info-item {
            text-align: center;
            padding: 10px;
            background: white;
            border-radius: 8px;
        }

        .info-label {
            font-size: 12px;
            color: #666;
            margin-bottom: 5px;
            text-transform: uppercase;
        }

        .info-value {
            font-size: 14px;
            font-weight: 600;
            color: #333;
        }

        .status {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            color: white;
            text-transform: uppercase;
            display: inline-block;
        }

        .price {
            color: #e74c3c;
            font-weight: 600;
        }

        .products-section h3 {
            margin-bottom: 15px;
            color: #333;
        }

        .product-item {
            display: flex;
            align-items: center;
            padding: 15px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            margin-bottom: 10px;
            background: white;
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

        .actions {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin-top: 20px;
            flex-wrap: wrap;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-outline {
            background: transparent;
            border: 1px solid #667eea;
            color: #667eea;
        }

        .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        @media (max-width: 768px) {
            .container {
                margin: 10px;
            }

            .content {
                padding: 15px;
            }

            .info-grid {
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
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>📋 Chi tiết đơn hàng #<?php echo htmlspecialchars($order['order_code'] ?? $order['id']); ?></h1>
            <p>Thông tin chi tiết về đơn hàng của bạn</p>
        </div>

        <div class="content">
            <!-- Thông tin đơn hàng -->
            <div class="order-info">
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Ngày đặt hàng</div>
                        <div class="info-value"><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Trạng thái</div>
                        <div class="status" style="background: <?php echo $status_colors[$order['status']]; ?>">
                            <?php echo $status_text[$order['status']]; ?>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Tổng tiền</div>
                        <div class="info-value price"><?php echo number_format($total_amount, 0, ',', '.'); ?> VNĐ</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Thanh toán</div>
                        <div class="info-value"><?php echo htmlspecialchars($order['payment_method'] ?? 'COD'); ?></div>
                    </div>
                </div>

                <?php if (!empty($order['shipping_address'])): ?>
                    <div class="info-item" style="grid-column: 1/-1;">
                        <div class="info-label">Địa chỉ giao hàng</div>
                        <div class="info-value"><?php echo htmlspecialchars($order['shipping_address']); ?></div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Danh sách Dịch vụ -->
            <div class="products-section">
                <h3>📦 Dịch vụ đã đặt (<?php echo count($order_items); ?> Dịch vụ)</h3>

                <?php foreach ($order_items as $item): ?>
                    <div class="product-item">
                        <?php if ($item['product_image']): ?>
                            <img src="<?php echo htmlspecialchars($item['product_image']); ?>"
                                alt="<?php echo htmlspecialchars($item['product_name']); ?>" class="product-image">
                        <?php else: ?>
                            <div class="product-image" style="display: flex; align-items: center; justify-content: center; color: #999;">📷</div>
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

            <!-- Actions -->
            <div class="actions">
                <a href="my_orders.php" class="btn btn-primary">← Quay lại danh sách</a>

                <?php if ($order['status'] == 'pending'): ?>
                    <a href="cancel_order.php?id=<?php echo $order['id']; ?>" class="btn btn-outline"
                        onclick="return confirm('Bạn có chắc muốn hủy đơn hàng này?')">❌ Hủy đơn</a>
                <?php endif; ?>

                <?php if ($order['status'] == 'delivered'): ?>
                    <a href="review_order.php?id=<?php echo $order['id']; ?>" class="btn btn-outline">⭐ Đánh giá</a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Print order
        function printOrder() {
            window.print();
        }

        // Auto refresh every 2 minutes for status update
        setTimeout(() => location.reload(), 120000);
    </script>
</body>

</html>