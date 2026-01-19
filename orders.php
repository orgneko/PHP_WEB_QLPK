<?php
session_start();

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Kết nối database (thay đổi thông tin kết nối theo database của bạn)
$host = 'localhost';
$dbname = 'sportswear_shop';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Lỗi kết nối database: " . $e->getMessage());
}

$user_id = $_SESSION['user_id'];

// Lấy thông tin khách hàng
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Lấy danh sách đơn hàng
$stmt = $pdo->prepare("
    SELECT o.*, 
           COUNT(oi.id) as total_items,
           SUM(oi.quantity * oi.price) as total_amount
    FROM orders o 
    LEFT JOIN order_items oi ON o.id = oi.order_id 
    WHERE o.user_id = ? 
    GROUP BY o.id 
    ORDER BY o.created_at DESC
");
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Hàm định dạng trạng thái đơn hàng
function getStatusText($status) {
    switch($status) {
        case 'pending': return 'Chờ xác nhận';
        case 'confirmed': return 'Đã xác nhận';
        case 'shipping': return 'Đang giao hàng';
        case 'delivered': return 'Đã giao hàng';
        case 'cancelled': return 'Đã hủy';
        default: return 'Không xác định';
    }
}

// Hàm định dạng màu trạng thái
function getStatusColor($status) {
    switch($status) {
        case 'pending': return '#ffc107';
        case 'confirmed': return '#17a2b8';
        case 'shipping': return '#007bff';
        case 'delivered': return '#28a745';
        case 'cancelled': return '#dc3545';
        default: return '#6c757d';
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đơn hàng của tôi - SportWear Shop</title>
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
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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

        .box-icon {
            width: 30px;
            height: 30px;
            background: rgba(255,255,255,0.2);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
        }

        .customer-info {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px 25px;
            margin: 0;
        }

        .customer-info h3 {
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 18px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
        }

        .info-item {
            background: rgba(255,255,255,0.1);
            padding: 12px 15px;
            border-radius: 8px;
            backdrop-filter: blur(10px);
        }

        .info-label {
            font-size: 12px;
            opacity: 0.8;
            margin-bottom: 5px;
        }

        .info-value {
            font-size: 14px;
            font-weight: 500;
        }

        .orders-section {
            padding: 25px;
        }

        .order-card {
            border: 1px solid #e0e0e0;
            border-radius: 12px;
            margin-bottom: 20px;
            overflow: hidden;
            transition: all 0.3s ease;
            background: white;
        }

        .order-card:hover {
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }

        .order-header {
            background: #f8f9fa;
            padding: 15px 20px;
            border-bottom: 1px solid #e0e0e0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }

        .order-id {
            font-weight: 600;
            color: #333;
            font-size: 16px;
        }

        .order-date {
            color: #666;
            font-size: 14px;
        }

        .order-status {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            color: white;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .order-body {
            padding: 20px;
        }

        .order-summary {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
        }

        .summary-item {
            text-align: center;
            padding: 12px;
            background: #f8f9fa;
            border-radius: 8px;
        }

        .summary-label {
            font-size: 12px;
            color: #666;
            margin-bottom: 5px;
        }

        .summary-value {
            font-size: 16px;
            font-weight: 600;
            color: #333;
        }

        .order-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-top: 15px;
            flex-wrap: wrap;
        }

        .btn {
            padding: 8px 16px;
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
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }

        .empty-state img {
            width: 120px;
            height: 120px;
            opacity: 0.5;
            margin-bottom: 20px;
        }

        .empty-state h3 {
            margin-bottom: 10px;
            color: #333;
        }

        .price {
            color: #e74c3c;
            font-weight: 600;
        }

        @media (max-width: 768px) {
            .container {
                margin: 10px;
                border-radius: 10px;
            }
            
            .header {
                padding: 20px 15px;
            }
            
            .header h1 {
                font-size: 24px;
            }
            
            .customer-info {
                padding: 15px;
            }
            
            .orders-section {
                padding: 15px;
            }
            
            .order-header {
                padding: 12px 15px;
                flex-direction: column;
                align-items: flex-start;
            }
            
            .order-body {
                padding: 15px;
            }
            
            .info-grid {
                grid-template-columns: 1fr;
            }
            
            .order-summary {
                grid-template-columns: 1fr;
            }
            
            .order-actions {
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>
                <div class="box-icon">📦</div>
                Đơn hàng của tôi
            </h1>
        </div>

        <!-- Thông tin khách hàng -->
        <div class="customer-info">
            <h3>
                👤 Thông tin khách hàng
            </h3>
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Tên đầy đủ</div>
                    <div class="info-value"><?php echo htmlspecialchars($user['full_name'] ?? $user['name'] ?? 'Chưa cập nhật'); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Username</div>
                    <div class="info-value"><?php echo htmlspecialchars($user['username'] ?? 'Chưa có'); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Email</div>
                    <div class="info-value"><?php echo htmlspecialchars($user['email']); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Điện thoại</div>
                    <div class="info-value"><?php echo htmlspecialchars($user['phone'] ?? 'Chưa cập nhật'); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">SĐT</div>
                    <div class="info-value"><?php echo htmlspecialchars($user['phone'] ?? 'Chưa có'); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Địa chỉ giao hàng</div>
                    <div class="info-value"><?php echo htmlspecialchars($user['address'] ?? 'Chưa cập nhật'); ?></div>
                </div>
            </div>
        </div>

        <!-- Danh sách đơn hàng -->
        <div class="orders-section">
            <?php if (empty($orders)): ?>
                <div class="empty-state">
                    <div style="font-size: 48px; margin-bottom: 20px;">📦</div>
                    <h3>Chưa có đơn hàng nào</h3>
                    <p>Bạn chưa có đơn hàng nào. Hãy bắt đầu mua sắm ngay!</p>
                    <div style="margin-top: 20px;">
                        <a href="products.php" class="btn btn-primary">Mua sắm ngay</a>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($orders as $order): ?>
                    <div class="order-card">
                        <div class="order-header">
                            <div>
                                <div class="order-id">Đơn hàng #<?php echo htmlspecialchars($order['order_code'] ?? $order['id']); ?></div>
                                <div class="order-date">Ngày đặt: <?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></div>
                            </div>
                            <div class="order-status" style="background-color: <?php echo getStatusColor($order['status']); ?>">
                                <?php echo getStatusText($order['status']); ?>
                            </div>
                        </div>
                        
                        <div class="order-body">
                            <div class="order-summary">
                                <div class="summary-item">
                                    <div class="summary-label">Số lượng sản phẩm</div>
                                    <div class="summary-value"><?php echo $order['total_items']; ?> sản phẩm</div>
                                </div>
                                <div class="summary-item">
                                    <div class="summary-label">Tổng tiền</div>
                                    <div class="summary-value price"><?php echo number_format($order['total_amount'] ?? 0, 0, ',', '.'); ?> VNĐ</div>
                                </div>
                                <div class="summary-item">
                                    <div class="summary-label">Phương thức thanh toán</div>
                                    <div class="summary-value"><?php echo htmlspecialchars($order['payment_method'] ?? 'COD'); ?></div>
                                </div>
                            </div>
                            
                            <div class="order-actions">
                                <a href="order_detail.php?id=<?php echo $order['id']; ?>" class="btn btn-primary">
                                    👁️ Xem chi tiết
                                </a>
                                <?php if ($order['status'] == 'pending'): ?>
                                    <a href="cancel_order.php?id=<?php echo $order['id']; ?>" class="btn btn-outline" 
                                       onclick="return confirm('Bạn có chắc muốn hủy đơn hàng này?')">
                                        ❌ Hủy đơn
                                    </a>
                                <?php endif; ?>
                                <?php if ($order['status'] == 'delivered'): ?>
                                    <a href="review_order.php?id=<?php echo $order['id']; ?>" class="btn btn-outline">
                                        ⭐ Đánh giá
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Tự động refresh trang mỗi 5 phút để cập nhật trạng thái đơn hàng
        setTimeout(function() {
            location.reload();
        }, 300000);

        // Thêm hiệu ứng loading khi click vào các nút
        document.querySelectorAll('.btn').forEach(button => {
            button.addEventListener('click', function() {
                this.style.opacity = '0.7';
                this.innerHTML = '⏳ Đang xử lý...';
            });
        });

        // Hiệu ứng hover cho order cards
        document.querySelectorAll('.order-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-3px)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
        });
    </script>
</body>
</html>