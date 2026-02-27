<?php
session_start();
require_once '../config/config.php';

$error = '';

// Hàm hỗ trợ dự phòng trường hợp file config.php của bạn chưa định nghĩa
if (!function_exists('sanitizeInput')) {
    function sanitizeInput($data)
    {
        return htmlspecialchars(stripslashes(trim($data)));
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizeInput($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $error = 'Vui lòng nhập đầy đủ thông tin!';
    } else {
        // Cho phép đăng nhập bằng cả username hoặc email
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Logic xử lý mật khẩu thông minh của bạn (Giữ nguyên)
            $is_valid_password = false;
            if (substr($user['password'], 0, 4) === '$2y$' && password_verify($password, $user['password'])) {
                $is_valid_password = true;
            } elseif ($password === $user['password']) {
                $is_valid_password = true;
            }

            if ($is_valid_password) {
                // Lưu session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['role'] = $user['role'];

                // Chuyển hướng theo role
                if ($user['role'] === 'admin') {
                    header('Location: ../admin/index.php');
                } else {
                    header('Location: ../index.php');
                }
                exit;
            } else {
                $error = 'Tên đăng nhập hoặc mật khẩu không chính xác!';
            }
        } else {
            $error = 'Tài khoản không tồn tại!';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - Phòng Khám BHH</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            /* Đổi sang màu xanh đặc trưng của Y tế */
            background: linear-gradient(135deg, #f0f2f5 0%, #d9e2ec 100%);
        }

        .login-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(16, 48, 149, 0.1);
            overflow: hidden;
            border: 1px solid #e2e8f0;
        }

        .login-header {
            /* Tông màu xanh BHH Clinic */
            background: linear-gradient(135deg, #103095 0%, #0056b3 100%);
            color: white;
            padding: 35px 30px;
            text-align: center;
        }

        .login-body {
            padding: 40px;
        }

        .form-control {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            padding: 12px 15px;
            background-color: #f8f9fa;
        }

        .form-control:focus {
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

        .btn-login {
            background: #103095;
            color: white;
            border: none;
            border-radius: 10px;
            padding: 12px 30px;
            font-weight: bold;
            font-size: 1.1rem;
            transition: all 0.3s ease;
        }

        .btn-login:hover {
            background: #0b226e;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(16, 48, 149, 0.3);
            color: white;
        }
    </style>
</head>

<body>
    <div class="login-container">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6 col-lg-5">
                    <div class="login-card">
                        <div class="login-header">
                            <h3 class="fw-bold"><i class="fas fa-clinic-medical me-2"></i> Phòng Khám BHH</h3>
                            <p class="mb-0 text-white-50">Đăng nhập hệ thống y tế</p>
                        </div>

                        <div class="login-body">
                            <?php if ($error): ?>
                                <div class="alert alert-danger fw-bold border-0 shadow-sm" role="alert">
                                    <i class="fas fa-exclamation-triangle me-2"></i> <?= $error ?>
                                </div>
                            <?php endif; ?>

                            <form method="POST">
                                <div class="mb-3">
                                    <label for="username" class="form-label fw-bold text-dark">Tên đăng nhập hoặc Email</label>
                                    <div class="input-group">
                                        <span class="input-group-text text-muted"><i class="fas fa-user"></i></span>
                                        <input type="text" class="form-control" id="username" name="username"
                                            value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>"
                                            placeholder="Nhập tài khoản của bạn" required>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label for="password" class="form-label fw-bold text-dark">Mật khẩu</label>
                                    <div class="input-group">
                                        <span class="input-group-text text-muted"><i class="fas fa-lock"></i></span>
                                        <input type="password" class="form-control" id="password" name="password" placeholder="Nhập mật khẩu" required>
                                    </div>
                                </div>

                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-login shadow-sm">
                                        ĐĂNG NHẬP <i class="fas fa-sign-in-alt ms-1"></i>
                                    </button>
                                </div>

                                <div class="text-center mt-3">
                                    <a href="#" class="text-decoration-none text-muted small">Quên mật khẩu?</a>
                                </div>
                            </form>

                            <div class="text-center mt-4 pt-3 border-top">
                                <p class="mb-0 text-muted">Chưa có hồ sơ bệnh án?
                                    <a href="register.php" class="text-primary fw-bold text-decoration-none">Tạo hồ sơ ngay</a>
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
</body>

</html>