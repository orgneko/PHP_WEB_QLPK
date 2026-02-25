<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include '../config/config.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Lấy thông tin giỏ hàng
$user_id = $_SESSION['user_id'];
$cart_sql = "SELECT c.*, c.product_id, p.name AS ten_san_pham, p.price AS gia, p.image_url AS hinh_anh, p.stock_quantity 
             FROM cart c 
             JOIN products p ON c.product_id = p.id 
             WHERE c.user_id = ?";
$stmt = $pdo->prepare($cart_sql);
$stmt->execute([$user_id]);
$cart_items = [];
$total_amount = 0;
while ($row = $stmt->fetch()) {
    $cart_items[] = $row;
    $total_amount += $row['gia'] * $row['quantity'];
}

// Kiểm tra giỏ hàng có rỗng không
if (empty($cart_items)) {
    header('Location: cart.php');
    exit();
}

// Lấy thông tin khách hàng
$user_sql = "SELECT * FROM users WHERE id = ?";
$stmt = $pdo->prepare($user_sql);
$stmt->execute([$user_id]);
$user_info = $stmt->fetch();

// Xử lý đặt hàng
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $ho_ten = $_POST['ho_ten'];
    $email = $_POST['email'];
    $so_dien_thoai = $_POST['so_dien_thoai'];
    $dia_chi = $_POST['dia_chi'];
    $tinh_thanh = $_POST['tinh_thanh'];
    $quan_huyen = $_POST['quan_huyen'];
    $phuong_xa = $_POST['phuong_xa'];
    $ghi_chu = $_POST['ghi_chu'];
    $phuong_thuc_thanh_toan = $_POST['phuong_thuc_thanh_toan'];
    $phuong_thuc_van_chuyen = $_POST['phuong_thuc_van_chuyen'];

    // Tính phí vận chuyển
    $phi_van_chuyen = 0;
    switch ($phuong_thuc_van_chuyen) {
        case 'buu_dien':
            $phi_van_chuyen = 25000;
            break;
        case 'chuyen_phat_nhanh':
            $phi_van_chuyen = 50000;
            break;
        case 'giao_hang_truc_tiep':
            $phi_van_chuyen = 30000;
            break;
    }

    $tong_tien = $total_amount + $phi_van_chuyen;

    // Bắt đầu transaction
    $pdo->beginTransaction();

    try {
        // Tạo đơn hàng
        $dia_chi_day_du = $dia_chi . ', ' . $phuong_xa . ', ' . $quan_huyen . ', ' . $tinh_thanh;
        $order_number = 'DH' . time();

        $order_sql = "INSERT INTO orders (user_id, order_number, total_amount, payment_method, delivery_method, delivery_address, status, created_at)
                      VALUES (?, ?, ?, ?,
                      ?, ?, 'pending', NOW())";
        $stmt = $pdo->prepare($order_sql);
        $stmt->execute([
            $user_id,
            $order_number,
            $tong_tien,
            $phuong_thuc_thanh_toan,
            $phuong_thuc_van_chuyen,
            $dia_chi_day_du
        ]);
        $order_id = $pdo->lastInsertId();

        // Thêm chi tiết đơn hàng
        foreach ($cart_items as $item) {
            $detail_sql = "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)";
            $stmt = $pdo->prepare($detail_sql);
            $stmt->execute([$order_id, $item['product_id'], $item['quantity'], $item['gia']]);

            // Cập nhật số lượng Dịch vụ
            $update_stock = "UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ?";
            $stmt = $pdo->prepare($update_stock);
            $stmt->execute([$item['quantity'], $item['product_id']]);
        }

        // Xóa giỏ hàng
        $clear_cart = "DELETE FROM cart WHERE user_id = ?";
        $stmt = $pdo->prepare($clear_cart);
        $stmt->execute([$user_id]);

        $pdo->commit();

        // Chuyển hướng đến trang thành công
        header('Location: order_sucess.php?order_id=' . $order_id);
        exit();
    } catch (Exception $e) {
        $pdo->rollBack();
        $error_message = "Có lỗi xảy ra khi đặt hàng: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanh toán - SportShop</title>
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
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .checkout-wrapper {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 30px;
        }

        .checkout-form {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .order-summary {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            height: fit-content;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }

        .form-group textarea {
            height: 80px;
            resize: vertical;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .section-title {
            font-size: 18px;
            font-weight: bold;
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #007bff;
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

        .item-price {
            color: #666;
            font-size: 14px;
        }

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

        .btn-primary {
            width: 100%;
            padding: 15px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .btn-primary:hover {
            background-color: #0056b3;
        }

        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #007bff;
            text-decoration: none;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
        }

        .payment-methods {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-top: 10px;
        }

        .payment-option {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }

        .payment-option:hover {
            border-color: #007bff;
            background-color: #f8f9fa;
        }

        .payment-option input[type="radio"] {
            margin-right: 5px;
        }

        @media (max-width: 768px) {
            .checkout-wrapper {
                grid-template-columns: 1fr;
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            .payment-methods {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <a href="cart.php" class="back-link">← Quay lại giỏ hàng</a>

        <h1>Thanh toán</h1>

        <?php if (isset($error_message)): ?>
            <div class="error-message"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="checkout-wrapper">
                <div class="checkout-form">
                    <div class="section-title">Thông tin giao hàng</div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="ho_ten">Họ và tên *</label>
                            <input type="text" id="ho_ten" name="ho_ten" required
                                value="<?php echo htmlspecialchars($user_info['full_name']); ?>">
                        </div>

                        <div class="form-group">
                            <label for="email">Email *</label>
                            <input type="email" id="email" name="email" required
                                value="<?php echo htmlspecialchars($user_info['email']); ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="so_dien_thoai">Số điện thoại *</label>
                        <input type="tel" id="so_dien_thoai" name="so_dien_thoai" required
                            value="<?php echo htmlspecialchars($user_info['phone']); ?>">
                    </div>

                    <div class="form-group">
                        <label for="dia_chi">Địa chỉ *</label>
                        <input type="text" id="dia_chi" name="dia_chi" required
                            value="<?php echo htmlspecialchars($user_info['address']); ?>">
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="tinh_thanh">Tỉnh/Thành phố *</label>
                            <select id="tinh_thanh" name="tinh_thanh" required>
                                <option value="">Chọn tỉnh/thành phố</option>
                                <option value="Ho Chi Minh">TP. Hồ Chí Minh</option>
                                <option value="Ha Noi">Hà Nội</option>
                                <option value="Da Nang">Đà Nẵng</option>
                                <option value="Can Tho">Cần Thơ</option>
                                <option value="Hai Phong">Hải Phòng</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="quan_huyen">Quận/Huyện *</label>
                            <input type="text" id="quan_huyen" name="quan_huyen" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="phuong_xa">Phường/Xã *</label>
                        <input type="text" id="phuong_xa" name="phuong_xa" required>
                    </div>

                    <div class="form-group">
                        <label for="ghi_chu">Ghi chú</label>
                        <textarea id="ghi_chu" name="ghi_chu" placeholder="Ghi chú thêm về đơn hàng..."></textarea>
                    </div>

                    <div class="section-title">Phương thức vận chuyển</div>

                    <div class="form-group">
                        <label>
                            <input type="radio" name="phuong_thuc_van_chuyen" value="buu_dien" required>
                            Gửi hàng qua bưu điện (+25.000đ)
                        </label>
                    </div>

                    <div class="form-group">
                        <label>
                            <input type="radio" name="phuong_thuc_van_chuyen" value="chuyen_phat_nhanh" required>
                            Chuyển phát nhanh trong nước (+50.000đ)
                        </label>
                    </div>

                    <div class="form-group">
                        <label>
                            <input type="radio" name="phuong_thuc_van_chuyen" value="giao_hang_truc_tiep" required>
                            Đưa hàng trực tiếp (+30.000đ)
                        </label>
                    </div>

                    <div class="section-title">Phương thức thanh toán</div>

                    <div class="payment-methods">
                        <div class="payment-option">
                            <label>
                                <input type="radio" name="phuong_thuc_thanh_toan" value="atm" required>
                                Thanh toán bằng thẻ ATM
                            </label>
                        </div>

                        <div class="payment-option">
                            <label>
                                <input type="radio" name="phuong_thuc_thanh_toan" value="truc_tiep" required>
                                Thanh toán trực tiếp
                            </label>
                        </div>
                    </div>
                </div>

                <div class="order-summary">
                    <div class="section-title">Đơn hàng của bạn</div>

                    <?php foreach ($cart_items as $item): ?>
                        <div class="order-item">
                            <img src="<?php echo htmlspecialchars($item['hinh_anh']); ?>"
                                alt="<?php echo htmlspecialchars($item['ten_san_pham']); ?>"
                                class="item-image">

                            <div class="item-info">
                                <div class="item-name"><?php echo htmlspecialchars($item['ten_san_pham']); ?></div>
                                <div class="item-price"><?php echo number_format($item['gia'], 0, ',', '.'); ?>đ</div>
                                <div class="item-quantity">Số lượng: <?php echo $item['quantity']; ?></div>
                            </div>

                            <div class="item-total">
                                <?php echo number_format($item['gia'] * $item['quantity'], 0, ',', '.'); ?>đ
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <div class="summary-row">
                        <span>Tạm tính:</span>
                        <span><?php echo number_format($total_amount, 0, ',', '.'); ?>đ</span>
                    </div>

                    <div class="summary-row">
                        <span>Phí vận chuyển:</span>
                        <span id="shipping-fee">0đ</span>
                    </div>

                    <div class="summary-row summary-total">
                        <span>Tổng cộng:</span>
                        <span id="total-amount"><?php echo number_format($total_amount, 0, ',', '.'); ?>đ</span>
                    </div>

                    <button type="submit" class="btn-primary">Đặt hàng</button>
                </div>
            </div>
        </form>
    </div>

    <script>
        // Cập nhật phí vận chuyển khi thay đổi phương thức
        const shippingMethods = document.querySelectorAll('input[name="phuong_thuc_van_chuyen"]');
        const shippingFeeElement = document.getElementById('shipping-fee');
        const totalAmountElement = document.getElementById('total-amount');
        const baseAmount = <?php echo $total_amount; ?>;

        shippingMethods.forEach(method => {
            method.addEventListener('change', function() {
                let shippingFee = 0;

                switch (this.value) {
                    case 'buu_dien':
                        shippingFee = 25000;
                        break;
                    case 'chuyen_phat_nhanh':
                        shippingFee = 50000;
                        break;
                    case 'giao_hang_truc_tiep':
                        shippingFee = 30000;
                        break;
                }

                const total = baseAmount + shippingFee;

                shippingFeeElement.textContent = shippingFee.toLocaleString('vi-VN') + 'đ';
                totalAmountElement.textContent = total.toLocaleString('vi-VN') + 'đ';
            });
        });
    </script>
</body>

</html>