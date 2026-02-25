<?php
require_once '../config/config.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];

// Lấy Danh sách lịch hẹn của người dùng
$stmt = $pdo->prepare("
    SELECT c.*, p.name, p.price, p.sale_price, p.image_url, p.stock_quantity
    FROM cart c
    JOIN products p ON c.product_id = p.id
    WHERE c.user_id = ?
    ORDER BY c.created_at DESC
");
$stmt->execute([$user_id]);
$cart_items = $stmt->fetchAll();

// Tính tổng tiền
$total = 0;
foreach ($cart_items as $item) {
    $price = $item['sale_price'] ?: $item['price'];
    $total += $price * $item['quantity'];
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh sách lịch hẹn - BHH</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .cart-item {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            padding: 20px;
        }

        .cart-item img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 10px;
        }

        .quantity-controls {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .quantity-controls button {
            width: 35px;
            height: 35px;
            border: none;
            border-radius: 50%;
            background: #f8f9fa;
            color: #495057;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .quantity-controls button:hover {
            background: #e9ecef;
        }

        .quantity-controls input {
            width: 60px;
            text-align: center;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 5px;
        }

        .cart-summary {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 30px;
            position: sticky;
            top: 20px;
        }

        .empty-cart {
            text-align: center;
            padding: 100px 0;
            color: #6c757d;
        }

        .empty-cart i {
            font-size: 80px;
            margin-bottom: 20px;
        }
    </style>
</head>

<body class="bg-light">
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-running"></i> SportWear Shop
            </a>

            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="index.php">Trang chủ</a>
                <a class="nav-link" href="logout.php">Đăng xuất</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-8">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fas fa-shopping-cart"></i> Danh sách lịch hẹn của bạn</h2>
                    <?php if (!empty($cart_items)): ?>
                        <small class="text-muted"><?= count($cart_items) ?> Dịch vụ</small>
                    <?php endif; ?>
                </div>

                <?php if (empty($cart_items)): ?>
                    <div class="empty-cart">
                        <i class="fas fa-shopping-cart"></i>
                        <h4>Danh sách lịch hẹn trống</h4>
                        <p>Bạn chưa có Dịch vụ nào trong Danh sách lịch hẹn.</p>
                        <a href="index.php" class="btn btn-primary">
                            <i class="fas fa-shopping-bag"></i> Tiếp tục mua sắm
                        </a>
                    </div>
                <?php else: ?>
                    <?php foreach ($cart_items as $item): ?>
                        <div class="cart-item">
                            <div class="row align-items-center">
                                <div class="col-md-2">
                                    <img src="<?= $item['image_url'] ?: 'images/no-image.jpg' ?>"
                                        alt="<?= htmlspecialchars($item['name']) ?>"
                                        class="img-fluid">
                                </div>

                                <div class="col-md-4">
                                    <h6><?= htmlspecialchars($item['name']) ?></h6>
                                    <?php if ($item['size']): ?>
                                        <small class="text-muted">Size: <?= htmlspecialchars($item['size']) ?></small><br>
                                    <?php endif; ?>
                                    <?php if ($item['color']): ?>
                                        <small class="text-muted">Màu: <?= htmlspecialchars($item['color']) ?></small>
                                    <?php endif; ?>
                                </div>


                                <div class="col-md-2">
                                    <div class="price">
                                        <?php if ($item['sale_price']): ?>
                                            <span class="text-decoration-line-through text-muted small">
                                                <?= formatPrice($item['price']) ?>
                                            </span><br>
                                            <span class="text-danger fw-bold">
                                                <?= formatPrice($item['sale_price']) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="fw-bold">
                                                <?= formatPrice($item['price']) ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <div class="quantity-controls">
                                        <button type="button" class="btn-quantity-decrease" data-cart-id="<?= $item['id'] ?>">
                                            <i class="fas fa-minus"></i>
                                        </button>
                                        <input type="number" class="quantity-input"
                                            value="<?= $item['quantity'] ?>"
                                            min="1"
                                            max="<?= $item['stock_quantity'] ?>"
                                            data-cart-id="<?= $item['id'] ?>">
                                        <button type="button" class="btn-quantity-increase" data-cart-id="<?= $item['id'] ?>">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="fw-bold text-primary item-total" data-cart-id="<?= $item['id'] ?>">
                                            <?php
                                            $price = $item['sale_price'] ?: $item['price'];
                                            echo formatPrice($price * $item['quantity']);
                                            ?>
                                        </span>
                                        <button type="button" class="btn btn-outline-danger btn-sm btn-remove"
                                            data-cart-id="<?= $item['id'] ?>">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <?php if (!empty($cart_items)): ?>
                <div class="col-md-4">
                    <div class="cart-summary">
                        <h5 class="mb-3">Tóm tắt đơn hàng</h5>

                        <div class="d-flex justify-content-between mb-2">
                            <span>Tạm tính:</span>
                            <span id="subtotal"><?= formatPrice($total) ?></span>
                        </div>

                        <div class="d-flex justify-content-between mb-2">
                            <span>Phí vận chuyển:</span>
                            <span class="text-muted">Miễn phí</span>
                        </div>

                        <hr>

                        <div class="d-flex justify-content-between mb-3">
                            <strong>Tổng cộng:</strong>
                            <strong class="text-primary" id="total"><?= formatPrice($total) ?></strong>
                        </div>

                        <div class="d-grid gap-2">
                            <a href="check_out.php" class="btn btn-primary btn-lg">
                                <i class="fas fa-credit-card"></i> Thanh toán
                            </a>
                            <a href="index.php" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left"></i> Tiếp tục mua sắm
                            </a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Update quantity
        document.querySelectorAll('.quantity-input').forEach(input => {
            input.addEventListener('change', function() {
                updateQuantity(this.dataset.cartId, this.value);
            });
        });

        document.querySelectorAll('.btn-quantity-increase').forEach(button => {
            button.addEventListener('click', function() {
                const cartId = this.dataset.cartId;
                const input = document.querySelector(`input[data-cart-id="${cartId}"]`);
                const newQuantity = parseInt(input.value) + 1;
                const maxQuantity = parseInt(input.max);

                if (newQuantity <= maxQuantity) {
                    input.value = newQuantity;
                    updateQuantity(cartId, newQuantity);
                }
            });
        });

        document.querySelectorAll('.btn-quantity-decrease').forEach(button => {
            button.addEventListener('click', function() {
                const cartId = this.dataset.cartId;
                const input = document.querySelector(`input[data-cart-id="${cartId}"]`);
                const newQuantity = parseInt(input.value) - 1;

                if (newQuantity >= 1) {
                    input.value = newQuantity;
                    updateQuantity(cartId, newQuantity);
                }
            });
        });

        // Remove item
        document.querySelectorAll('.btn-remove').forEach(button => {
            button.addEventListener('click', function() {
                if (confirm('Bạn có chắc muốn xóa Dịch vụ này khỏi Danh sách lịch hẹn?')) {
                    removeFromCart(this.dataset.cartId);
                }
            });
        });

        function updateQuantity(cartId, quantity) {
            fetch('update_cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=update&cart_id=${cartId}&quantity=${quantity}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Có lỗi xảy ra: ' + data.message);
                    }
                });
        }

        function removeFromCart(cartId) {
            fetch('update_cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=remove&cart_id=${cartId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Có lỗi xảy ra: ' + data.message);
                    }
                });
        }
    </script>
</body>

</html>