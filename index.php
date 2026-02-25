<?php
session_start();

// --- 1. KẾT NỐI DATABASE ---
// Kiểm tra xem có file config không, nếu không thì kết nối trực tiếp
if (file_exists('../config/config.php')) {
    require_once '../config/config.php';
} else {
    $host = 'localhost';
    $db   = 'phongkham';
    $user = 'root';
    $pass = '';
    $charset = 'utf8mb4';
    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    try {
        $pdo = new PDO($dsn, $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (\PDOException $e) {
        die("Lỗi kết nối Database: " . $e->getMessage());
    }
}

// --- 2. LẤY DỮ LIỆU CẦN THIẾT ---

// A. Lấy 4 Gói khám (Dịch vụ) mới nhất để hiển thị
$stmt_services = $pdo->query("SELECT * FROM products WHERE status = 'active' ORDER BY created_at DESC LIMIT 4");
$services = $stmt_services->fetchAll();

// B. Lấy 4 Bác sĩ để hiển thị
$stmt_doctors = $pdo->query("SELECT * FROM suppliers ORDER BY id ASC LIMIT 4");
$doctors = $stmt_doctors->fetchAll();

// C. Hàm kiểm tra đăng nhập (Helper)
if (!function_exists('isLoggedIn')) {
    function isLoggedIn()
    {
        return isset($_SESSION['user_id']);
    }
}
if (!function_exists('isAdmin')) {
    function isAdmin()
    {
        return isset($_SESSION['role']) && $_SESSION['role'] == 'admin';
    }
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHÒNG KHÁM BHH - Chăm sóc sức khỏe toàn diện</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        html {
            scroll-behavior: smooth;
        }

        /* Cuộn trang mượt mà */

        /* CSS tùy chỉnh thêm cho đẹp */
        .service-item {
            transition: all 0.3s;
            cursor: pointer;
        }

        .service-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1) !important;
        }

        .doctor-card img {
            transition: transform 0.3s;
        }

        .doctor-card:hover img {
            transform: scale(1.05);
        }

        .banner-overlay {
            background: linear-gradient(rgba(16, 48, 149, 0.8), rgba(16, 48, 149, 0.6));
        }

        /* Nút chat */
        .chat-toggle-btn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: #007bff;
            color: #fff;
            border: none;
            font-size: 24px;
            box-shadow: 0 4px 15px rgba(0, 123, 255, 0.4);
            z-index: 1000;
            transition: transform 0.2s;
        }

        .chat-toggle-btn:hover {
            transform: scale(1.1);
        }
    </style>
</head>

<body>

    <nav class="navbar navbar-expand-lg bg-white navbar-light shadow-sm sticky-top px-4 py-3">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="index.php" style="font-weight: bold; font-size: 26px; color: #103095;">
                <i class="fas fa-clinic-medical me-2"></i> Phòng Khám BHH
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCollapse">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarCollapse">
                <div class="navbar-nav ms-auto py-0">
                    <a href="index.php" class="nav-item nav-link active">Trang chủ</a>
                    <a href="views/booking.php" class="nav-item nav-link text-primary fw-bold">Đặt lịch khám</a>
                    <a href="#services" class="nav-item nav-link">Gói khám</a>
                    <a href="#doctors" class="nav-item nav-link">Bác sĩ</a>
                    <a href="#footer" class="nav-item nav-link">Liên hệ</a>
                </div>

                <div class="ms-3 border-start ps-3 d-none d-lg-block">
                    <?php if (isLoggedIn()): ?>
                        <div class="dropdown">
                            <button class="btn btn-outline-primary dropdown-toggle rounded-pill px-3" type="button" id="userDropdown" data-bs-toggle="dropdown">
                                <i class="fas fa-user me-1"></i> Tài khoản
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="#">Hồ sơ bệnh án</a></li>
                                <?php if (isAdmin()): ?>
                                    <li><a class="dropdown-item text-danger" href="admin/index.php">Quản trị Admin</a></li>
                                <?php endif; ?>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item" href="logout.php">Đăng xuất</a></li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <a href="views/login.php" class="btn btn-primary rounded-pill px-3 me-2">Đăng nhập</a>
                        <a href="register.php" class="btn btn-primary rounded-pill px-3">Đăng ký</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>


    <div class="container-fluid p-0 mb-5">
        <div class="position-relative" style="height: 600px;">
            <img class="w-100 h-100" src="https://images.unsplash.com/photo-1538108149393-fbbd81895907?w=1600&q=80" style="object-fit: cover;" alt="Phong kham BHH">

            <div class="position-absolute top-0 start-0 w-100 h-100 banner-overlay d-flex align-items-center">
                <div class="container">
                    <div class="row justify-content-start">
                        <div class="col-sm-10 col-lg-8">
                            <h5 class="text-white text-uppercase mb-3 animated slideInDown">Uy tín - Tận tâm - Chuyên nghiệp</h5>
                            <h1 class="display-3 text-white animated slideInDown mb-4">Chăm Sóc Sức Khỏe <br>Toàn Diện Cho Bạn</h1>
                            <p class="fs-5 text-white mb-4 pb-2">Hệ thống đặt lịch khám thông minh, kết nối bác sĩ chuyên khoa hàng đầu mà không cần chờ đợi.</p>

                            <a href="views/booking.php" class="btn btn-warning py-md-3 px-md-5 me-3 rounded-pill fw-bold text-dark">
                                <i class="far fa-calendar-check me-2"></i>ĐẶT LỊCH NGAY
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div class="container-xxl py-5">
        <div class="container">
            <div class="row g-5">
                <div class="col-lg-4 wow fadeIn">
                    <div class="feature-item border rounded p-4 h-100 text-center shadow-sm">
                        <div class="btn-square bg-light rounded-circle mx-auto mb-4" style="width: 80px; height: 80px; display:flex; align-items:center; justify-content:center;">
                            <i class="fa fa-user-md fa-3x text-primary"></i>
                        </div>
                        <h5 class="mb-3">Bác sĩ Đầu ngành</h5>
                        <p class="text-muted">Đội ngũ giáo sư, tiến sĩ, bác sĩ giàu kinh nghiệm từ các bệnh viện lớn.</p>
                    </div>
                </div>
                <div class="col-lg-4 wow fadeIn">
                    <div class="feature-item border rounded p-4 h-100 text-center shadow-sm">
                        <div class="btn-square bg-light rounded-circle mx-auto mb-4" style="width: 80px; height: 80px; display:flex; align-items:center; justify-content:center;">
                            <i class="fa fa-check fa-3x text-primary"></i>
                        </div>
                        <h5 class="mb-3">Dịch vụ Chất lượng</h5>
                        <p class="text-muted">Quy trình khám khép kín, nhanh chóng, thủ tục đơn giản.</p>
                    </div>
                </div>
                <div class="col-lg-4 wow fadeIn">
                    <div class="feature-item border rounded p-4 h-100 text-center shadow-sm">
                        <div class="btn-square bg-light rounded-circle mx-auto mb-4" style="width: 80px; height: 80px; display:flex; align-items:center; justify-content:center;">
                            <i class="fa fa-comment-medical fa-3x text-primary"></i>
                        </div>
                        <h5 class="mb-3">Hỗ trợ 24/7</h5>
                        <p class="text-muted">Tổng đài tư vấn và đặt lịch hoạt động liên tục các ngày trong tuần.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div class="container-xxl py-5 bg-light" id="services">
        <div class="container">
            <div class="text-center mx-auto mb-5" style="max-width: 600px;">
                <p class="d-inline-block border rounded-pill py-1 px-4 text-primary bg-white">Dịch vụ</p>
                <h1 style="color: #103095;">Các Gói Khám Nổi Bật</h1>
            </div>

            <div class="row g-4">
                <?php if (count($services) > 0): ?>
                    <?php foreach ($services as $sv): ?>
                        <div class="col-lg-3 col-md-6">
                            <div class="service-item bg-white rounded h-100 p-4 border text-center">
                                <div class="d-inline-block rounded-circle bg-light p-2 mb-4">
                                    <img class="rounded-circle" src="<?= htmlspecialchars($sv['image_url'] ?? 'https://via.placeholder.com/100') ?>"
                                        style="width: 100px; height: 100px; object-fit: cover;" alt="">
                                </div>

                                <h5 class="mb-3"><?= htmlspecialchars($sv['name']) ?></h5>
                                <p class="text-primary fw-bold fs-5"><?= number_format($sv['price'], 0, ',', '.') ?> đ</p>
                                <p class="text-muted small mb-4"><?= mb_strimwidth($sv['description'] ?? '', 0, 70, "...") ?></p>

                                <a class="btn btn-outline-primary rounded-pill px-4" href="booking.php?note=Đăng ký: <?= urlencode($sv['name']) ?>">
                                    Đặt lịch <i class="fa fa-arrow-right ms-2"></i>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12 text-center text-muted">Đang cập nhật danh sách dịch vụ...</div>
                <?php endif; ?>
            </div>
        </div>
    </div>


    <div class="container-xxl py-5" id="doctors">
        <div class="container">
            <div class="text-center mx-auto mb-5">
                <h1 style="color: #103095;">Đội Ngũ Chuyên Gia</h1>
                <p class="text-muted">Các bác sĩ giàu kinh nghiệm sẵn sàng hỗ trợ bạn</p>
            </div>

            <div class="row g-4">
                <?php foreach ($doctors as $doc): ?>
                    <div class="col-lg-3 col-md-6">
                        <div class="doctor-card bg-white rounded overflow-hidden shadow-sm text-center border">
                            <div class="p-4">
                                <img class="img-fluid rounded-circle mb-3" src="<?= htmlspecialchars($doc['image']) ?>" alt="" style="width: 120px; height: 120px; object-fit: cover;">
                                <h5 class="fw-bold mb-1"><?= htmlspecialchars($doc['name']) ?></h5>
                                <small class="text-primary"><?= htmlspecialchars($doc['address']) ?></small>
                            </div>
                            <div class="d-grid p-3 bg-light">
                                <a class="btn btn-primary btn-sm rounded-pill" href="booking.php?doctor_id=<?= $doc['id'] ?>">Đặt hẹn khám</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>


    <div class="container-fluid bg-dark text-light footer mt-5 pt-5" id="footer">
        <div class="container py-5">
            <div class="row g-5">
                <div class="col-lg-4 col-md-6">
                    <h5 class="text-white mb-4">Phòng Khám BHH</h5>
                    <p class="mb-2"><i class="fa fa-map-marker-alt me-3"></i>Số 10, Đường ABC, TP. Thanh Hóa</p>
                    <p class="mb-2"><i class="fa fa-phone-alt me-3"></i>0969 699 69</p>
                    <p class="mb-2"><i class="fa fa-envelope me-3"></i>tuvan@bhhclinic.com</p>
                    <div class="d-flex pt-2">
                        <a class="btn btn-outline-light btn-social rounded-circle me-1" href=""><i class="fab fa-twitter"></i></a>
                        <a class="btn btn-outline-light btn-social rounded-circle me-1" href=""><i class="fab fa-facebook-f"></i></a>
                        <a class="btn btn-outline-light btn-social rounded-circle me-1" href=""><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <h5 class="text-white mb-4">Liên kết nhanh</h5>
                    <a class="btn btn-link text-white-50" href="#services">Gói khám sức khỏe</a>
                    <a class="btn btn-link text-white-50" href="#doctors">Đội ngũ bác sĩ</a>
                    <a class="btn btn-link text-white-50" href="booking.php">Đặt lịch hẹn</a>
                    <a class="btn btn-link text-white-50" href="#">Chính sách bảo mật</a>
                </div>
                <div class="col-lg-4 col-md-6">
                    <h5 class="text-white mb-4">Giờ làm việc</h5>
                    <p class="mb-1">Thứ 2 - Thứ 6: 08:00 - 17:00</p>
                    <p class="mb-1">Thứ 7: 08:00 - 12:00</p>
                    <p class="text-warning mb-0">Chủ nhật: Nghỉ (Cấp cứu 24/7)</p>
                </div>
            </div>
        </div>
        <div class="container">
            <div class="copyright text-center py-4 border-top border-secondary">
                <p class="mb-0">&copy; <a class="border-bottom text-white" href="#">BHH Clinic</a>. All Rights Reserved.</p>
            </div>
        </div>
    </div>


    !-- NÚT MỞ CHAT -->
    <button class="chat-toggle-btn" onclick="toggleChat()">
        <i class="fas fa-comment-dots"></i>
    </button>

    <!-- KHUNG CHAT -->
    <div id="chat-widget" class="chat-widget">

        <!-- HEADER -->
        <div class="chat-header">
            <div class="d-flex align-items-center">
                <div class="me-2">
                    <i class="fas fa-robot"></i>
                </div>
                <div>
                    <h6 class="mb-0 fw-bold">Trợ lý ảo BHH</h6>
                    <small class="text-white-50">Hỗ trợ 24/7</small>
                </div>
            </div>
            <span onclick="toggleChat()" style="cursor:pointer;">&times;</span>
        </div>

        <!-- BODY -->
        <div class="chat-body" id="chat-body">

            <div class="message bot-message">
                Xin chào! Tôi là trợ lý ảo của phòng khám. Tôi có thể giúp gì cho bạn? 🏥
            </div>

            <div class="chat-options mt-3">
                <button class="option-btn" onclick="botReply('price')">
                    💰 Bảng giá khám
                </button>
                <button class="option-btn" onclick="botReply('address')">
                    📍 Địa chỉ ở đâu?
                </button>
                <button class="option-btn" onclick="botReply('book')">
                    📅 Đặt lịch thế nào?
                </button>
                <button class="option-btn" onclick="botReply('human')">
                    👨‍⚕️ Gặp tư vấn viên
                </button>
            </div>

        </div>

        <!-- FOOTER -->
        <div class="chat-footer">
            <input
                type="text"
                id="chat-input"
                placeholder="Nhập tin nhắn..."
                onkeypress="handleEnter(event)">
            <button onclick="sendMessage()">
                <i class="fas fa-paper-plane"></i>
            </button>
        </div>

    </div>

    <script src="js/script.js"></script>

</body>

</html>