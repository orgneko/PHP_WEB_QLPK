<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Tin tức & Khuyến mãi - SportWear Shop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #f6f8fa; font-family: 'Roboto', Arial, sans-serif; }
        .news-section { margin: 40px auto; max-width: 1100px; }
        .news-title { font-size: 2rem; font-weight: bold; color: #232323; margin-bottom: 24px; text-align: center; }
        .news-card { background: #fff; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); margin-bottom: 28px; display: flex; overflow: hidden; }
        .news-img { width: 180px; height: 130px; object-fit: cover; border-radius: 12px 0 0 12px; }
        .news-content { padding: 18px 22px; flex: 1; }
        .news-content h5 { font-size: 1.1rem; font-weight: bold; margin-bottom: 8px; }
        .news-content .desc { color: #444; font-size: 15px; }
        @media (max-width: 700px) {
            .news-card { flex-direction: column; }
            .news-img { width: 100%; height: 180px; border-radius: 12px 12px 0 0; }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark" style="background: linear-gradient(90deg, #007bff 0%, #43cea2 100%);">
        <div class="container">
            <a class="navbar-brand" href="index.php" style="font-weight: bold; font-size: 28px; color: #ffe600;">SportWear Shop</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link text-white" href="index.php">Trang chủ</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="products.php">Dịch vụ mới</a></li>
                    <li class="nav-item"><a class="nav-link text-warning" href="news.php">Tin tức và khuyến mãi</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="contact.php">Liên hệ</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="news-section">
        <div class="news-title">Tin tức &amp; Khuyến mãi</div>
        <!-- Tin tức 1 -->
        <div class="news-card">
            <img class="news-img" src="https://bizweb.dktcdn.net/100/340/361/files/xx-07445.jpg?v=1743673474622" alt="Tin tức mới">
            <div class="news-content">
                <h5> UPDATE VỢT PICKLEBALL &amp; CẦU LÔNG - BỘ ĐÔI VỢT THỂ THAO ĐƯỢC MONG ĐỢI</h5>
                <div class="desc">Tin vui dành cho chính thức lên kệ các Dịch vụ vợt thể thao Pickleball và Cầu lông...</div>
            </div>
        </div>
        <!-- Tin tức 2 -->
        <div class="news-card">
            <img class="news-img" src="https://bizweb.dktcdn.net/100/340/361/articles/vn-11134208-7r98o-lwomew7p8us94d.jpg?v=1742544992490" alt="1990s sneaker">
            <div class="news-content">
                <h5>“REPLY 1990s” CÙNG XU HƯỚNG SNEAKER DỄ MÓNG</h5>
                <div class="desc">Quay vòng thời trang, giày thể thao đế mỏng đã trở thành xu hướng lớn trong thị trường thời trang...</div>
            </div>
        </div>
        <!-- Khuyến mãi 1 -->
        <div class="news-card">
            <img class="news-img" src="https://bizweb.dktcdn.net/100/340/361/files/xx-07445.jpg?v=1743673474622" alt="Khuyến mại 800k">
            <div class="news-content">
                <h5>MUA CÀNG NHIỀU HOÀN CÀNG LỚN TỚI 800K</h5>
                <div class="desc">&#128640; Hoàn ngay 500.000vnđ khi mua sắm với hoá đơn từ 2.500.000 - 3.500.000vnđ &#128640; Hoàn ngay 800.000 khi mua sắm với hoá...</div>
            </div>
        </div>
        <!-- Khuyến mãi 2 -->
        <div class="news-card">
            <img class="news-img" src="https://bizweb.dktcdn.net/100/340/361/files/xx-07445.jpg?v=1743673474622" alt="1050 sale">
            <div class="news-content">
                <h5>🎉🎉🎉 TƯNG BỪNG KHAI TRƯƠNG </h5>
                <div class="desc">Dành riêng cho fan hàng hiệu ưu đãi độc quyền 🔥 SALE UP TO 50% tất cả các thương hiệu adidas,...</div>
            </div>
        </div>
    </div>
</body>
</html>