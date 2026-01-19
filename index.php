<?php
require_once 'config.php';

// Lấy danh sách sản phẩm
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';
$category_id = isset($_GET['category']) ? (int)$_GET['category'] : 0;

$sql = "SELECT p.*, c.name as category_name FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE p.status = 'active'";

$params = [];

if ($search) {
    $sql .= " AND (p.name LIKE ? OR p.code LIKE ? OR p.colors LIKE ?)";
    $searchTerm = "%{$search}%";
    $params = [$searchTerm, $searchTerm, $searchTerm];
}

if ($category_id > 0) {
    $sql .= $search ? " AND p.category_id = ?" : " AND p.category_id = ?";
    $params[] = $category_id;
}

$sql .= " ORDER BY p.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

// Lấy danh sách danh mục
$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Phòng Khám Đa Khoa Ánh Sáng</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', 'Segoe UI', Arial, sans-serif;
            background: #f6f8fa;
            color: #222;
        }

        .navbar {
            background: linear-gradient(90deg, #007bff 0%, #43cea2 100%) !important;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.07);
        }

        .navbar-brand {
            font-weight: bold;
            font-size: 28px;
            color: #ffe600 !important;
            letter-spacing: 1px;
        }

        .navbar-nav .nav-link {
            color: #fff !important;
            font-size: 16px;
            font-weight: 500;
            margin: 0 8px;
            transition: color 0.2s;
        }

        .navbar-nav .nav-link.text-warning {
            color: #ffe600 !important;
            font-weight: bold;
        }

        .navbar-nav .nav-link:hover,
        .navbar-nav .nav-link.active {
            color: #43cea2 !important;
        }

        .navbar-toggler {
            border: none;
        }

        .banner-carousel {
            background: #232323;
        }

        .carousel-inner {
            min-height: 420px;
            border-radius: 0 0 24px 24px;
            overflow: hidden;
        }

        .carousel-item {
            position: relative;
        }

        .carousel-control-prev-icon,
        .carousel-control-next-icon {
            background-color: #ffe600;
            border-radius: 50%;
        }

        .category-filter {
            background: #fff;
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 32px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .product-card {
            border: none;
            border-radius: 14px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s, box-shadow 0.3s;
            background: #fff;
        }

        .product-card:hover {
            transform: translateY(-7px) scale(1.03);
            box-shadow: 0 8px 24px rgba(67, 206, 162, 0.13);
        }

        .card-title {
            font-size: 18px;
            font-weight: 600;
            color: #232323;
        }

        .price-original {
            text-decoration: line-through;
            color: #6c757d;
            font-size: 15px;
        }

        .price-sale {
            color: #dc3545;
            font-weight: bold;
            font-size: 18px;
        }

        .btn-outline-primary {
            border-radius: 6px;
            font-weight: 500;
        }

        .btn-primary,
        .btn-outline-primary:active {
            background: linear-gradient(90deg, #007bff 0%, #43cea2 100%);
            border: none;
            font-weight: 600;
            border-radius: 6px;
        }

        .btn-primary:hover {
            background: linear-gradient(90deg, #43cea2 0%, #007bff 100%);
        }

        .footer {
            background: #232323;
            color: #fff;
            padding: 48px 0 0 0;
            font-family: 'Roboto', Arial, sans-serif;
            margin-top: 48px;
        }

        .footer h5 {
            color: #ffe600;
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 18px;
            letter-spacing: 0.5px;
        }

        .footer a {
            color: #fff;
            text-decoration: none;
            transition: color 0.2s;
        }

        .footer a:hover {
            color: #43cea2;
            text-decoration: underline;
        }

        .footer .social-icons a {
            font-size: 22px;
            margin-right: 14px;
            color: #fff;
            transition: color 0.2s;
        }

        .footer .social-icons a:hover {
            color: #ffe600;
        }

        .footer .footer-bottom {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            margin-top: 30px;
            padding-bottom: 18px;
        }

        .footer .footer-bottom button {
            background: #002b5c;
            color: #fff;
            border: none;
            border-radius: 6px;
            padding: 8px 18px;
            font-weight: bold;
            font-size: 15px;
        }

        @media (max-width: 991px) {
            .navbar-nav.mx-auto {
                margin-left: 0 !important;
                margin-right: 0 !important;
            }

            .footer .container {
                flex-direction: column;
            }
        }

        @media (max-width: 600px) {
            .category-filter {
                padding: 12px;
            }

            .footer {
                padding: 24px 0 0 0;
            }

            .carousel-inner {
                min-height: 220px;
            }
        }

        .banner-carousel .banner-title {
            font-size: 48px;
            font-weight: bold;
            color: #ffe600;
            text-align: center;
            margin-top: 80px;
            text-shadow: 2px 2px 8px #232323;
        }

        .banner-carousel .banner-sub {
            font-size: 28px;
            color: #fff;
            text-align: center;
            margin-bottom: 32px;
        }

        .banner-carousel .banner-btn {
            display: block;
            margin: 32px auto 0 auto;
            font-size: 22px;
            font-weight: bold;
            background: #fff;
            color: #232323;
            border: none;
            border-radius: 30px;
            padding: 10px 32px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
        }
    </style>
</head>

<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark" style="background: linear-gradient(90deg, #007bff 0%, #43cea2 100%);">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="index.php" style="font-weight: bold; font-size: 28px; color: #ffe600;">
                Đa Khoa Ánh Sáng
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-center" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link text-white" href="index.php">Trang chủ</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="products.php">Các gói khám bệnh</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="news.php">Tin tức và khuyến mãi</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="contact.php">Liên hệ</a>
                    </li>
                </ul>
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item me-2">
                        <form class="d-flex" method="GET" action="products.php">
                            <input class="form-control form-control-sm me-2" type="search" name="search" placeholder="Tìm sản phẩm..." aria-label="Search">
                            <button class="btn btn-outline-light btn-sm" type="submit"><i class="fas fa-search"></i></button>
                        </form>
                    </li>
                    <?php if (isLoggedIn()): ?>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="cart.php">
                                <i class="fas fa-shopping-cart"></i>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="orders.php">
                                <i class="fas fa-box"></i>
                            </a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link text-white dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user"></i>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                <li><a class="dropdown-item" href="profile.php">Thông tin cá nhân</a></li>
                                <?php if (isAdmin()): ?>
                                    <li><a class="dropdown-item" href="admin/">Quản trị</a></li>
                                <?php endif; ?>
                                <li><a class="dropdown-item" href="logout.php">Đăng xuất</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="login.php"><i class="fas fa-sign-in-alt"></i> Đăng nhập</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="register.php"><i class="fas fa-user-plus"></i> Đăng ký</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>


    <div class="banner-carousel">
        <div class="carousel-inner" style="min-height: 420px;">
            <div class="carousel-item active">
                <div style="
                    min-height: 420px;
                    background: url('https://bizweb.dktcdn.net/100/340/361/themes/913887/assets/slider_3.jpg?1753158264387') center/cover no-repeat;
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                    justify-content: center;
                    position: relative;
                ">
                    <div class="banner-title" style="color: #ffe600; text-shadow: 2px 2px 8px #232323;">
                        Khám Phá Bộ Sưu Tập Mới Nhất
                    </div>
                    <button class="banner-btn" style="margin-top: 32px;" onclick="window.location.href='products.php'">XEM NGAY</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bộ sưu tập dưới banner -->
    <div class="container" style="margin-top: 40px; margin-bottom: 40px;">
        <h2 style="text-align: center; font-weight: 600; letter-spacing: 2px; margin-bottom: 18px;">BỘ SƯU TẬP</h2>
        <div style="width: 80px; height: 3px; background: #232323; margin: 0 auto 32px auto; border-radius: 2px;"></div>
        <div class="row g-4 justify-content-center">
            <!-- Bộ sưu tập 1: Áo thể thao nam -->
            <div class="col-12 col-sm-6 col-md-3">
                <a href="products.php?category=1" style="text-decoration: none; color: inherit;">
                    <div style="position: relative; overflow: hidden; border-radius: 12px;">
                        <img src="https://bizweb.dktcdn.net/thumb/large/100/340/361/products/ao-thun-terrex-xperior-climacool-trang-jn8134-hm30.jpg?v=1745035366060" alt="Áo thể thao nam" style="width: 100%; aspect-ratio: 1/1; object-fit: cover;">
                        <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; 
                            background: rgba(0,0,0,0.18); display: flex; align-items: center; justify-content: center;">
                            <span style="color: #fff; font-size: 2rem; font-weight: 600; text-align: center; letter-spacing: 1px; text-shadow: 1px 1px 8px #232323;">
                                ÁO THỂ THAO NAM
                            </span>
                        </div>
                    </div>
                </a>
            </div>
            <!-- Bộ sưu tập 2: Quần thể thao nam -->
            <div class="col-12 col-sm-6 col-md-3">
                <a href="products.php?category=2" style="text-decoration: none; color: inherit;">
                    <div style="position: relative; overflow: hidden; border-radius: 12px;">
                        <img src="https://kingmensport.vn/wp-content/uploads/2021/06/27-768x768.jpg" alt="Quần thể thao nam" style="width: 100%; aspect-ratio: 1/1; object-fit: cover;">
                        <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; 
                            background: rgba(0,0,0,0.18); display: flex; align-items: center; justify-content: center;">
                            <span style="color: #fff; font-size: 2rem; font-weight: 600; text-align: center; letter-spacing: 1px; text-shadow: 1px 1px 8px #232323;">
                                QUẦN THỂ THAO NAM
                            </span>
                        </div>
                    </div>
                </a>
            </div>
            <!-- Bộ sưu tập 3: Giày thể thao nam -->
            <div class="col-12 col-sm-6 col-md-3">
                <a href="products.php?category=3" style="text-decoration: none; color: inherit;">
                    <div style="position: relative; overflow: hidden; border-radius: 12px;">
                        <img src="https://kingmensport.vn/wp-content/uploads/2019/09/10-11-768x654.jpg" alt="Giày thể thao nam" style="width: 100%; aspect-ratio: 1/1; object-fit: cover;">
                        <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; 
                            background: rgba(0,0,0,0.18); display: flex; align-items: center; justify-content: center;">
                            <span style="color: #fff; font-size: 2rem; font-weight: 600; text-align: center; letter-spacing: 1px; text-shadow: 1px 1px 8px #232323;">
                                GIÀY THỂ THAO NAM
                            </span>
                        </div>
                    </div>
                </a>
            </div>
            <!-- Bộ sưu tập 4: Giày bóng đá -->
            <div class="col-12 col-sm-6 col-md-3">
                <a href="products.php?category=5" style="text-decoration: none; color: inherit;">
                    <div style="position: relative; overflow: hidden; border-radius: 12px;">
                        <img src="https://kingmensport.vn/wp-content/uploads/2023/02/331131552_5933897656655855_510054232188727582_n-768x768.jpg" alt="Giày bóng đá" style="width: 100%; aspect-ratio: 1/1; object-fit: cover;">
                        <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; 
                            background: rgba(0,0,0,0.18); display: flex; align-items: center; justify-content: center;">
                            <span style="color: #fff; font-size: 2rem; font-weight: 600; text-align: center; letter-spacing: 1px; text-shadow: 1px 1px 8px #232323;">
                                GIÀY BÓNG ĐÁ
                            </span>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container mt-5">
        <!-- Search and Filter -->
        <div class="category-filter">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <input type="text" class="form-control" name="search"
                        value="<?= htmlspecialchars($search) ?>"
                        placeholder="Tìm kiếm sản phẩm...">
                </div>
                <div class="col-md-4">
                    <select class="form-select" name="category">
                        <option value="0">Tất cả danh mục</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= $category['id'] ?>"
                                <?= $category_id == $category['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($category['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Tìm kiếm
                    </button>
                    <a href="index.php" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>

        <!-- Products Grid -->
        <div class="row">
            <?php if (empty($products)): ?>
                <div class="col-12 text-center">
                    <p class="lead text-muted">Không tìm thấy sản phẩm nào.</p>
                </div>
            <?php else: ?>
                <?php foreach ($products as $product): ?>
                    <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                        <div class="card product-card h-100">
                            <img src="<?= $product['image_url'] ?: 'images/no-image.jpg' ?>"
                                class="card-img-top" alt="<?= htmlspecialchars($product['name']) ?>"
                                style="height: 200px; object-fit: cover;">

                            <div class="card-body d-flex flex-column">
                                <h6 class="card-title"><?= htmlspecialchars($product['name']) ?></h6>
                                <p class="text-muted small">Mã: <?= htmlspecialchars($product['code']) ?></p>
                                <p class="text-muted small mb-2"><?= htmlspecialchars($product['category_name']) ?></p>

                                <div class="mt-auto">
                                    <div class="price-section mb-3">
                                        <?php if ($product['sale_price']): ?>
                                            <span class="price-original"><?= formatPrice($product['price']) ?></span><br>
                                            <span class="price-sale h6"><?= formatPrice($product['sale_price']) ?></span>
                                        <?php else: ?>
                                            <span class="h6 text-primary"><?= formatPrice($product['price']) ?></span>
                                        <?php endif; ?>
                                    </div>

                                    <div class="d-grid gap-2">
                                        <a href="product_detail.php?id=<?= $product['id'] ?>"
                                            class="btn btn-outline-primary btn-sm">Xem chi tiết</a>

                                        <?php if (isLoggedIn()): ?>
                                            <button class="btn btn-primary btn-sm add-to-cart"
                                                data-product-id="<?= $product['id'] ?>">
                                                <i class="fas fa-cart-plus"></i> Thêm vào giỏ
                                            </button>
                                        <?php else: ?>
                                            <a href="login.php" class="btn btn-primary btn-sm">
                                                Đăng nhập để mua
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Tin tức và khuyến mại -->
    <div class="container" style="margin-top: 40px; margin-bottom: 40px;">
        <div class="row">
            <!-- Tin tức -->
            <div class="col-md-6 mb-4">
                <div style="background: #232323; color: #fff; border-radius: 8px 8px 0 0; padding: 10px 18px; font-size: 1.3rem; font-weight: bold;">
                    TIN TỨC
                </div>
                <div style="background: #fff; border-radius: 0 0 8px 8px; padding: 18px;">
                    <div class="d-flex mb-3">
                        <img src="https://bizweb.dktcdn.net/100/340/361/files/xx-07445.jpg?v=1743673474622" alt="Tin tức mới" style="width: 90px; height: 70px; object-fit: cover; border-radius: 6px; margin-right: 16px;">
                        <div>
                            <div style="font-weight: bold;"> UPDATE VỢT PICKLEBALL &amp; CẦU LÔNG - BỘ ĐÔI VỢT THỂ THAO ĐƯỢC MONG ĐỢI</div>
                            <div style="font-size: 14px; color: #444;">Tin vui dành cho chính thức lên kệ các sản phẩm vợt thể thao Pickleball và Cầu lông...</div>
                        </div>
                    </div>
                    <div class="d-flex mb-3">
                        <img src="https://bizweb.dktcdn.net/100/340/361/articles/vn-11134208-7r98o-lwomew7p8us94d.jpg?v=1742544992490" alt="1990s sneaker" style="width: 90px; height: 70px; object-fit: cover; border-radius: 6px; margin-right: 16px;">
                        <div>
                            <div style="font-weight: bold;">“REPLY 1990s” CÙNG XU HƯỚNG SNEAKER DỄ MÓNG</div>
                            <div style="font-size: 14px; color: #444;">Quay vòng thời trang, giày thể thao đế mỏng đã trở thành xu hướng lớn trong thị trường thời trang...</div>
                        </div>
                    </div>
                    <div class="text-center">
                        <a href="#" style="border: 1px solid #232323; border-radius: 8px; padding: 7px 22px; color: #232323; background: #fff; font-weight: 500; text-decoration: none; transition: background 0.2s;">Xem tất cả &gt;</a>
                    </div>
                </div>
            </div>
            <!-- Tin khuyến mại -->
            <div class="col-md-6 mb-4">
                <div style="background: #232323; color: #fff; border-radius: 8px 8px 0 0; padding: 10px 18px; font-size: 1.3rem; font-weight: bold;">
                    TIN KHUYẾN MẠI
                </div>
                <div style="background: #fff; border-radius: 0 0 8px 8px; padding: 18px;">
                    <div class="d-flex mb-3">
                        <img src="https://bizweb.dktcdn.net/100/340/361/files/xx-07445.jpg?v=1743673474622" alt="Khuyến mại 800k" style="width: 90px; height: 70px; object-fit: cover; border-radius: 6px; margin-right: 16px;">
                        <div>
                            <div style="font-weight: bold;">MUA CÀNG NHIỀU HOÀN CÀNG LỚN TỚI 800K</div>
                            <div style="font-size: 14px; color: #444;">&#128640; Hoàn ngay 500.000vnđ khi mua sắm với hoá đơn từ 2.500.000 - 3.500.000vnđ &#128640; Hoàn ngay 800.000 khi mua sắm với hoá...</div>
                        </div>
                    </div>
                    <div class="d-flex mb-3">
                        <img src="https://bizweb.dktcdn.net/100/340/361/files/xx-07445.jpg?v=1743673474622" alt="1050 sale" style="width: 90px; height: 70px; object-fit: cover; border-radius: 6px; margin-right: 16px;">
                        <div>
                            <div style="font-weight: bold;">🎉🎉🎉 TƯNG BỪNG KHAI TRƯƠNG </div>
                            <div style="font-size: 14px; color: #444;">Dành riêng cho fan hàng hiệu ưu đãi độc quyền 🔥 SALE UP TO 50% tất cả các thương hiệu adidas,...</div>
                        </div>
                    </div>
                    <div class="text-center">
                        <a href="#" style="border: 1px solid #232323; border-radius: 8px; padding: 7px 22px; color: #232323; background: #fff; font-weight: 500; text-decoration: none; transition: background 0.2s;">Xem tất cả &gt;</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer style="background: #232323; color: #fff; padding: 40px 0 0 0; font-family: Arial, sans-serif;">
        <div class="container" style="max-width: 1200px; margin: auto;">
            <div style="display: flex; flex-wrap: wrap; justify-content: space-between;">
                <!-- Logo & Contact -->
                <div style="flex: 1 1 320px; margin-bottom: 30px;">
                    <!-- Đã bỏ logo Maxx Sport và báo cáo Bộ Công Thương -->
                    <div style="margin-bottom: 12px;">
                        <i class="fas fa-map-marker-alt"></i> Địa chỉ:Ba chấm , Thanh Hóa
                    </div>
                    <div style="margin-bottom: 12px;">
                        <i class="fas fa-phone"></i> Số điện thoại: 096969969
                    </div>
                    <div style="margin-bottom: 12px;">
                        <i class="fas fa-envelope"></i> Email: blabla@gmail.com
                    </div>
                    <div style="font-size: 13px; color: #bbb; margin-top: 18px;">
                        © 2025 Phòng Khám Đa Khoa Ánh Sáng. Bảo lưu mọi quyền.
                    </div>
                </div>
                <!-- Chính sách -->
                <div style="flex: 1 1 180px; margin-bottom: 30px;">
                    <h5 style="margin-bottom: 18px;">CHÍNH SÁCH</h5>
                    <ul style="list-style: none; padding: 0; font-size: 15px;">
                        <li><a href="#" style="color: #fff; text-decoration: none;">Chính sách bảo mật</a></li>
                        <li><a href="#" style="color: #fff; text-decoration: none;">Quy định sử dụng</a></li>
                        <li><a href="#" style="color: #fff; text-decoration: none;">Chính sách thanh toán</a></li>
                        <li><a href="#" style="color: #fff; text-decoration: none;">Chính sách vận chuyển</a></li>
                        <li><a href="#" style="color: #fff; text-decoration: none;">Đổi trả hàng online</a></li>
                        <li><a href="#" style="color: #fff; text-decoration: none;">Đổi trả hàng tại shop</a></li>
                    </ul>
                </div>
                <!-- Về chúng tôi -->
                <div style="flex: 1 1 180px; margin-bottom: 30px;">
                    <h5 style="margin-bottom: 18px;">VỀ CHÚNG TÔI</h5>
                    <ul style="list-style: none; padding: 0; font-size: 15px;">
                        <li><a href="#" style="color: #fff; text-decoration: none;">Giới thiệu</a></li>
                        <li><a href="#" style="color: #fff; text-decoration: none;">Hướng dẫn mua hàng online</a></li>
                        <li><a href="#" style="color: #fff; text-decoration: none;">Tuyển dụng</a></li>
                        <li><a href="#" style="color: #fff; text-decoration: none;">Hệ thống cửa hàng</a></li>
                        <li><a href="#" style="color: #fff; text-decoration: none;">Tuyển đại lý</a></li>
                    </ul>
                </div>
                <!-- Đăng ký nhận tin -->
                <div style="flex: 1 1 220px; margin-bottom: 30px;">
                    <h5 style="margin-bottom: 18px;">ĐĂNG KÝ NHẬN TIN</h5>
                    <div style="margin-bottom: 10px; font-size: 15px;">Bạn muốn nhận khuyến mãi đặc biệt?<br>Đăng ký ngay.</div>
                    <form style="display: flex; margin-bottom: 14px;">
                        <input type="email" placeholder="Nhập địa chỉ email" style="flex:1; padding: 8px 12px; border-radius: 30px 0 0 30px; border: none;">
                        <button type="submit" style="background: #fff; color: #232323; border: none; border-radius: 0 30px 30px 0; padding: 8px 22px; font-weight: bold;">Đăng ký</button>
                    </form>
                    <div style="display: flex; gap: 12px; margin-top: 8px;">
                        <a href="#" style="color: #3b5998;"><i class="fab fa-facebook fa-lg"></i></a>
                        <a href="#" style="color: #0084ff;"><i class="fab fa-zalo fa-lg"></i></a>
                        <a href="#" style="color: #e4405f;"><i class="fab fa-instagram fa-lg"></i></a>
                        <a href="#" style="color: #ff0000;"><i class="fab fa-youtube fa-lg"></i></a>
                        <a href="#" style="color: #000;"><i class="fab fa-tiktok fa-lg"></i></a>
                    </div>
                </div>
            </div>
            <!-- Dòng cuối cùng -->
            <div style="display: flex; align-items: center; justify-content: flex-end; margin-top: 30px; padding-bottom: 18px;">
                <div>
                    <button style="background: #002b5c; color: #fff; border: none; border-radius: 6px; padding: 8px 18px; font-weight: bold; font-size: 15px;">
                        <i class="fas fa-bell"></i> HÀNG MỚI
                    </button>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Add to cart functionality
        document.querySelectorAll('.add-to-cart').forEach(button => {
            button.addEventListener('click', function() {
                const productId = this.dataset.productId;

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
                            alert('Đã thêm sản phẩm vào danh sách đăng ký!');
                        } else {
                            alert('Có lỗi xảy ra: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Có lỗi xảy ra khi thêm sản phẩm vào danh sách đăng ký!');
                    });
            });
        });
    </script>


</body>

</html>