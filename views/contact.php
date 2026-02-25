<?php
session_start();
require_once 'config.php';

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $subject = trim($_POST['subject']);
    $message_text = trim($_POST['content']);
    
    // Lưu feedback vào database
    $stmt = $pdo->prepare("INSERT INTO feedback (name, email, subject, message, created_at) VALUES (?, ?, ?, ?, NOW())");
    if ($stmt->execute([$name, $email, $subject, $message_text])) {
        $message = '<div class="alert alert-success">Cảm ơn bạn đã gửi phản hồi!</div>';
    } else {
        $message = '<div class="alert alert-danger">Có lỗi xảy ra, vui lòng thử lại!</div>';
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Liên hệ - SportShop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .contact-info {
            background: #f8f9fa;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        .contact-info i {
            color: #2ecc71;
            font-size: 24px;
            margin-right: 10px;
        }
        .contact-form {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .map-container {
            height: 400px;
            margin-top: 30px;
        }
        .contact-item {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
<div class="container my-5">
    <h2 class="text-center mb-4">Liên hệ với chúng tôi</h2>
    
    <?php if ($message) echo $message; ?>
    
    <div class="row">
        <div class="col-md-4">
            <div class="contact-info">
                <h4 class="mb-4">Thông tin liên hệ</h4>
                
                <div class="contact-item">
                    <i class="fas fa-map-marker-alt"></i>
                    <span>1236, Ba Chấm, Thanh Hóa</span>
                </div>
                
                <div class="contact-item">
                    <i class="fas fa-phone"></i>
                    <span>096996969</span>
                </div>
                
                <div class="contact-item">
                    <i class="fas fa-envelope"></i>
                    <span>contact@sportshop.com</span>
                </div>
                
                <div class="contact-item">
                    <i class="fas fa-clock"></i>
                    <span>8:00 - 22:00 (Thứ 2 - Chủ nhật)</span>
                </div>
            </div>
            
            <div class="social-links mt-4">
                <h4 class="mb-3">Kết nối với chúng tôi</h4>
                <a href="#" class="btn btn-outline-primary me-2"><i class="fab fa-facebook"></i></a>
                <a href="#" class="btn btn-outline-info me-2"><i class="fab fa-twitter"></i></a>
                <a href="#" class="btn btn-outline-danger"><i class="fab fa-instagram"></i></a>
            </div>
        </div>
        
        <div class="col-md-8">
            <div class="contact-form">
                <h4 class="mb-4">Gửi phản hồi</h4>
                <form method="post">
                    <div class="mb-3">
                        <label class="form-label">Họ tên</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Tiêu đề</label>
                        <input type="text" name="subject" class="form-control" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Nội dung</label>
                        <textarea name="content" class="form-control" rows="5" required></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Gửi phản hồi</button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="map-container">
        <iframe 
            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3919.2598834720203!2d106.69841031533417!3d10.789472261898276!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zMTDCsDQ3JzIyLjEiTiAxMDbCsDQyJzAwLjgiRQ!5e0!3m2!1svi!2s!4v1629789456797!5m2!1svi!2s"
            width="100%" 
            height="100%" 
            style="border:0;" 
            allowfullscreen="" 
            loading="lazy">
        </iframe>
    </div>
</div>

<!-- Thêm nút trở về trang chủ -->
<div class="container mb-5">
    <a href="index.php" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Về trang chủ
    </a>
</div>
</body>
</html>