<?php
require_once 'config.php';

// Lấy danh sách Dịch vụ
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

// Lấy danh sách Chuyên khoa
$stmt_doctors = $pdo->query("SELECT * FROM suppliers ORDER BY id ASC LIMIT 4");
$doctors = $stmt_doctors->fetchAll();
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHÒNG KHÁM BHH - Đặt lịch khám thông minh</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">

</head>

<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark" style="background: linear-gradient(90deg, #007bff 0%, #43cea2 100%);">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="index.php" style="font-weight: bold; font-size: 28px; color: #ffe600;">
                Phòng Khám Thông Minh BHH
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
                        <a class="nav-link text-white" href="">Đặt lịch khám</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="">Hướng dẫn</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="">Liên hệ</a>
                    </li>
                </ul>
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item me-2">
                        <!-- <form class="d-flex" method="GET" action="products.php">
                            <input class="form-control form-control-sm me-2" type="search" name="search" placeholder="Tìm Dịch vụ..." aria-label="Search">
                            <button class="btn btn-outline-light btn-sm" type="submit"><i class="fas fa-search"></i></button>
                        </form> -->
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
        <div class="carousel-inner" style="min-height: 560px;">
            <div class="carousel-item active">
                <div style="
                    min-height: 560px;
                    background: url('sources/anh2.png') center 25%/cover no-repeat;
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                    justify-content: center;
                    position: relative;
                ">
                    <div class="banner-title" style="color: #ffe600; text-shadow: 2px 2px 8px #232323;">
                        Khám Phá Dịch Vụ Mới Nhất
                    </div>
                    <button class="banner-btn" style="margin-top: 32px;" onclick="window.location.href='products.php'">XEM NGAY</button>
                </div>
            </div>
        </div>
    </div>

    <div class="container py-5">
        <div class="row text-center">
            <div class="col-12 mb-4">
                <h3 style="color: #103095; font-weight: bold;">Tại sao chọn Phòng Khám BHH?</h3>
                <p class="text-muted">Mang lại giải pháp chăm sóc sức khỏe toàn diện và tin cậy nhất</p>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="feature-box p-4 h-100">
                    <div class="icon-circle mb-3">
                        <i class="fas fa-user-md fa-2x text-white"></i>
                    </div>
                    <h5 class="font-weight-bold mb-3">Đội ngũ chuyên gia</h5>
                    <p class="text-muted">
                        Quy tụ các bác sĩ đầu ngành, giàu kinh nghiệm từ các bệnh viện lớn, tận tâm với người bệnh.
                    </p>
                </div>
            </div>

            <div class="col-md-4 mb-4">
                <div class="feature-box p-4 h-100">
                    <div class="icon-circle mb-3">
                        <i class="fas fa-microscope fa-2x text-white"></i>
                    </div>
                    <h5 class="font-weight-bold mb-3">Trang thiết bị hiện đại</h5>
                    <p class="text-muted">
                        Hệ thống máy móc nhập khẩu 100% từ Đức và Mỹ, đảm bảo kết quả chẩn đoán chính xác nhất.
                    </p>
                </div>
            </div>

            <div class="col-md-4 mb-4">
                <div class="feature-box p-4 h-100">
                    <div class="icon-circle mb-3">
                        <i class="fas fa-clock fa-2x text-white"></i>
                    </div>
                    <h5 class="font-weight-bold mb-3">Hỗ trợ 24/7</h5>
                    <p class="text-muted">
                        Đội ngũ chăm sóc khách hàng và cấp cứu luôn sẵn sàng hỗ trợ bạn bất kể ngày đêm.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container mt-5">
        <!-- Search and Filter -->
        <!-- <div class="category-filter">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <input type="text" class="form-control" name="search" 
                           value="<?= htmlspecialchars($search) ?>" 
                           placeholder="Tìm kiếm Dịch vụ...">
                </div>
                <div class="col-md-4">
                    <select class="form-select" name="category">
                        <option value="0">Tất cả Chuyên khoa</option>
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
        </div> -->

        <!-- Products Grid -->
        <!-- <div class="row">
            <?php if (empty($products)): ?>
                <div class="col-12 text-center">
                    <p class="lead text-muted">Không tìm thấy Dịch vụ nào.</p>
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
                                                <i class="fas fa-cart-plus"></i> Đăng ký khám
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
    </div> -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

        <div class="container py-5" style="background-color: #F0F5FA;">
            <h2 class="text-center mb-5" style="color: #103095; font-weight: bold;">Dịch vụ</h2>

            <div class="row">
                <div class="col-lg-7 col-md-12">
                    <div class="row g-3">
                        <div class="col-md-4 col-4 mb-3">
                            <div class="service-card active" onclick="changeService('thankinh', this)">
                                <i class="fas fa-plus-square fa-2x mb-2"></i>
                                <h6>Thần kinh</h6>
                            </div>
                        </div>

                        <div class="col-md-4 col-4 mb-3">
                            <div class="service-card" onclick="changeService('timmach', this)">
                                <i class="fas fa-heartbeat fa-2x mb-2"></i>
                                <h6>Tim mạch</h6>
                            </div>
                        </div>

                        <div class="col-md-4 col-4 mb-3">
                            <div class="service-card" onclick="changeService('chanthuong', this)">
                                <i class="fas fa-stethoscope fa-2x mb-2"></i>
                                <h6>Chấn thương<br>chỉnh hình</h6>
                            </div>
                        </div>

                        <div class="col-md-4 col-4 mb-3">
                            <div class="service-card" onclick="changeService('phauthuat', this)">
                                <i class="fas fa-syringe fa-2x mb-2"></i>
                                <h6>Phẫu thuật</h6>
                            </div>
                        </div>

                        <div class="col-md-4 col-4 mb-3">
                            <div class="service-card" onclick="changeService('nhakhoa', this)">
                                <i class="fas fa-hospital fa-2x mb-2"></i>
                                <h6>Nha khoa</h6>
                            </div>
                        </div>

                        <div class="col-md-4 col-4 mb-3">
                            <div class="service-card" onclick="changeService('chandoan', this)">
                                <i class="fas fa-wave-square fa-2x mb-2"></i>
                                <h6>Chẩn đoán hình<br>ảnh</h6>
                            </div>
                        </div>

                        <div class="col-md-4 col-4 mb-3">
                            <div class="service-card" onclick="changeService('tietnieu', this)">
                                <i class="fas fa-clipboard-list fa-2x mb-2"></i>
                                <h6>Tiết niệu</h6>
                            </div>
                        </div>

                        <div class="col-md-4 col-4 mb-3">
                            <div class="service-card" onclick="changeService('noikhoa', this)">
                                <i class="fas fa-band-aid fa-2x mb-2"></i>
                                <h6>Nội khoa</h6>
                            </div>
                        </div>

                        <div class="col-md-4 col-4 mb-3">
                            <div class="service-card" onclick="changeService('xemthem', this)">
                                <i class="fas fa-briefcase-medical fa-2x mb-2"></i>
                                <h6>Xem thêm</h6>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="col-lg-5 col-md-12">
                    <div id="service-detail-panel" class="p-4 h-100 d-flex flex-column justify-content-center">
                        <h3 class="text-primary font-weight-bold mb-4">Khoa Thần kinh</h3>
                        <ul class="list-unstyled text-secondary" style="line-height: 2.5;">
                            <li><i class="fas fa-crosshairs text-primary mr-2 small"></i> Tư vấn chuyên khoa thần kinh</li>
                            <li><i class="fas fa-crosshairs text-primary mr-2 small"></i> Chăm sóc toàn diện não bộ và thần kinh</li>
                            <li><i class="fas fa-crosshairs text-primary mr-2 small"></i> Dịch vụ chẩn đoán hình ảnh tiên tiến</li>
                            <li><i class="fas fa-crosshairs text-primary mr-2 small"></i> Điều trị động kinh và co giật</li>
                            <li><i class="fas fa-crosshairs text-primary mr-2 small"></i> Đánh giá trí nhớ và nhận thức</li>
                            <li><i class="fas fa-crosshairs text-primary mr-2 small"></i> Quản lý rối loạn vận động</li>
                        </ul>
                        <a href="booking.php" class="btn btn-primary rounded-pill mt-3 px-4 py-2 font-weight-bold" style="width: fit-content;">Đặt lịch khám ngay</a>
                    </div>
                </div>
            </div>
        </div>
        <section class="container py-5">
            <div class="text-center mb-5">
                <h3 style="color: #103095; font-weight: bold;">Đội ngũ Chuyên gia</h3>
                <p class="text-muted">Các bác sĩ đầu ngành, giàu kinh nghiệm và tận tâm</p>
            </div>

            <div class="row">
                <div class="row">
                    <?php foreach ($doctors as $doctor): ?>
                        <div class="col-md-3 col-sm-6 mb-4">
                            <div class="doctor-card text-center p-4 h-100">
                                <img src="<?= htmlspecialchars($doctor['image']) ?>"
                                    alt="<?= htmlspecialchars($doctor['name']) ?>"
                                    class="doctor-img mb-3"
                                    style="width: 150px; height: 150px; object-fit: cover; border-radius: 50%;">

                                <h5 class="font-weight-bold text-dark mb-1">
                                    <?= htmlspecialchars($doctor['name']) ?>
                                </h5>

                                <p class="text-primary small mb-3">
                                    <?= htmlspecialchars($doctor['address']) ?>
                                </p>

                                <a href="booking.php?doctor_id=<?= $doctor['id'] ?>" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                                    Đặt lịch
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="col-md-3 col-sm-6 mb-4">
                    <div class="doctor-card text-center p-4">
                        <img src="https://via.placeholder.com/150" alt="Bác sĩ B" class="doctor-img mb-3">
                        <h5 class="font-weight-bold text-dark mb-1">ThS.BS Trần Thị B</h5>
                        <p class="text-primary small mb-3">Khoa Tim mạch</p>
                        <button class="btn btn-outline-primary btn-sm rounded-pill px-3">Đặt lịch</button>
                    </div>
                </div>

                <div class="col-md-3 col-sm-6 mb-4">
                    <div class="doctor-card text-center p-4">
                        <img src="https://via.placeholder.com/150" alt="Bác sĩ C" class="doctor-img mb-3">
                        <h5 class="font-weight-bold text-dark mb-1">BSCKII Lê Văn C</h5>
                        <p class="text-primary small mb-3">Chấn thương chỉnh hình</p>
                        <button class="btn btn-outline-primary btn-sm rounded-pill px-3">Đặt lịch</button>
                    </div>
                </div>

                <div class="col-md-3 col-sm-6 mb-4">
                    <div class="doctor-card text-center p-4">
                        <img src="https://via.placeholder.com/150" alt="Bác sĩ D" class="doctor-img mb-3">
                        <h5 class="font-weight-bold text-dark mb-1">BS Phạm Thị D</h5>
                        <p class="text-primary small mb-3">Nha khoa</p>
                        <button class="btn btn-outline-primary btn-sm rounded-pill px-3">Đặt lịch</button>
                    </div>
                </div>
            </div>
        </section>
        <section class="py-5" style="background-color: #F0F5FA;">
            <div class="container">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3 style="color: #103095; font-weight: bold;">Tin tức & Sự kiện</h3>
                    <a href="#" class="text-primary font-weight-bold">Xem tất cả <i class="fas fa-arrow-right ml-1"></i></a>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-4">
                        <div class="news-card bg-white h-100">
                            <img src="https://via.placeholder.com/400x250" class="w-100" alt="Tin tuc 1">
                            <div class="p-3">
                                <small class="text-muted"><i class="far fa-calendar-alt mr-1"></i> 20/01/2026</small>
                                <h5 class="mt-2 font-weight-bold text-dark">Dấu hiệu sớm của bệnh đột quỵ bạn cần biết</h5>
                                <p class="text-muted small mt-2">Đột quỵ có thể xảy ra với bất kỳ ai. Hãy tìm hiểu các dấu hiệu nhận biết sớm...</p>
                                <a href="#" class="text-primary font-weight-bold small">Đọc tiếp</a>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 mb-4">
                        <div class="news-card bg-white h-100">
                            <img src="https://via.placeholder.com/400x250" class="w-100" alt="Tin tuc 2">
                            <div class="p-3">
                                <small class="text-muted"><i class="far fa-calendar-alt mr-1"></i> 18/01/2026</small>
                                <h5 class="mt-2 font-weight-bold text-dark">Lịch nghỉ tết Nguyên Đán 2026</h5>
                                <p class="text-muted small mt-2">Phòng khám xin thông báo lịch nghỉ tết và lịch trực cấp cứu trong dịp lễ...</p>
                                <a href="#" class="text-primary font-weight-bold small">Đọc tiếp</a>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 mb-4">
                        <div class="news-card bg-white h-100">
                            <img src="https://via.placeholder.com/400x250" class="w-100" alt="Tin tuc 3">
                            <div class="p-3">
                                <small class="text-muted"><i class="far fa-calendar-alt mr-1"></i> 15/01/2026</small>
                                <h5 class="mt-2 font-weight-bold text-dark">Gói khám sức khỏe tổng quát ưu đãi 30%</h5>
                                <p class="text-muted small mt-2">Chương trình tri ân khách hàng nhân dịp đầu năm mới với nhiều ưu đãi...</p>
                                <a href="#" class="text-primary font-weight-bold small">Đọc tiếp</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <button class="chat-toggle-btn" onclick="toggleChat()">
            <i class="fas fa-comment-dots"></i>
        </button>

        <div id="chat-widget" class="chat-widget">
            <div class="chat-header">
                <div class="d-flex align-items-center">
                    <div class="chat-avatar mr-2">
                        <i class="fas fa-robot"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 font-weight-bold">Trợ lý ảo BHH</h6>
                        <small class="text-white-50">Hỗ trợ 24/7</small>
                    </div>
                </div>
                <span class="close-chat" onclick="toggleChat()">&times;</span>
            </div>

            <div class="chat-body" id="chat-body">
                <div class="message bot-message">
                    Xin chào! Tôi là trợ lý ảo của phòng khám. Tôi có thể giúp gì cho bạn? 🏥
                </div>

                <div class="chat-options mt-3">
                    <button class="option-btn" onclick="botReply('price')">💰 Bảng giá khám</button>
                    <button class="option-btn" onclick="botReply('address')">📍 Địa chỉ ở đâu?</button>
                    <button class="option-btn" onclick="botReply('book')">📅 Đặt lịch thế nào?</button>
                    <button class="option-btn" onclick="botReply('human')">👨‍⚕️ Gặp tư vấn viên</button>
                </div>
            </div>

            <div class="chat-footer">
                <input type="text" id="chat-input" placeholder="Nhập tin nhắn..." onkeypress="handleEnter(event)">

                <button onclick="sendMessage()"><i class="fas fa-paper-plane"></i></button>
            </div>
        </div>
        <div id="consultation-modal" class="modal-overlay">
            <div class="modal-content">
                <span class="close-modal" onclick="closeModal()">&times;</span>
                <h3 class="text-primary font-weight-bold text-center mb-4">Đăng ký tư vấn miễn phí</h3>

                <form>
                    <div class="form-group mb-3">
                        <input type="text" class="form-control" placeholder="Họ và tên của bạn" required>
                    </div>
                    <div class="form-group mb-3">
                        <input type="text" class="form-control" placeholder="Số điện thoại" required>
                    </div>
                    <div class="form-group mb-3">
                        <textarea class="form-control" rows="4" placeholder="Bạn cần tư vấn về vấn đề gì?"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block w-100 font-weight-bold">GỬI YÊU CẦU</button>
                </form>
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
                            © 2023 BHH.
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


    </div>
    <script src="js/script.js"></script>
</body>

</html>