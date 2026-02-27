<?php
session_start();
require_once '../config/config.php';

$error = '';
$success = '';

// Hàm hỗ trợ dự phòng trường hợp file config.php của bạn chưa định nghĩa
if (!function_exists('sanitizeInput')) {
    function sanitizeInput($data)
    {
        return htmlspecialchars(stripslashes(trim($data)));
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizeInput($_POST['username']);
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $full_name = sanitizeInput($_POST['full_name']);
    $phone = sanitizeInput($_POST['phone']);
    $address = sanitizeInput($_POST['address']);
    // Nhận thêm 2 biến mới
    $gender = $_POST['gender'] ?? null;
    $date_of_birth = $_POST['date_of_birth'] ?? null;

    // Validate input
    if (empty($username) || empty($email) || empty($password) || empty($full_name)) {
        $error = 'Vui lòng nhập đầy đủ thông tin bắt buộc!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Email không hợp lệ!';
    } elseif (strlen($password) < 6) {
        $error = 'Mật khẩu phải có ít nhất 6 ký tự!';
    } elseif ($password !== $confirm_password) {
        $error = 'Mật khẩu xác nhận không khớp!';
    } else {
        // Check if username or email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);

        if ($stmt->rowCount() > 0) {
            $error = 'Tên đăng nhập hoặc email đã tồn tại!';
        } else {
            // Create new user (Bệnh nhân)
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            // Thêm role = 'patient' và các cột mới vào câu lệnh INSERT
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, full_name, phone, address, gender, date_of_birth, role) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'patient')");

            try {
                if ($stmt->execute([$username, $email, $hashed_password, $full_name, $phone, $address, $gender, $date_of_birth])) {
                    $success = 'Tạo hồ sơ bệnh nhân thành công! Vui lòng đăng nhập.';
                } else {
                    $error = 'Có lỗi xảy ra khi tạo hồ sơ!';
                }
            } catch (PDOException $e) {
                $error = 'Lỗi hệ thống: ' . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tạo Hồ Sơ Y Tế - Phòng Khám BHH</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .register-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            /* Đổi sang màu nền sáng, sạch sẽ của Y tế */
            background: linear-gradient(135deg, #f0f2f5 0%, #d9e2ec 100%);
            padding: 40px 0;
        }

        .register-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(16, 48, 149, 0.1);
            overflow: hidden;
            border: 1px solid #e2e8f0;
        }

        .register-header {
            /* Tông màu xanh BHH Clinic */
            background: linear-gradient(135deg, #103095 0%, #0056b3 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .register-body {
            padding: 40px;
        }

        .form-control,
        .form-select {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            padding: 12px 15px;
            background-color: #f8f9fa;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #103095;
            box-shadow: 0 0 0 0.2rem rgba(16, 48, 149, 0.15);
            background-color: #fff;
        }

        .input-group-text {
            background-color: #f8f9fa;
            border: 2px solid #e9ecef;
            border-right: none;
        }

        .form-control {
            border-left: none;
        }

        .btn-register {
            background: #103095;
            color: white;
            border: none;
            border-radius: 10px;
            padding: 12px 30px;
            font-weight: bold;
            font-size: 1.1rem;
            transition: all 0.3s ease;
        }

        .btn-register:hover {
            background: #0b226e;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(16, 48, 149, 0.3);
            color: white;
        }

        .required {
            color: #dc3545;
        }
    </style>
</head>

<body>
    <div class="register-container">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-10 col-lg-8">
                    <div class="register-card">
                        <div class="register-header">
                            <h3><i class="fas fa-notes-medical me-2"></i> Phòng Khám BHH</h3>
                            <p class="mb-0 text-white-50">Tạo hồ sơ bệnh nhân trực tuyến</p>
                        </div>

                        <div class="register-body">
                            <?php if ($error): ?>
                                <div class="alert alert-danger fw-bold border-0 shadow-sm" role="alert">
                                    <i class="fas fa-exclamation-triangle me-2"></i> <?= $error ?>
                                </div>
                            <?php endif; ?>

                            <?php if ($success): ?>
                                <div class="alert alert-success fw-bold border-0 shadow-sm" role="alert">
                                    <i class="fas fa-check-circle me-2"></i> <?= $success ?>
                                    <br><a href="login.php" class="alert-link mt-2 d-inline-block">Bấm vào đây để Đăng nhập</a>
                                </div>
                            <?php endif; ?>

                            <form method="POST">
                                <h5 class="text-primary border-bottom pb-2 mb-3"><i class="fas fa-user-lock me-2"></i>Thông tin tài khoản</h5>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="username" class="form-label fw-bold text-dark">
                                                Tên đăng nhập <span class="required">*</span>
                                            </label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fas fa-user text-muted"></i></span>
                                                <input type="text" class="form-control" id="username" name="username"
                                                    value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>"
                                                    required>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="email" class="form-label fw-bold text-dark">
                                                Email <span class="required">*</span>
                                            </label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fas fa-envelope text-muted"></i></span>
                                                <input type="email" class="form-control" id="email" name="email"
                                                    value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>"
                                                    required>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="password" class="form-label fw-bold text-dark">
                                                Mật khẩu <span class="required">*</span>
                                            </label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fas fa-lock text-muted"></i></span>
                                                <input type="password" class="form-control" id="password" name="password" required>
                                            </div>
                                            <div class="form-text">Ít nhất 6 ký tự</div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="confirm_password" class="form-label fw-bold text-dark">
                                                Xác nhận mật khẩu <span class="required">*</span>
                                            </label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fas fa-lock text-muted"></i></span>
                                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <h5 class="text-primary border-bottom pb-2 mb-3 mt-4"><i class="fas fa-id-card-alt me-2"></i>Thông tin bệnh nhân</h5>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="full_name" class="form-label fw-bold text-dark">
                                                Họ và tên <span class="required">*</span>
                                            </label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fas fa-id-card text-muted"></i></span>
                                                <input type="text" class="form-control" id="full_name" name="full_name"
                                                    value="<?= isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : '' ?>"
                                                    required>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="phone" class="form-label fw-bold text-dark">Số điện thoại</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fas fa-phone text-muted"></i></span>
                                                <input type="tel" class="form-control" id="phone" name="phone"
                                                    value="<?= isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : '' ?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label fw-bold text-dark">Giới tính</label>
                                            <select name="gender" class="form-select">
                                                <option value="Nam" <?= (isset($_POST['gender']) && $_POST['gender'] == 'Nam') ? 'selected' : '' ?>>Nam</option>
                                                <option value="Nữ" <?= (isset($_POST['gender']) && $_POST['gender'] == 'Nữ') ? 'selected' : '' ?>>Nữ</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label fw-bold text-dark">Ngày sinh</label>
                                            <input type="date" name="date_of_birth" class="form-control" style="border-left: 2px solid #e9ecef;" value="<?= $_POST['date_of_birth'] ?? '' ?>">
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label for="address" class="form-label fw-bold text-dark">Địa chỉ thường trú</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-map-marker-alt text-muted"></i></span>
                                        <textarea class="form-control" id="address" name="address" rows="2" placeholder="Số nhà, Tỉnh/Thành phố..."><?= isset($_POST['address']) ? htmlspecialchars($_POST['address']) : '' ?></textarea>
                                    </div>
                                </div>

                                <div class="d-grid gap-2 mt-4">
                                    <button type="submit" class="btn btn-register shadow-sm py-3">
                                        TẠO HỒ SƠ Y TẾ <i class="fas fa-arrow-right ms-2"></i>
                                    </button>
                                </div>
                            </form>

                            <div class="text-center mt-4 pt-3 border-top">
                                <p class="mb-0 text-muted">Đã có hồ sơ bệnh án?
                                    <a href="login.php" class="text-primary fw-bold text-decoration-none">Đăng nhập ngay</a>
                                </p>
                                <p class="mt-3">
                                    <a href="../index.php" class="text-muted text-decoration-none fw-bold">
                                        <i class="fas fa-arrow-left me-1"></i> Quay lại trang chủ
                                    </a>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Password confirmation validation (Giữ nguyên của bạn)
        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;

            if (password !== confirmPassword) {
                this.setCustomValidity('Mật khẩu xác nhận không khớp');
            } else {
                this.setCustomValidity('');
            }
        });
    </script>
</body>

</html>