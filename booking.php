<?php
session_start();
// KẾT NỐI DATABASE (Thay đổi thông tin nếu cần)
$host = 'localhost';
$db   = 'phongkham'; // Tên database của bạn
$user = 'root';
$pass = '';      // Mật khẩu (thường là rỗng trên XAMPP)
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
try {
    $pdo = new PDO($dsn, $user, $pass);
} catch (\PDOException $e) {
    die("Lỗi kết nối DB: " . $e->getMessage());
}

// 1. LẤY DANH SÁCH BÁC SĨ (Để hiện vào ô chọn)
$stmt = $pdo->query("SELECT * FROM suppliers");
$doctors = $stmt->fetchAll();

// 2. KIỂM TRA BÁC SĨ ĐƯỢC CHỌN TỪ TRANG CHỦ
$selected_doctor_id = isset($_GET['doctor_id']) ? $_GET['doctor_id'] : '';

// 3. XỬ LÝ KHI NGƯỜI DÙNG BẤM "ĐẶT LỊCH"
$message = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fullname = $_POST['fullname'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $doctor_id = $_POST['doctor_id'] ?? null;
    $date = $_POST['appointment_date'] ?? '';
    $symptom = $_POST['note'] ?? '';

    // Validate đơn giản
    if ($fullname && $phone && $date) {
        // Lưu vào bảng ORDERS
        $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : NULL;

        // --- SỬA LỖI: Tự động sinh mã đặt lịch ngẫu nhiên ---
        $order_number = 'BHH-' . strtoupper(uniqid());
        // ---------------------------------------------------

        // Thêm order_number vào câu lệnh SQL
        $sql = "INSERT INTO orders (order_number, user_id, fullname, phone_number, doctor_id, appointment_date, note, status, created_at) 
                VALUES (:ordernum, :uid, :name, :phone, :doc, :date, :note, 'pending', NOW())";

        $stmt = $pdo->prepare($sql);

        try {
            $result = $stmt->execute([
                ':ordernum' => $order_number, // Gửi mã vừa sinh vào đây
                ':uid' => $user_id,
                ':name' => $fullname,
                ':phone' => $phone,
                ':doc' => $doctor_id,
                ':date' => $date,
                ':note' => $symptom
            ]);

            if ($result) {
                $message = "<div class='alert alert-success'>✅ Đặt lịch thành công! Mã phiếu: <b>$order_number</b>. Chúng tôi sẽ liên hệ sớm.</div>";
            }
        } catch (PDOException $e) {
            $message = "<div class='alert alert-danger'>❌ Lỗi hệ thống: " . $e->getMessage() . "</div>";
        }
    } else {
        $message = "<div class='alert alert-warning'>⚠️ Vui lòng điền đầy đủ thông tin.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt Lịch Khám - Phòng Khám BHH</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }

        .booking-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .booking-header {
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .form-control {
            border-radius: 8px;
            padding: 10px 15px;
        }

        .btn-submit {
            background: #007bff;
            color: white;
            padding: 12px;
            border-radius: 8px;
            font-weight: bold;
            width: 100%;
            border: none;
        }

        .btn-submit:hover {
            background: #0056b3;
        }
    </style>
</head>

<body>

    <nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
        <div class="container">
            <a class="navbar-brand" href="index.php"><i class="fas fa-hospital-alt"></i> PHÒNG KHÁM BHH</a>
        </div>
    </nav>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <?= $message ?>

                <div class="card booking-card">
                    <div class="booking-header">
                        <h3><i class="far fa-calendar-check"></i> ĐĂNG KÝ KHÁM BỆNH</h3>
                        <p class="mb-0">Vui lòng điền thông tin để đặt hẹn với bác sĩ</p>
                    </div>
                    <div class="card-body p-4">
                        <form action="" method="POST">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label>Họ và tên bệnh nhân <span class="text-danger">*</span></label>
                                    <input type="text" name="fullname" class="form-control" placeholder="Nguyễn Văn A" required>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label>Số điện thoại <span class="text-danger">*</span></label>
                                    <input type="tel" name="phone" class="form-control" placeholder="0xxxxxxxx" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label>Chọn Bác sĩ chuyên khoa</label>
                                <select name="doctor_id" class="form-control">
                                    <option value="">-- Chọn bác sĩ --</option>
                                    <?php foreach ($doctors as $doc): ?>
                                        <option value="<?= $doc['id'] ?>" <?= ($doc['id'] == $selected_doctor_id) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($doc['name']) ?> - <?= htmlspecialchars($doc['address']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label>Ngày giờ muốn khám <span class="text-danger">*</span></label>
                                <input type="datetime-local" name="appointment_date" class="form-control" required>
                            </div>

                            <div class="mb-4">
                                <label>Triệu chứng / Ghi chú</label>
                                <textarea name="note" class="form-control" rows="3" placeholder="Ví dụ: Đau đầu, sốt cao 2 ngày nay..."></textarea>
                            </div>

                            <button type="submit" class="btn btn-submit">XÁC NHẬN ĐẶT LỊCH</button>
                        </form>
                    </div>
                    <div class="card-footer text-center bg-light">
                        <small class="text-muted">Đội ngũ tư vấn sẽ gọi lại xác nhận sau 15 phút.</small>
                        <br>
                        <a href="index.php" class="text-decoration-none">← Quay lại trang chủ</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>

</html>