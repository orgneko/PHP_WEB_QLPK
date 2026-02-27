<?php
session_start();

// 1. KẾT NỐI DATABASE (Sử dụng file config chuẩn)
if (file_exists('../config/config.php')) {
    require_once '../config/config.php';
} else {
    // Dự phòng nếu không tìm thấy config
    $pdo = new PDO("mysql:host=localhost;dbname=phongkham;charset=utf8mb4", 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}

// 2. LẤY DỮ LIỆU ĐỂ HIỂN THỊ LÊN FORM
// Lấy danh sách Bác sĩ (Kèm tên Chuyên khoa)
$doctors = $pdo->query("SELECT d.*, s.name as specialty_name FROM doctors d LEFT JOIN specialties s ON d.specialty_id = s.id")->fetchAll();
// Lấy danh sách Gói khám
$services = $pdo->query("SELECT * FROM services WHERE status = 'active'")->fetchAll();

// Nhận tham số từ trang chủ truyền sang (nếu khách bấm từ thẻ Bác sĩ hoặc Gói khám)
$selected_doctor_id = $_GET['doctor_id'] ?? '';
$selected_service_id = $_GET['service_id'] ?? '';

// 3. XỬ LÝ KHI BỆNH NHÂN BẤM "XÁC NHẬN ĐẶT LỊCH"
$message = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fullname = $_POST['fullname'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $doctor_id = !empty($_POST['doctor_id']) ? $_POST['doctor_id'] : null;
    $date = $_POST['appointment_date'] ?? '';
    $symptom = $_POST['note'] ?? '';
    $promo_code = trim($_POST['promo_code'] ?? '');

    // Lấy thông tin người dùng đang đăng nhập (Nếu có)
    $user_id = $_SESSION['user_id'] ?? null;

    if ($fullname && $phone && $date) {
        // Xử lý logic Mã Giảm Giá (Kiểm tra xem mã có hợp lệ không)
        $discount_amount = 0;
        if (!empty($promo_code)) {
            $stmt_promo = $pdo->prepare("SELECT * FROM promotions WHERE code = ? AND status = 'active' AND (end_date IS NULL OR end_date >= CURDATE())");
            $stmt_promo->execute([$promo_code]);
            $promo = $stmt_promo->fetch();
            if ($promo) {
                // Giả sử lấy giá trị giảm (Tạm thời note vào ghi chú, bạn có thể xử lý trừ tiền sau nếu muốn)
                $symptom .= "\n[Khách dùng mã ưu đãi: " . $promo['code'] . " - Giảm " . $promo['discount_value'] . ($promo['discount_type'] == 'percent' ? '%' : 'đ') . "]";
            } else {
                $message = "<div class='alert alert-danger'><i class='fas fa-exclamation-triangle'></i> Mã ưu đãi không hợp lệ hoặc đã hết hạn!</div>";
                // Bỏ qua quá trình lưu để khách nhập lại
                goto end_booking_process;
            }
        }

        // Tạo mã phiếu khám ngẫu nhiên
        $booking_code = 'BHH-' . strtoupper(substr(uniqid(), -6));

        // Lưu vào bảng BOOKINGS (Đã đổi tên từ orders)
        $sql = "INSERT INTO bookings (booking_code, user_id, fullname, phone_number, doctor_id, appointment_date, note, status, created_at) 
                VALUES (:bcode, :uid, :name, :phone, :doc, :date, :note, 'pending', NOW())";

        $stmt = $pdo->prepare($sql);

        try {
            $result = $stmt->execute([
                ':bcode' => $booking_code,
                ':uid' => $user_id,
                ':name' => $fullname,
                ':phone' => $phone,
                ':doc' => $doctor_id,
                ':date' => $date,
                ':note' => $symptom
            ]);

            if ($result) {
                // Lấy ID của lịch hẹn vừa lưu
                $booking_id = $pdo->lastInsertId();

                // NẾU khách có chọn "Gói Khám", lưu thêm vào bảng booking_details
                $service_id_post = !empty($_POST['service_id']) ? $_POST['service_id'] : null;
                if ($service_id_post) {
                    // Lấy giá của dịch vụ
                    $stmt_price = $pdo->prepare("SELECT price, sale_price FROM services WHERE id = ?");
                    $stmt_price->execute([$service_id_post]);
                    $svc = $stmt_price->fetch();
                    $final_price = ($svc['sale_price'] && $svc['sale_price'] > 0) ? $svc['sale_price'] : $svc['price'];

                    $sql_detail = "INSERT INTO booking_details (booking_id, service_id, quantity, price) VALUES (?, ?, 1, ?)";
                    $pdo->prepare($sql_detail)->execute([$booking_id, $service_id_post, $final_price]);

                    // Cập nhật lại tổng tiền vào bảng bookings
                    $pdo->prepare("UPDATE bookings SET total_amount = ? WHERE id = ?")->execute([$final_price, $booking_id]);
                }

                $message = "<div class='alert alert-success fw-bold p-4 text-center'>
                                <i class='fas fa-check-circle fa-3x mb-3 text-success'></i><br>
                                Đặt lịch thành công!<br>
                                Mã phiếu khám: <span class='text-primary fs-4'>$booking_code</span><br>
                                <small class='text-muted fw-normal'>Chúng tôi sẽ liên hệ với bạn trong vòng 15 phút để xác nhận.</small>
                            </div>";

                // Reset form sau khi thành công
                $_POST = [];
            }
        } catch (PDOException $e) {
            $message = "<div class='alert alert-danger'>❌ Lỗi hệ thống: " . $e->getMessage() . "</div>";
        }
    } else {
        $message = "<div class='alert alert-warning'>⚠️ Vui lòng điền đầy đủ thông tin bắt buộc (*).</div>";
    }
}
end_booking_process:
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt Lịch Khám - Phòng Khám BHH</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #f0f2f5;
        }

        .booking-card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            background: #fff;
        }

        .booking-header {
            background: linear-gradient(135deg, #103095, #0056b3);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }

        .form-control,
        .form-select {
            border-radius: 10px;
            padding: 12px 15px;
            border: 1px solid #ced4da;
            background-color: #f8f9fa;
        }

        .form-control:focus,
        .form-select:focus {
            box-shadow: none;
            border-color: #103095;
            background-color: #fff;
        }

        .btn-submit {
            background: #103095;
            color: white;
            padding: 15px;
            border-radius: 10px;
            font-weight: bold;
            font-size: 1.1rem;
            width: 100%;
            border: none;
            transition: all 0.3s;
        }

        .btn-submit:hover {
            background: #0b226e;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(16, 48, 149, 0.3);
        }
    </style>
</head>

<body>

    <nav class="navbar navbar-expand-lg navbar-dark mb-5 shadow-sm" style="background-color: #103095;">
        <div class="container">
            <a class="navbar-brand fw-bold" href="../index.php">
                <i class="fas fa-clinic-medical me-2"></i> PHÒNG KHÁM BHH
            </a>
            <div class="ms-auto text-white fw-bold">
                <i class="fas fa-phone-alt me-2"></i> Hotline: 1900 6996
            </div>
        </div>
    </nav>

    <div class="container mb-5">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-10">
                <?= $message ?>

                <?php if (!isset($result) || !$result): // Ẩn form nếu đã đặt lịch thành công 
                ?>
                    <div class="card booking-card">
                        <div class="booking-header position-relative">
                            <h2 class="fw-bold mb-2"><i class="far fa-calendar-check me-2"></i> PHIẾU ĐĂNG KÝ KHÁM BỆNH</h2>
                            <p class="mb-0 text-white-50">Tiết kiệm thời gian, không cần chờ đợi</p>
                        </div>

                        <div class="card-body p-4 p-md-5">
                            <form action="" method="POST">

                                <h5 class="text-primary fw-bold mb-3 border-bottom pb-2">1. Thông tin Bệnh nhân</h5>
                                <div class="row mb-4">
                                    <div class="col-md-6 mb-3 mb-md-0">
                                        <label class="form-label fw-bold">Họ và tên <span class="text-danger">*</span></label>
                                        <input type="text" name="fullname" class="form-control" placeholder="VD: Nguyễn Văn A" required value="<?= $_POST['fullname'] ?? '' ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Số điện thoại liên hệ <span class="text-danger">*</span></label>
                                        <input type="tel" name="phone" class="form-control" placeholder="Để phòng khám xác nhận lịch" required value="<?= $_POST['phone'] ?? '' ?>">
                                    </div>
                                </div>

                                <h5 class="text-primary fw-bold mb-3 border-bottom pb-2">2. Thông tin Dịch vụ Y tế</h5>
                                <div class="row mb-4">
                                    <div class="col-md-12 mb-3">
                                        <label class="form-label fw-bold">Chọn Gói Khám (Tùy chọn)</label>
                                        <select name="service_id" class="form-select">
                                            <option value="">-- Khám chuyên khoa thông thường --</option>
                                            <?php foreach ($services as $sv): ?>
                                                <option value="<?= $sv['id'] ?>" <?= ($sv['id'] == $selected_service_id) ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($sv['name']) ?>
                                                    (Giá: <?= number_format($sv['sale_price'] ?: $sv['price'], 0, ',', '.') ?>đ)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">Chọn Bác sĩ (Tùy chọn)</label>
                                        <select name="doctor_id" class="form-select">
                                            <option value="">-- Phòng khám tự sắp xếp --</option>
                                            <?php foreach ($doctors as $doc): ?>
                                                <option value="<?= $doc['id'] ?>" <?= ($doc['id'] == $selected_doctor_id) ? 'selected' : '' ?>>
                                                    Bs. <?= htmlspecialchars($doc['name']) ?> (<?= htmlspecialchars($doc['specialty_name']) ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">Ngày giờ muốn khám <span class="text-danger">*</span></label>
                                        <input type="datetime-local" name="appointment_date" class="form-control" required value="<?= $_POST['appointment_date'] ?? '' ?>">
                                    </div>
                                </div>

                                <h5 class="text-primary fw-bold mb-3 border-bottom pb-2">3. Triệu chứng & Khuyến mãi</h5>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Mô tả triệu chứng bệnh</label>
                                    <textarea name="note" class="form-control" rows="3" placeholder="Ví dụ: Bị ho khan và sốt nhẹ 2 ngày nay..."><?= $_POST['note'] ?? '' ?></textarea>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label fw-bold">Mã ưu đãi (Nếu có)</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white border-end-0 text-success"><i class="fas fa-ticket-alt"></i></span>
                                        <input type="text" name="promo_code" class="form-control border-start-0" placeholder="Nhập mã giảm giá..." value="<?= htmlspecialchars($promo_code ?? '') ?>">
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-submit shadow"><i class="fas fa-paper-plane me-2"></i> GỬI YÊU CẦU ĐẶT LỊCH</button>
                            </form>
                        </div>

                        <div class="card-footer text-center bg-light py-3 border-top-0">
                            <a href="../index.php" class="text-decoration-none text-muted fw-bold">
                                <i class="fas fa-arrow-left me-1"></i> Trở về Trang chủ
                            </a>
                        </div>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>