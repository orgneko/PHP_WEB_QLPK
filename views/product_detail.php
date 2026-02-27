<?php
session_start();
require_once '../config/config.php';

// Lấy ID Dịch vụ từ URL
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Lấy thông tin chi tiết Dịch vụ
$stmt = $pdo->prepare("
    SELECT p.*, c.name as category_name, s.name as supplier_name 
    FROM services p 
    LEFT JOIN categories c ON p.category_id = c.id 
    LEFT JOIN doctors s ON p.supplier_id = s.id 
    WHERE p.id = ?
");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

// Nếu không tìm thấy Dịch vụ, chuyển về trang chủ
if (!$product) {
    header('Location: index.php');
    exit;
}

// Lấy các Dịch vụ liên quan (cùng Chuyên khoa)
$stmt = $pdo->prepare("
    SELECT * FROM services 
    WHERE category_id = ? AND id != ? 
    LIMIT 4
");
$stmt->execute([$product['category_id'], $product_id]);
$related_services = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($product['name']) ?> - SportShop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        .product-image {
            max-height: 600px;
            width: 100%;
            object-fit: contain;
            display: block;
            margin: 0 auto;
        }

        .product-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
        }

        .price-original {
            text-decoration: line-through;
            color: #6c757d;
        }

        .price-sale {
            color: #dc3545;
            font-weight: bold;
            font-size: 1.5em;
        }

        .quantity-input {
            max-width: 100px;
        }

        .related-product-card {
            transition: transform 0.3s;
        }

        .related-product-card:hover {
            transform: translateY(-5px);
        }
    </style>
</head>

<body>
    <div class="container my-5">
        <div class="row">
            <!-- Ảnh Dịch vụ -->
            <div class="col-md-6">
                <img src="<?= htmlspecialchars($product['image_url']) ?>"
                    class="img-fluid product-image"
                    alt="<?= htmlspecialchars($product['name']) ?>">
            </div>

            <!-- Thông tin Dịch vụ -->
            <div class="col-md-6">
                <div class="product-info">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="index.php">Trang chủ</a></li>
                            <li class="breadcrumb-item">
                                <a href="index.php?category=<?= $product['category_id'] ?>">
                                    <?= htmlspecialchars($product['category_name']) ?>
                                </a>
                            </li>
                            <li class="breadcrumb-item active"><?= htmlspecialchars($product['name']) ?></li>
                        </ol>
                    </nav>

                    <h1 class="h2 mb-3"><?= htmlspecialchars($product['name']) ?></h1>

                    <p class="text-muted">Mã Dịch vụ: <?= htmlspecialchars($product['code']) ?></p>

                    <div class="mb-3">
                        <?php if ($product['sale_price']): ?>
                            <div class="price-original h5"><?= number_format($product['price'], 0, ',', '.') ?>đ</div>
                            <div class="price-sale" id="total-price"><?= number_format($product['sale_price'], 0, ',', '.') ?>đ</div>
                        <?php else: ?>
                            <div class="price-sale" id="total-price"><?= number_format($product['price'], 0, ',', '.') ?>đ</div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Số lượng:</label>
                        <input type="number" id="quantity" class="form-control quantity-input"
                            value="1" min="1" max="<?= $product['stock_quantity'] ?>">
                    </div>

                    <?php if (isLoggedIn()): ?>
                        <button class="btn btn-primary btn-lg mb-3" onclick="addToCart()">
                            <i class="fas fa-cart-plus"></i> Đăng ký khám
                        </button>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-primary btn-lg mb-3">
                            Đăng nhập để mua hàng
                        </a>
                    <?php endif; ?>

                    <div class="mb-4">
                        <h5>Mô tả Dịch vụ:</h5>
                        <p><?= nl2br(htmlspecialchars($product['description'])) ?></p>
                    </div>

                    <div class="additional-info">
                        <p><strong>Chuyên khoa:</strong> <?= htmlspecialchars($product['category_name']) ?></p>
                        <?php if ($product['supplier_name']): ?>
                            <p><strong>Nhà cung cấp:</strong> <?= htmlspecialchars($product['supplier_name']) ?></p>
                        <?php endif; ?>
                        <p><strong>Tình trạng:</strong>
                            <?= $product['stock_quantity'] > 0 ? 'Còn hàng' : 'Hết hàng' ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dịch vụ liên quan -->
        <?php if ($related_services): ?>
            <div class="mt-5">
                <h3>Dịch vụ liên quan</h3>
                <div class="row">
                    <?php foreach ($related_services as $related): ?>
                        <div class="col-md-3">
                            <div class="card related-product-card">
                                <img src="<?= htmlspecialchars($related['image_url']) ?>"
                                    class="card-img-top" alt="<?= htmlspecialchars($related['name']) ?>"
                                    style="height: 200px; object-fit: cover;">
                                <div class="card-body">
                                    <h5 class="card-title"><?= htmlspecialchars($related['name']) ?></h5>
                                    <p class="card-text">
                                        <?php if ($related['sale_price']): ?>
                                            <span class="price-original"><?= formatPrice($related['price']) ?></span>
                                            <span class="price-sale"><?= formatPrice($related['sale_price']) ?></span>
                                        <?php else: ?>
                                            <span class="price-sale"><?= formatPrice($related['price']) ?></span>
                                        <?php endif; ?>
                                    </p>
                                    <a href="product_detail.php?id=<?= $related['id'] ?>"
                                        class="btn btn-outline-primary">Xem chi tiết</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function addToCart() {
            const quantity = document.getElementById('quantity').value;

            fetch('add_to_cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `product_id=<?= $product_id ?>&quantity=${quantity}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Đã thêm Dịch vụ vào giỏ hàng!');
                    } else {
                        alert('Có lỗi xảy ra: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Có lỗi xảy ra khi Đăng ký khám hàng!');
                });
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>