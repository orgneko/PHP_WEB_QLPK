<?php
session_start();

// --- 1. KẾT NỐI DATABASE (Đã sửa lại đường dẫn chuẩn cho thư mục gốc) ---
if (file_exists('config/config.php')) {
    require_once 'config/config.php';
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

// --- 2. LẤY DỮ LIỆU TỪ DATABASE CHUẨN Y TẾ ---

// A. Lấy 4 Gói khám đang Active
$stmt_services = $pdo->query("SELECT * FROM services WHERE status = 'active' ORDER BY created_at DESC LIMIT 4");
$services = $stmt_services->fetchAll();

// B. Lấy 4 Bác sĩ (Dùng JOIN để lấy tên Chuyên khoa thay vì gọi cột address đã xóa)
$stmt_doctors = $pdo->query("
    SELECT d.*, s.name as specialty_name 
    FROM doctors d 
    LEFT JOIN specialties s ON d.specialty_id = s.id 
    ORDER BY d.id ASC LIMIT 4
");
$doctors = $stmt_doctors->fetchAll();

// C. Hàm hỗ trợ
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
    <style>
        html {
            scroll-behavior: smooth;
        }

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
                                <i class="fas fa-user me-1"></i> Hồ sơ của tôi
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="#">Lịch sử khám bệnh</a></li>
                                <?php if (isAdmin()): ?>
                                    <li><a class="dropdown-item text-danger fw-bold" href="admin/index.php">Vào trang Quản trị</a></li>
                                <?php endif; ?>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item" href="logout.php">Đăng xuất</a></li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <a href="views/login.php" class="btn btn-outline-primary rounded-pill px-3 me-2">Đăng nhập</a>
                        <a href="views/register.php" class="btn btn-primary rounded-pill px-3">Đăng ký</a>
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
                            <a href="views/booking.php" class="btn btn-warning py-md-3 px-md-5 me-3 rounded-pill fw-bold text-dark shadow">
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
                    <div class="border rounded p-4 h-100 text-center shadow-sm">
                        <div class="btn-square bg-light rounded-circle mx-auto mb-4" style="width: 80px; height: 80px; display:flex; align-items:center; justify-content:center;">
                            <i class="fa fa-user-md fa-3x text-primary"></i>
                        </div>
                        <h5 class="mb-3 fw-bold">Bác sĩ Đầu ngành</h5>
                        <p class="text-muted">Đội ngũ giáo sư, tiến sĩ, bác sĩ giàu kinh nghiệm từ các bệnh viện tuyến trung ương.</p>
                    </div>
                </div>
                <div class="col-lg-4 wow fadeIn">
                    <div class="border rounded p-4 h-100 text-center shadow-sm">
                        <div class="btn-square bg-light rounded-circle mx-auto mb-4" style="width: 80px; height: 80px; display:flex; align-items:center; justify-content:center;">
                            <i class="fa fa-stethoscope fa-3x text-primary"></i>
                        </div>
                        <h5 class="mb-3 fw-bold">Trang thiết bị hiện đại</h5>
                        <p class="text-muted">Hệ thống máy móc nội soi, siêu âm, xét nghiệm được nhập khẩu trực tiếp từ Châu Âu.</p>
                    </div>
                </div>
                <div class="col-lg-4 wow fadeIn">
                    <div class="border rounded p-4 h-100 text-center shadow-sm">
                        <div class="btn-square bg-light rounded-circle mx-auto mb-4" style="width: 80px; height: 80px; display:flex; align-items:center; justify-content:center;">
                            <i class="fa fa-clock fa-3x text-primary"></i>
                        </div>
                        <h5 class="mb-3 fw-bold">Không chờ đợi</h5>
                        <p class="text-muted">Chủ động thời gian, đến là khám ngay với hệ thống đặt lịch thông minh 24/7.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container-xxl py-5 bg-light" id="services">
        <div class="container">
            <div class="text-center mx-auto mb-5" style="max-width: 600px;">
                <p class="d-inline-block border rounded-pill py-1 px-4 text-primary bg-white fw-bold shadow-sm">Dịch vụ y tế</p>
                <h1 style="color: #103095;" class="fw-bold mt-2">Các Gói Khám Nổi Bật</h1>
            </div>

            <div class="row g-4">
                <?php if (count($services) > 0): ?>
                    <?php foreach ($services as $sv): ?>
                        <div class="col-lg-3 col-md-6">
                            <div class="service-item bg-white rounded h-100 p-4 border text-center position-relative">
                                <div class="d-inline-block rounded-circle bg-light p-2 mb-3">
                                    <img class="rounded-circle shadow-sm" src="<?= htmlspecialchars($sv['image_url'] ?: 'https://via.placeholder.com/100?text=Clinic') ?>" style="width: 90px; height: 90px; object-fit: cover;" alt="">
                                </div>
                                <h5 class="mb-3 fw-bold text-dark"><?= htmlspecialchars($sv['name']) ?></h5>
                                <?php if (!empty($sv['sale_price']) && $sv['sale_price'] < $sv['price']): ?>
                                    <p class="text-danger fw-bold fs-5 mb-0"><?= number_format($sv['sale_price'], 0, ',', '.') ?> đ</p>
                                    <p class="text-muted small text-decoration-line-through"><?= number_format($sv['price'], 0, ',', '.') ?> đ</p>
                                <?php else: ?>
                                    <p class="text-primary fw-bold fs-5"><?= number_format($sv['price'], 0, ',', '.') ?> đ</p>
                                <?php endif; ?>
                                <p class="text-muted small mb-4"><?= mb_strimwidth($sv['description'] ?? '', 0, 60, "...") ?></p>
                                <a class="btn btn-outline-primary rounded-pill px-4" href="views/booking.php?service_id=<?= $sv['id'] ?>">
                                    Đặt lịch ngay
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12 text-center text-muted">Đang cập nhật danh sách gói khám...</div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="container-xxl py-5" id="doctors">
        <div class="container">
            <div class="text-center mx-auto mb-5">
                <h1 style="color: #103095;" class="fw-bold">Đội Ngũ Chuyên Gia</h1>
                <p class="text-muted">Các bác sĩ giàu kinh nghiệm luôn sẵn sàng hỗ trợ bạn</p>
            </div>

            <div class="row g-4">
                <?php if (count($doctors) > 0): ?>
                    <?php foreach ($doctors as $doc): ?>
                        <div class="col-lg-3 col-md-6">
                            <div class="doctor-card bg-white rounded overflow-hidden shadow-sm text-center border h-100 d-flex flex-column">
                                <div class="p-4 flex-grow-1">
                                    <img class="img-fluid rounded-circle mb-3 border border-3 border-light shadow-sm" src="<?= htmlspecialchars($doc['image'] ?: 'https://via.placeholder.com/150?text=Doctor') ?>" alt="" style="width: 130px; height: 130px; object-fit: cover;">
                                    <h5 class="fw-bold mb-2 text-dark"><?= htmlspecialchars($doc['name']) ?></h5>
                                    <span class="badge bg-info text-dark mb-2 px-3 py-2 rounded-pill">
                                        <?= htmlspecialchars($doc['specialty_name'] ?? 'Chuyên khoa Nội') ?>
                                    </span>
                                    <p class="text-muted small mt-2"><?= mb_strimwidth($doc['bio'] ?? '', 0, 70, "...") ?></p>
                                </div>
                                <div class="d-grid p-3 bg-light border-top mt-auto">
                                    <a class="btn btn-primary btn-sm rounded-pill fw-bold" href="views/booking.php?doctor_id=<?= $doc['id'] ?>">Đặt lịch với Bác sĩ</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12 text-center text-muted">Đang cập nhật danh sách bác sĩ...</div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="container-fluid bg-dark text-light footer mt-5 pt-5" id="footer">
        <div class="container py-5">
            <div class="row g-5">
                <div class="col-lg-4 col-md-6">
                    <h5 class="text-white mb-4 fw-bold"><i class="fas fa-clinic-medical me-2"></i>Phòng Khám BHH</h5>
                    <p class="mb-2"><i class="fa fa-map-marker-alt me-3"></i>Số 10, Đường Y Tế, TP. Hà Nội</p>
                    <p class="mb-2"><i class="fa fa-phone-alt me-3"></i>1900 6996</p>
                    <p class="mb-2"><i class="fa fa-envelope me-3"></i>cskh@bhhclinic.com</p>
                </div>
                <div class="col-lg-4 col-md-6">
                    <h5 class="text-white mb-4 fw-bold">Liên kết nhanh</h5>
                    <a class="btn btn-link text-white-50 text-decoration-none" href="#services">Gói khám sức khỏe</a>
                    <a class="btn btn-link text-white-50 text-decoration-none" href="#doctors">Đội ngũ bác sĩ</a>
                    <a class="btn btn-link text-white-50 text-decoration-none" href="views/booking.php">Đặt lịch hẹn</a>
                    <a class="btn btn-link text-white-50 text-decoration-none" href="#">Chính sách bảo mật</a>
                </div>
                <div class="col-lg-4 col-md-6">
                    <h5 class="text-white mb-4 fw-bold">Giờ làm việc</h5>
                    <p class="mb-1">Thứ 2 - Thứ 6: 07:30 - 17:00</p>
                    <p class="mb-1">Thứ 7: 07:30 - 12:00</p>
                    <p class="text-warning fw-bold mb-0">Cấp cứu trực 24/7</p>
                </div>
            </div>
        </div>
        <div class="container">
            <div class="copyright text-center py-4 border-top border-secondary text-white-50">
                <p class="mb-0">&copy; 2026 <strong>BHH Clinic</strong>. All Rights Reserved.</p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>