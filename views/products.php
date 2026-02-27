<?php
session_start();
require_once '../config/config.php';

// Lấy danh sách Chuyên khoa
$categories = $pdo->query("SELECT * FROM categories")->fetchAll();

// Xử lý lọc
$where = [];
$params = [];

// Lọc theo nhiều Chuyên khoa
if (!empty($_GET['category'])) {
    $catArr = array_map('intval', (array)$_GET['category']);
    $where[] = 'category_id IN (' . implode(',', $catArr) . ')';
}

// Lọc theo nhiều mức giá
if (!empty($_GET['price_range'])) {
    $priceSql = [];
    $priceRanges = [
        '1' => [0, 500000],
        '2' => [500000, 1000000],
        '3' => [1000000, 2000000],
        '4' => [2000000, 3000000],
        '5' => [3000000, 5000000],
        '6' => [5000000, 100000000],
    ];
    foreach ((array)$_GET['price_range'] as $key) {
        if (isset($priceRanges[$key])) {
            $range = $priceRanges[$key];
            $priceSql[] = "(price >= {$range[0]} AND price < {$range[1]})";
        }
    }
    if ($priceSql) {
        $where[] = '(' . implode(' OR ', $priceSql) . ')';
    }
}

$sql = "SELECT * FROM services";
if ($where) {
    $sql .= " WHERE " . implode(' AND ', $where);
}
$sql .= " ORDER BY created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$services = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Dịch vụ - SportShop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 25px;
            padding: 20px 0;
        }

        .product-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
            overflow: hidden;
        }

        .product-card:hover {
            transform: translateY(-5px);
        }

        .product-image {
            position: relative;
            padding-top: 100%;
            /* Tỷ lệ 1:1 */
            overflow: hidden;
        }

        .product-image img {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .product-card:hover .product-image img {
            transform: scale(1.05);
        }

        .product-info {
            padding: 15px;
        }

        .product-name {
            font-size: 1.1em;
            font-weight: 600;
            margin-bottom: 10px;
            color: #333;
            height: 2.4em;
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            line-clamp: 2;
            -webkit-box-orient: vertical;
        }

        .product-price {
            color: #e74c3c;
            font-size: 1.2em;
            font-weight: bold;
        }

        .product-original-price {
            text-decoration: line-through;
            color: #999;
            font-size: 0.9em;
        }

        .btn-add-cart {
            background: #2ecc71;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            width: 100%;
            margin-top: 10px;
            transition: background 0.3s ease;
        }

        .btn-add-cart:hover {
            background: #27ae60;
        }

        .product-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: #e74c3c;
            color: white;
            padding: 5px 10px;
            border-radius: 3px;
            font-size: 0.8em;
        }
    </style>
</head>

<body>
    <div class="container mt-4">
        <h2 class="mb-4">Dịch vụ thể thao</h2>
        <a href="index.php" class="btn btn-secondary mb-3">
            <i class="fas fa-arrow-left"></i> Về trang chủ
        </a>

        <div class="row">
            <div class="col-md-3">
                <form method="get" id="filterForm">
                    <h5 class="mb-3">DÒNG Dịch vụ</h5>
                    <?php foreach ($categories as $cat): ?>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="category[]" value="<?= $cat['id'] ?>"
                                <?= (isset($_GET['category']) && in_array($cat['id'], (array)$_GET['category'])) ? 'checked' : '' ?>>
                            <label class="form-check-label"><?= htmlspecialchars($cat['name']) ?></label>
                        </div>
                    <?php endforeach; ?>

                    <hr>
                    <h5 class="mb-3">MỨC GIÁ</h5>
                    <?php
                    $priceRanges = [
                        '1' => [0, 500000, 'Dưới 500.000đ'],
                        '2' => [500000, 1000000, '500.000đ - 1.000.000đ'],
                        '3' => [1000000, 2000000, '1.000.000đ - 2.000.000đ'],
                        '4' => [2000000, 3000000, '2.000.000đ - 3.000.000đ'],
                        '5' => [3000000, 5000000, '3.000.000đ - 5.000.000đ'],
                        '6' => [5000000, 100000000, 'Trên 5.000.000đ'],
                    ];
                    foreach ($priceRanges as $key => $range):
                    ?>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="price_range[]" value="<?= $key ?>"
                                <?= (isset($_GET['price_range']) && in_array($key, (array)$_GET['price_range'])) ? 'checked' : '' ?>>
                            <label class="form-check-label"><?= $range[2] ?></label>
                        </div>
                    <?php endforeach; ?>

                    <!-- Thêm các loại Dịch vụ khác nếu có -->
                    <!-- ... -->

                    <button type="submit" class="btn btn-primary mt-3">Lọc</button>
                    <a href="services.php" class="btn btn-outline-secondary mt-3">Reset</a>
                </form>
            </div>
            <div class="col-md-9">
                <div class="product-grid">
                    <?php foreach ($services as $p): ?>
                        <div class="product-card">
                            <a href="product_detail.php?id=<?= $p['id'] ?>" style="text-decoration:none; color:inherit;">
                                <div class="product-image">
                                    <img src="<?= htmlspecialchars($p['image_url']) ?>"
                                        alt="<?= htmlspecialchars($p['name']) ?>">
                                    <?php if ($p['sale_price']): ?>
                                        <span class="product-badge">Giảm giá</span>
                                    <?php endif; ?>
                                </div>
                                <div class="product-info">
                                    <h3 class="product-name"><?= htmlspecialchars($p['name']) ?></h3>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <?php if ($p['sale_price']): ?>
                                                <div class="product-original-price">
                                                    <?= number_format($p['price'], 0, ',', '.') ?>đ
                                                </div>
                                                <div class="product-price">
                                                    <?= number_format($p['sale_price'], 0, ',', '.') ?>đ
                                                </div>
                                            <?php else: ?>
                                                <div class="product-price">
                                                    <?= number_format($p['price'], 0, ',', '.') ?>đ
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <small class="text-muted">
                                            Còn <?= $p['stock_quantity'] ?> Dịch vụ
                                        </small>
                                    </div>
                                </div>
                            </a>
                            <button class="btn-add-cart" onclick="addToCart(<?= $p['id'] ?>)">
                                Đăng ký khám
                            </button>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        function addToCart(productId) {
            fetch('add_to_cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `product_id=${productId}&quantity=1`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Đã Đăng ký khám hàng!');
                    } else {
                        alert(data.message || 'Có lỗi xảy ra!');
                    }
                });
        }
    </script>
</body>

</html>