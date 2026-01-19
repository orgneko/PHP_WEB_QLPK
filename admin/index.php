<?php
session_start();
require_once '../config.php';

// Tổng sản phẩm
$total_products = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();

// Đơn hàng hôm nay
$today_orders = $pdo->query("SELECT COUNT(*) FROM orders WHERE DATE(created_at) = CURDATE()")->fetchColumn();

// Doanh thu tháng này
$this_month = date('Y-m');
$month_revenue = $pdo->query("SELECT SUM(total_amount) FROM orders WHERE status='completed' AND DATE_FORMAT(created_at, '%Y-%m') = '$this_month'")->fetchColumn();

// Sản phẩm sắp hết (tồn kho <= 10)
$low_stock = $pdo->query("SELECT COUNT(*) FROM products WHERE stock_quantity <= 10")->fetchColumn();

// Dữ liệu doanh thu 6 tháng gần nhất cho biểu đồ
$revenue_chart = $pdo->query("
    SELECT DATE_FORMAT(created_at, '%m/%Y') as month, SUM(total_amount) as revenue
    FROM orders
    WHERE status='completed'
    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
    ORDER BY DATE_FORMAT(created_at, '%Y-%m') DESC
    LIMIT 6
")->fetchAll(PDO::FETCH_ASSOC);
$revenue_chart = array_reverse($revenue_chart); // Để tháng cũ lên trước
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Shop Quần Áo Thể Thao</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
            color: #333;
        }

        .admin-container {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 250px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px 0;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }

        .sidebar h2 {
            text-align: center;
            margin-bottom: 30px;
            font-size: 1.5em;
            border-bottom: 2px solid rgba(255,255,255,0.2);
            padding-bottom: 15px;
        }

        .sidebar ul {
            list-style: none;
        }

        .sidebar ul li {
            margin-bottom: 5px;
        }

        .sidebar ul li a {
            display: block;
            padding: 15px 20px;
            color: white;
            text-decoration: none;
            transition: all 0.3s;
            border-left: 4px solid transparent;
        }

        .sidebar ul li a:hover,
        .sidebar ul li a.active {
            background: rgba(255,255,255,0.1);
            border-left-color: #fff;
            transform: translateX(5px);
        }

        .main-content {
            flex: 1;
            padding: 20px;
        }

        .header {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h1 {
            color: #333;
            font-size: 2em;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .content-section {
            display: none;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .content-section.active {
            display: block;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: linear-gradient(135deg, #ff6b6b, #ee5a24);
            color: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-card:nth-child(2) {
            background: linear-gradient(135deg, #4ecdc4, #44a08d);
        }

        .stat-card:nth-child(3) {
            background: linear-gradient(135deg, #45b7d1, #2980b9);
        }

        .stat-card:nth-child(4) {
            background: linear-gradient(135deg, #f093fb, #f5576c);
        }

        .stat-card h3 {
            font-size: 2.5em;
            margin-bottom: 10px;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }

        .btn-success {
            background: linear-gradient(135deg, #4ecdc4, #44a08d);
            color: white;
        }

        .btn-danger {
            background: linear-gradient(135deg, #ff6b6b, #ee5a24);
            color: white;
        }

        .btn-warning {
            background: linear-gradient(135deg, #feca57, #ff9ff3);
            color: white;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .data-table th,
        .data-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #f0f0f0;
        }

        .data-table th {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            font-weight: 600;
        }

        .data-table tr:hover {
            background-color: #f8f9fa;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #333;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 5px;
            font-size: 14px;
            transition: border-color 0.3s;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }

        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 20px;
            border-radius: 10px;
            width: 80%;
            max-width: 600px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover {
            color: black;
        }

        .search-filter {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .search-filter input {
            flex: 1;
            min-width: 200px;
            padding: 10px;
            border: 2px solid #e0e0e0;
            border-radius: 5px;
        }

        .chart-container {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }

        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-processing {
            background: #d1ecf1;
            color: #0c5460;
        }

        .status-completed {
            background: #d4edda;
            color: #155724;
        }

        .status-cancelled {
            background: #f8d7da;
            color: #721c24;
        }

        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .product-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 5px;
        }

        @media (max-width: 768px) {
            .admin-container {
                flex-direction: column;
            }
            
            .sidebar {
                width: 100%;
                order: 2;
            }
            
            .main-content {
                order: 1;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .grid-2 {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="sidebar">
            <h2>🏃‍♂️ SportShop Admin</h2>
            <ul>
                <li><a href="index.php" class="active">📊 Dashboard</a></li>
                <li><a href="products.php">👕 Quản lý sản phẩm</a></li>
                <li><a href="categories.php">📂 Loại sản phẩm</a></li>
                <li><a href="suppliers.php">🏢 Nhà cung cấp</a></li>
                <li><a href="inventory.php">📦 Tồn kho</a></li>
                <li><a href="orders.php">🛒 Đơn hàng</a></li>
                <li><a href="reports.php">📈 Báo cáo bán hàng</a></li>
                <li><a href="promotions.php">🎁 Khuyến mãi</a></li>
                <li><a href="customers.php">👤 Khách hàng</a></li>
                <li><a href="change_password.php">🔑 Đổi mật khẩu</a></li>
                <li><a href="logout.php">🚪 Đăng xuất</a></li>
            </ul>
        </div>

        <div class="main-content">
            <div class="header">
                <h1>Quản trị hệ thống</h1>
                <div class="user-info">
                    <span>Xin chào, <strong>Admin</strong></span>
                    <button class="btn btn-primary">Đăng xuất</button>
                </div>
            </div>

            <!-- Dashboard Section -->
            <div id="dashboard" class="content-section active">
                <h2>Dashboard - Tổng quan</h2>
                <div class="stats-grid">
                    <div class="stat-card">
                        <h3><?= $total_products ?></h3>
                        <p>Tổng sản phẩm</p>
                    </div>
                    <div class="stat-card">
                        <h3><?= $today_orders ?></h3>
                        <p>Đơn hàng hôm nay</p>
                    </div>
                    <div class="stat-card">
                        <h3><?= number_format($month_revenue,0,',','.') ?> VNĐ</h3>
                        <p>Doanh thu tháng</p>
                    </div>
                    <div class="stat-card">
                        <h3><?= $low_stock ?></h3>
                        <p>Sản phẩm sắp hết</p>
                    </div>
                </div>

                <div class="grid-2">
                    <div class="chart-container">
                        <h3>Doanh thu 6 tháng gần nhất</h3>
                        <canvas id="revenueChart" height="120"></canvas>
                    </div>
                    <div class="chart-container">
                        <h3>Sản phẩm bán chạy</h3>
                        <div style="padding: 20px; text-align: center;">
                            <p>📊 Biểu đồ thống kê sản phẩm bán chạy</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Products Section -->
            <div id="products" class="content-section">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h2>Quản lý sản phẩm</h2>
                    <button class="btn btn-primary" onclick="openModal('add-product')">+ Thêm sản phẩm</button>
                </div>

                <div class="search-filter">
                    <input type="text" placeholder="Tìm kiếm sản phẩm..." id="searchProduct">
                    <select>
                        <option value="">Tất cả danh mục</option>
                        <option value="ao-thun">Áo thun</option>
                        <option value="quan-short">Quần short</option>
                        <option value="giay">Giày</option>
                    </select>
                    <button class="btn btn-primary">Tìm kiếm</button>
                </div>

                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Hình ảnh</th>
                            <th>Tên sản phẩm</th>
                            <th>Danh mục</th>
                            <th>Giá</th>
                            <th>Tồn kho</th>
                            <th>Khuyến mãi</th>
                            <th>Đã bán</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><img src="https://via.placeholder.com/60x60" alt="Sản phẩm" class="product-image"></td>
                            <td>Áo thun Nike Dri-FIT</td>
                            <td>Áo thun</td>
                            <td>590,000 VNĐ</td>
                            <td>25</td>
                            <td><span class="status-badge status-completed">10%</span></td>
                            <td>145</td>
                            <td>
                                <button class="btn btn-warning btn-sm" onclick="openModal('edit-product')">Sửa</button>
                                <button class="btn btn-danger btn-sm" onclick="deleteProduct(1)">Xóa</button>
                            </td>
                        </tr>
                        <tr>
                            <td><img src="https://via.placeholder.com/60x60" alt="Sản phẩm" class="product-image"></td>
                            <td>Quần short Adidas</td>
                            <td>Quần short</td>
                            <td>450,000 VNĐ</td>
                            <td>15</td>
                            <td><span class="status-badge status-pending">0%</span></td>
                            <td>89</td>
                            <td>
                                <button class="btn btn-warning btn-sm" onclick="openModal('edit-product')">Sửa</button>
                                <button class="btn btn-danger btn-sm" onclick="deleteProduct(2)">Xóa</button>
                            </td>
                        </tr>
                        <tr>
                            <td><img src="https://via.placeholder.com/60x60" alt="Sản phẩm" class="product-image"></td>
                            <td>Giày chạy bộ Puma</td>
                            <td>Giày</td>
                            <td>1,200,000 VNĐ</td>
                            <td>8</td>
                            <td><span class="status-badge status-completed">15%</span></td>
                            <td>67</td>
                            <td>
                                <button class="btn btn-warning btn-sm" onclick="openModal('edit-product')">Sửa</button>
                                <button class="btn btn-danger btn-sm" onclick="deleteProduct(3)">Xóa</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Categories Section -->
            <div id="categories" class="content-section">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h2>Quản lý loại sản phẩm</h2>
                    <a href="../index.php" class="btn btn-secondary" style="margin-bottom:15px;">Về trang chủ</a>
                    <button class="btn btn-primary" onclick="openModal('add-category')">+ Thêm loại sản phẩm</button>
                </div>

                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tên loại</th>
                            <th>Mô tả</th>
                            <th>Số sản phẩm</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1</td>
                            <td>Áo thun</td>
                            <td>Áo thun thể thao nam, nữ</td>
                            <td>45</td>
                            <td>
                                <button class="btn btn-warning btn-sm">Sửa</button>
                                <button class="btn btn-danger btn-sm">Xóa</button>
                            </td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td>Quần short</td>
                            <td>Quần short thể thao</td>
                            <td>32</td>
                            <td>
                                <button class="btn btn-warning btn-sm">Sửa</button>
                                <button class="btn btn-danger btn-sm">Xóa</button>
                            </td>
                        </tr>
                        <tr>
                            <td>3</td>
                            <td>Giày</td>
                            <td>Giày thể thao, chạy bộ</td>
                            <td>28</td>
                            <td>
                                <button class="btn btn-warning btn-sm">Sửa</button>
                                <button class="btn btn-danger btn-sm">Xóa</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Suppliers Section -->
            <div id="suppliers" class="content-section">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h2>Quản lý nhà cung cấp</h2>
                    <a href="../index.php" class="btn btn-secondary" style="margin-bottom:15px;">Về trang chủ</a>
                    <button class="btn btn-primary" onclick="openModal('add-supplier')">+ Thêm nhà cung cấp</button>
                </div>

                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tên nhà cung cấp</th>
                            <th>Địa chỉ</th>
                            <th>Điện thoại</th>
                            <th>Email</th>
                            <th>Số sản phẩm</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1</td>
                            <td>Nike Vietnam</td>
                            <td>Hà Nội</td>
                            <td>0123456789</td>
                            <td>nike@vietnam.com</td>
                            <td>45</td>
                            <td>
                                <button class="btn btn-warning btn-sm">Sửa</button>
                                <button class="btn btn-danger btn-sm">Xóa</button>
                            </td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td>Adidas Store</td>
                            <td>TP.HCM</td>
                            <td>0987654321</td>
                            <td>adidas@store.com</td>
                            <td>32</td>
                            <td>
                                <button class="btn btn-warning btn-sm">Sửa</button>
                                <button class="btn btn-danger btn-sm">Xóa</button>
                            </td>
                        </tr>
                        <tr>
                            <td>3</td>
                            <td>Puma Official</td>
                            <td>Đà Nẵng</td>
                            <td>0369852147</td>
                            <td>puma@official.com</td>
                            <td>28</td>
                            <td>
                                <button class="btn btn-warning btn-sm">Sửa</button>
                                <button class="btn btn-danger btn-sm">Xóa</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Inventory Section -->
            <div id="inventory" class="content-section">
                <h2>Thống kê tồn kho</h2>
                <a href="../index.php" class="btn btn-secondary" style="margin-bottom:15px;">Về trang chủ</a>
                
                <div class="search-filter">
                    <select>
                        <option value="">Tất cả sản phẩm</option>
                        <option value="low">Sắp hết hàng (&lt;10)</option>
                        <option value="medium">Trung bình (10-50)</option>
                        <option value="high">Nhiều (&gt;50)</option>
                    </select>
                    <button class="btn btn-primary">Lọc</button>
                </div>

                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Tên sản phẩm</th>
                            <th>Danh mục</th>
                            <th>Số lượng tồn</th>
                            <th>Giá trị tồn kho</th>
                            <th>Trạng thái</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Áo thun Nike Dri-FIT</td>
                            <td>Áo thun</td>
                            <td>25</td>
                            <td>14,750,000 VNĐ</td>
                            <td><span class="status-badge status-completed">Đủ hàng</span></td>
                            <td><button class="btn btn-primary btn-sm">Nhập thêm</button></td>
                        </tr>
                        <tr>
                            <td>Quần short Adidas</td>
                            <td>Quần short</td>
                            <td>15</td>
                            <td>6,750,000 VNĐ</td>
                            <td><span class="status-badge status-processing">Trung bình</span></td>
                            <td><button class="btn btn-primary btn-sm">Nhập thêm</button></td>
                        </tr>
                        <tr>
                            <td>Giày chạy bộ Puma</td>
                            <td>Giày</td>
                            <td>8</td>
                            <td>9,600,000 VNĐ</td>
                            <td><span class="status-badge status-pending">Sắp hết</span></td>
                            <td><button class="btn btn-danger btn-sm">Nhập gấp</button></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Orders Section -->
            <div id="orders" class="content-section">
                <h2>Quản lý đơn hàng</h2>
                <a href="../index.php" class="btn btn-secondary" style="margin-bottom:15px;">Về trang chủ</a>
                
                <div class="search-filter">
                    <input type="text" placeholder="Tìm kiếm đơn hàng...">
                    <select>
                        <option value="">Tất cả trạng thái</option>
                        <option value="pending">Chờ xử lý</option>
                        <option value="processing">Đang xử lý</option>
                        <option value="shipped">Đã giao</option>
                        <option value="cancelled">Đã hủy</option>
                    </select>
                    <button class="btn btn-primary">Tìm kiếm</button>
                </div>

                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Mã đơn</th>
                            <th>Khách hàng</th>
                            <th>Tổng tiền</th>
                            <th>Trạng thái</th>
                            <th>Ngày đặt</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>#DH001</td>
                            <td>Nguyễn Văn A</td>
                            <td>1,590,000 VNĐ</td>
                            <td><span class="status-badge status-pending">Chờ xử lý</span></td>
                            <td>12/07/2025</td>
                            <td>
                                <button class="btn btn-success btn-sm">Xử lý</button>
                                <button class="btn btn-primary btn-sm">Xem</button>
                            </td>
                        </tr>
                        <tr>
                            <td>#DH002</td>
                            <td>Trần Thị B</td>
                            <td>890,000 VNĐ</td>
                            <td><span class="status-badge status-processing">Đang xử lý</span></td>
                            <td>11/07/2025</td>
                            <td>
                                <button class="btn btn-warning btn-sm">Giao hàng</button>
                                <button class="btn btn-primary btn-sm">Xem</button>
                            </td>
                        </tr>
                        <tr>
                            <td>#DH003</td>
                            <td>Lê Văn C</td>
                            <td>1,200,000 VNĐ</td>
                            <td><span class="status-badge status-completed">Đã giao</span></td>
                            <td>10/07/2025</td>
                            <td>
                                <button class="btn btn-primary btn-sm">Xem</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Statistics Section -->
            <div id="statistics" class="content-section">
                <h2>Thống kê và báo cáo</h2>
                <a href="../index.php" class="btn btn-secondary" style="margin-bottom:15px;">Về trang chủ</a>
                
                <div class="search-filter">
                    <select>
                        <option value="today">Hôm nay</option>
                        <option value="week">Tuần này</option>
                        <option value="month">Tháng này</option>
                        <option value="year">Năm này</option>
                    </select>
                    <button class="btn btn-primary">Lọc</button>
                </div>

                <div class="grid-2">
                    <div class="chart-container">
                        <h3>Doanh thu theo thời gian</h3>
                        <div style="padding: 20px; text-align: center;">
                            <p>📊 Biểu đồ doanh thu</p>
                            <p><strong>Tháng này:</strong> 45,200,000 VNĐ</p>
                            <p><strong>Tháng trước:</strong> 38,500,000 VNĐ</p>
                            <p><strong>Tăng trưởng:</strong> +17.4%</p>
                        </div>
                    </div>
                    
                    <div class="chart-container">
                        <h3>Sản phẩm bán chạy</h3>
                        <div style="padding: 20px;">
                            <p>1. Áo thun Nike Dri-FIT - 145 sản phẩm</p>
                            <p>2. Quần short Adidas - 89 sản phẩm</p>
                            <p>3. Giày chạy bộ Puma - 67 sản phẩm</p>
                        </div>
                    </div>
                </div>

                <div class="grid-2">
                    <div class="chart-container">
                        <h3>Sản phẩm bán chậm</h3>
                        <div style="padding: 20px;">
                            <p>1. Áo khoác Nike - 12 sản phẩm</p>
                            <p>2. Quần dài Adidas - 8 sản phẩm</p>
                            <p>3. Giày đá bóng Puma - 5 sản phẩm</p>
                        </div>
                    </div>
                    
                    <div class="chart-container">
                        <h3>Đơn hàng theo trạng thái</h3>
                        <div style="padding: 20px; text-align: center;">
                            <p>📈 Biểu đồ thống kê đơn hàng theo trạng thái</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Promotions Section -->
            <div id="promotions" class="content-section">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h2>Quản lý khuyến mãi</h2>
                    <a href="../index.php" class="btn btn-secondary" style="margin-bottom:15px;">Về trang chủ</a>
                    <button class="btn btn-primary" onclick="openModal('add-promotion')">+ Thêm khuyến mãi</button>
                </div>

                <div class="search-filter">
                    <input type="text" placeholder="Tìm kiếm khuyến mãi...">
                    <select>
                        <option value="">Tất cả sản phẩm</option>
                        <option value="ao-thun">Áo thun</option>
                        <option value="quan-short">Quần short</option>
                        <option value="giay">Giày</option>
                    </select>
                    <button class="btn btn-primary">Tìm kiếm</button>
                </div>

                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tên khuyến mãi</th>
                            <th>Sản phẩm áp dụng</th>
                            <th>Giá trị</th>
                            <th>Ngày bắt đầu</th>
                            <th>Ngày kết thúc</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1</td>
                            <td>Giảm giá mùa hè</td>
                            <td>Áo thun Nike Dri-FIT</td>
                            <td>10%</td>
                            <td>01/06/2025</td>
                            <td>30/06/2025</td>
                            <td>
                                <button class="btn btn-warning btn-sm">Sửa</button>
                                <button class="btn btn-danger btn-sm">Xóa</button>
                            </td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td>Khuyến mãi sinh nhật</td>
                            <td>Quần short Adidas</td>
                            <td>15%</td>
                            <td>10/07/2025</td>
                            <td>20/07/2025</td>
                            <td>
                                <button class="btn btn-warning btn-sm">Sửa</button>
                                <button class="btn btn-danger btn-sm">Xóa</button>
                            </td>
                        </tr>
                        <tr>
                            <td>3</td>
                            <td>Giảm giá giày thể thao</td>
                            <td>Giày chạy bộ Puma</td>
                            <td>20%</td>
                            <td>15/07/2025</td>
                            <td>31/07/2025</td>
                            <td>
                                <button class="btn btn-warning btn-sm">Sửa</button>
                                <button class="btn btn-danger btn-sm">Xóa</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Customers Section -->
            <div id="customers" class="content-section">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h2>Quản lý khách hàng</h2>
                    <a href="../index.php" class="btn btn-secondary" style="margin-bottom:15px;">Về trang chủ</a>
                    <button class="btn btn-primary" onclick="openModal('add-customer')">+ Thêm khách hàng</button>
                </div>

                <div class="search-filter">
                    <input type="text" placeholder="Tìm kiếm khách hàng...">
                    <button class="btn btn-primary">Tìm kiếm</button>
                </div>

                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tên khách hàng</th>
                            <th>Email</th>
                            <th>Điện thoại</th>
                            <th>Địa chỉ</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1</td>
                            <td>Nguyễn Văn A</td>
                            <td>vana@gmail.com</td>
                            <td>0123456789</td>
                            <td>Hà Nội</td>
                            <td>
                                <button class="btn btn-warning btn-sm">Sửa</button>
                                <button class="btn btn-danger btn-sm">Xóa</button>
                            </td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td>Trần Thị B</td>
                            <td>tb@gmail.com</td>
                            <td>0987654321</td>
                            <td>TP.HCM</td>
                            <td>
                                <button class="btn btn-warning btn-sm">Sửa</button>
                                <button class="btn btn-danger btn-sm">Xóa</button>
                            </td>
                        </tr>
                        <tr>
                            <td>3</td>
                            <td>Lê Văn C</td>
                            <td>lec@gmail.com</td>
                            <td>0369852147</td>
                            <td>Đà Nẵng</td>
                            <td>
                                <button class="btn btn-warning btn-sm">Sửa</button>
                                <button class="btn btn-danger btn-sm">Xóa</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Change Password Section -->
            <div id="change_password" class="content-section">
                <h2>Đổi mật khẩu</h2>
                <a href="../index.php" class="btn btn-secondary" style="margin-bottom:15px;">Về trang chủ</a>
                
                <form action="#" method="POST">
                    <div class="form-group">
                        <label for="currentPassword">Mật khẩu hiện tại</label>
                        <input type="password" id="currentPassword" name="currentPassword" required>
                    </div>
                    <div class="form-group">
                        <label for="newPassword">Mật khẩu mới</label>
                        <input type="password" id="newPassword" name="newPassword" required>
                    </div>
                    <div class="form-group">
                        <label for="confirmPassword">Xác nhận mật khẩu mới</label>
                        <input type="password" id="confirmPassword" name="confirmPassword" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Đổi mật khẩu</button>
                </form>
            </div>

            <!-- Logout Section -->
            <div id="logout" class="content-section">
                <h2>Đăng xuất</h2>
                <p>Bạn có chắc chắn muốn đăng xuất khỏi tài khoản này?</p>
                <div>
                    <a href="login.php" class="btn btn-danger">Đăng xuất</a>
                    <a href="#" class="btn btn-secondary" onclick="showSection('dashboard')">Quay lại</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Modals -->
    <div class="modal" id="add-product">
        <div class="modal-content">
            <span class="close" onclick="closeModal('add-product')">&times;</span>
            <h2>Thêm sản phẩm mới</h2>
            <form action="#" method="POST">
                <div class="form-group">
                    <label for="productName">Tên sản phẩm</label>
                    <input type="text" id="productName" name="productName" required>
                </div>
                <div class="form-group">
                    <label for="productCategory">Danh mục</label>
                    <select id="productCategory" name="productCategory" required>
                        <option value="ao-thun">Áo thun</option>
                        <option value="quan-short">Quần short</option>
                        <option value="giay">Giày</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="productPrice">Giá</label>
                    <input type="number" id="productPrice" name="productPrice" required>
                </div>
                <div class="form-group">
                    <label for="productStock">Tồn kho</label>
                    <input type="number" id="productStock" name="productStock" required>
                </div>
                <div class="form-group">
                    <label for="productDiscount">Khuyến mãi</label>
                    <input type="text" id="productDiscount" name="productDiscount">
                </div>
                <button type="submit" class="btn btn-primary">Thêm sản phẩm</button>
            </form>
        </div>
    </div>

    <div class="modal" id="edit-product">
        <div class="modal-content">
            <span class="close" onclick="closeModal('edit-product')">&times;</span>
            <h2>Sửa sản phẩm</h2>
            <form action="#" method="POST">
                <div class="form-group">
                    <label for="editProductName">Tên sản phẩm</label>
                    <input type="text" id="editProductName" name="editProductName" value="Áo thun Nike Dri-FIT" required>
                </div>
                <div class="form-group">
                    <label for="editProductCategory">Danh mục</label>
                    <select id="editProductCategory" name="editProductCategory" required>
                        <option value="ao-thun" selected>Áo thun</option>
                        <option value="quan-short">Quần short</option>
                        <option value="giay">Giày</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="editProductPrice">Giá</label>
                    <input type="number" id="editProductPrice" name="editProductPrice" value="590000" required>
                </div>
                <div class="form-group">
                    <label for="editProductStock">Tồn kho</label>
                    <input type="number" id="editProductStock" name="editProductStock" value="25" required>
                </div>
                <div class="form-group">
                    <label for="editProductDiscount">Khuyến mãi</label>
                    <input type="text" id="editProductDiscount" name="editProductDiscount" value="10%">
                </div>
                <button type="submit" class="btn btn-primary">Cập nhật sản phẩm</button>
            </form>
        </div>
    </div>

    <div class="modal" id="add-category">
        <div class="modal-content">
            <span class="close" onclick="closeModal('add-category')">&times;</span>
            <h2>Thêm loại sản phẩm mới</h2>
            <form action="#" method="POST">
                <div class="form-group">
                    <label for="categoryName">Tên loại sản phẩm</label>
                    <input type="text" id="categoryName" name="categoryName" required>
                </div>
                <div class="form-group">
                    <label for="categoryDescription">Mô tả</label>
                    <textarea id="categoryDescription" name="categoryDescription" rows="3" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Thêm loại sản phẩm</button>
            </form>
        </div>
    </div>

    <div class="modal" id="edit-category">
        <div class="modal-content">
            <span class="close" onclick="closeModal('edit-category')">&times;</span>
            <h2>Sửa loại sản phẩm</h2>
            <form action="#" method="POST">
                <div class="form-group">
                    <label for="editCategoryName">Tên loại sản phẩm</label>
                    <input type="text" id="editCategoryName" name="editCategoryName" value="Áo thun" required>
                </div>
                <div class="form-group">
                    <label for="editCategoryDescription">Mô tả</label>
                    <textarea id="editCategoryDescription" name="editCategoryDescription" rows="3" required>Áo thun thể thao nam, nữ</textarea>
                </div>
                <button type="submit" class="btn btn-primary">Cập nhật loại sản phẩm</button>
            </form>
        </div>
    </div>

    <div class="modal" id="add-supplier">
        <div class="modal-content">
            <span class="close" onclick="closeModal('add-supplier')">&times;</span>
            <h2>Thêm nhà cung cấp mới</h2>
            <form action="#" method="POST">
                <div class="form-group">
                    <label for="supplierName">Tên nhà cung cấp</label>
                    <input type="text" id="supplierName" name="supplierName" required>
                </div>
                <div class="form-group">
                    <label for="supplierAddress">Địa chỉ</label>
                    <input type="text" id="supplierAddress" name="supplierAddress" required>
                </div>
                <div class="form-group">
                    <label for="supplierPhone">Điện thoại</label>
                    <input type="text" id="supplierPhone" name="supplierPhone" required>
                </div>
                <div class="form-group">
                    <label for="supplierEmail">Email</label>
                    <input type="email" id="supplierEmail" name="supplierEmail" required>
                </div>
                <button type="submit" class="btn btn-primary">Thêm nhà cung cấp</button>
            </form>
        </div>
    </div>

    <div class="modal" id="edit-supplier">
        <div class="modal-content">
            <span class="close" onclick="closeModal('edit-supplier')">&times;</span>
            <h2>Sửa nhà cung cấp</h2>
            <form action="#" method="POST">
                <div class="form-group">
                    <label for="editSupplierName">Tên nhà cung cấp</label>
                    <input type="text" id="editSupplierName" name="editSupplierName" value="Nike Vietnam" required>
                </div>
                <div class="form-group">
                    <label for="editSupplierAddress">Địa chỉ</label>
                    <input type="text" id="editSupplierAddress" name="editSupplierAddress" value="Hà Nội" required>
                </div>
                <div class="form-group">
                    <label for="editSupplierPhone">Điện thoại</label>
                    <input type="text" id="editSupplierPhone" name="editSupplierPhone" value="0123456789" required>
                </div>
                <div class="form-group">
                    <label for="editSupplierEmail">Email</label>
                    <input type="email" id="editSupplierEmail" name="editSupplierEmail" value="nike@vietnam.com" required>
                </div>
                <button type="submit" class="btn btn-primary">Cập nhật nhà cung cấp</button>
            </form>
        </div>
    </div>

    <div class="modal" id="add-promotion">
        <div class="modal-content">
            <span class="close" onclick="closeModal('add-promotion')">&times;</span>
            <h2>Thêm khuyến mãi mới</h2>
            <form action="#" method="POST">
                <div class="form-group">
                    <label for="promotionName">Tên khuyến mãi</label>
                    <input type="text" id="promotionName" name="promotionName" required>
                </div>
                <div class="form-group">
                    <label for="appliedProduct">Sản phẩm áp dụng</label>
                    <select id="appliedProduct" name="appliedProduct" required>
                        <option value="ao-thun">Áo thun Nike Dri-FIT</option>
                        <option value="quan-short">Quần short Adidas</option>
                        <option value="giay">Giày chạy bộ Puma</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="discountValue">Giá trị</label>
                    <input type="text" id="discountValue" name="discountValue" required>
                </div>
                <div class="form-group">
                    <label for="startDate">Ngày bắt đầu</label>
                    <input type="date" id="startDate" name="startDate" required>
                </div>
                <div class="form-group">
                    <label for="endDate">Ngày kết thúc</label>
                    <input type="date" id="endDate" name="endDate" required>
                </div>
                <button type="submit" class="btn btn-primary">Thêm khuyến mãi</button>
            </form>
        </div>
    </div>

    <div class="modal" id="edit-promotion">
        <div class="modal-content">
            <span class="close" onclick="closeModal('edit-promotion')">&times;</span>
            <h2>Sửa khuyến mãi</h2>
            <form action="#" method="POST">
                <div class="form-group">
                    <label for="editPromotionName">Tên khuyến mãi</label>
                    <input type="text" id="editPromotionName" name="editPromotionName" value="Giảm giá mùa hè" required>
                </div>
                <div class="form-group">
                    <label for="editAppliedProduct">Sản phẩm áp dụng</label>
                    <select id="editAppliedProduct" name="editAppliedProduct" required>
                        <option value="ao-thun" selected>Áo thun Nike Dri-FIT</option>
                        <option value="quan-short">Quần short Adidas</option>
                        <option value="giay">Giày chạy bộ Puma</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="editDiscountValue">Giá trị</label>
                    <input type="text" id="editDiscountValue" name="editDiscountValue" value="10%" required>
                </div>
                <div class="form-group">
                    <label for="editStartDate">Ngày bắt đầu</label>
                    <input type="date" id="editStartDate" name="editStartDate" value="2025-06-01" required>
                </div>
                <div class="form-group">
                    <label for="editEndDate">Ngày kết thúc</label>
                    <input type="date" id="editEndDate" name="editEndDate" value="2025-06-30" required>
                </div>
                <button type="submit" class="btn btn-primary">Cập nhật khuyến mãi</button>
            </form>
        </div>
    </div>

    <div class="modal" id="add-customer">
        <div class="modal-content">
            <span class="close" onclick="closeModal('add-customer')">&times;</span>
            <h2>Thêm khách hàng mới</h2>
            <form action="#" method="POST">
                <div class="form-group">
                    <label for="customerName">Tên khách hàng</label>
                    <input type="text" id="customerName" name="customerName" required>
                </div>
                <div class="form-group">
                    <label for="customerEmail">Email</label>
                    <input type="email" id="customerEmail" name="customerEmail" required>
                </div>
                <div class="form-group">
                    <label for="customerPhone">Điện thoại</label>
                    <input type="text" id="customerPhone" name="customerPhone" required>
                </div>
                <div class="form-group">
                    <label for="customerAddress">Địa chỉ</label>
                    <input type="text" id="customerAddress" name="customerAddress" required>
                </div>
                <button type="submit" class="btn btn-primary">Thêm khách hàng</button>
            </form>
        </div>
    </div>

    <div class="modal" id="edit-customer">
        <div class="modal-content">
            <span class="close" onclick="closeModal('edit-customer')">&times;</span>
            <h2>Sửa thông tin khách hàng</h2>
            <form action="#" method="POST">
                <div class="form-group">
                    <label for="editCustomerName">Tên khách hàng</label>
                    <input type="text" id="editCustomerName" name="editCustomerName" value="Nguyễn Văn A" required>
                </div>
                <div class="form-group">
                    <label for="editCustomerEmail">Email</label>
                    <input type="email" id="editCustomerEmail" name="editCustomerEmail" value="vana@gmail.com" required>
                </div>
                <div class="form-group">
                    <label for="editCustomerPhone">Điện thoại</label>
                    <input type="text" id="editCustomerPhone" name="editCustomerPhone" value="0123456789" required>
                </div>
                <div class="form-group">
                    <label for="editCustomerAddress">Địa chỉ</label>
                    <input type="text" id="editCustomerAddress" name="editCustomerAddress" value="Hà Nội" required>
                </div>
                <button type="submit" class="btn btn-primary">Cập nhật khách hàng</button>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Biểu đồ doanh thu 6 tháng gần nhất
        const ctx = document.getElementById('revenueChart').getContext('2d');
        const revenueChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?= json_encode(array_column($revenue_chart, 'month')) ?>,
                datasets: [{
                    label: 'Doanh thu (VNĐ)',
                    data: <?= json_encode(array_map('intval', array_column($revenue_chart, 'revenue'))) ?>,
                    backgroundColor: 'rgba(37,99,235,0.7)'
                }]
            },
            options: {
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    </script>
    <script>
        function showSection(sectionId) {
            const sections = document.querySelectorAll('.content-section');
            sections.forEach(section => {
                section.classList.remove('active');
            });

            const links = document.querySelectorAll('.sidebar ul li a');
            links.forEach(link => {
                link.classList.remove('active');
            });

            document.getElementById(sectionId).classList.add('active');
            const activeLink = Array.from(links).find(link => link.getAttribute('onclick').includes(sectionId));
            if (activeLink) {
                activeLink.classList.add('active');
            }
        }

        function openModal(modalId) {
            document.getElementById(modalId).style.display = 'block';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        function deleteProduct(productId) {
            if (confirm('Bạn có chắc chắn muốn xóa sản phẩm này?')) {
                // Thực hiện xóa sản phẩm
                alert('Sản phẩm đã được xóa.');
            }
        }
    </script>
</body>
</html>