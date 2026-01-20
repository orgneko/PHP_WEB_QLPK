<?php
session_start();
include 'config.php';

// Xử lý tìm kiếm
$search_results = [];
$search_query = "";

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search_query = $_GET['search'];
    $search_term = '%' . $search_query . '%';

    $sql = "SELECT p.*, c.name AS ten_loai 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE (p.name LIKE ? 
            OR p.code LIKE ? 
            OR p.color LIKE ? 
            OR p.size LIKE ?
            OR c.name LIKE ?)
            AND p.status = 'active'
            ORDER BY p.created_at DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$search_term, $search_term, $search_term, $search_term, $search_term]);
    $search_results = $stmt->fetchAll();
}

// Lấy danh sách loại Dịch vụ cho filter
$categories_sql = "SELECT * FROM categories";
$categories_stmt = $pdo->query($categories_sql);
$categories = $categories_stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tìm kiếm Dịch vụ - SportShop</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .search-form {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .search-input {
            width: 70%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        
        .search-btn {
            width: 25%;
            padding: 12px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            margin-left: 10px;
        }
        
        .search-btn:hover {
            background-color: #0056b3;
        }
        
        .filter-section {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .filter-group {
            flex: 1;
        }
        
        .filter-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        .filter-group select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .results-header {
            background: white;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
        }
        
        .product-card {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        
        .product-card:hover {
            transform: translateY(-5px);
        }
        
        .product-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        
        .product-info {
            padding: 15px;
        }
        
        .product-name {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 5px;
            color: #333;
        }
        
        .product-code {
            font-size: 12px;
            color: #666;
            margin-bottom: 5px;
        }
        
        .product-price {
            font-size: 18px;
            font-weight: bold;
            color: #e74c3c;
            margin-bottom: 10px;
        }
        
        .product-details {
            font-size: 12px;
            color: #666;
            margin-bottom: 10px;
        }
        
        .add-to-cart {
            width: 100%;
            padding: 8px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }
        
        .add-to-cart:hover {
            background-color: #218838;
        }
        
        .no-results {
            text-align: center;
            padding: 40px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #007bff;
            text-decoration: none;
        }
        
        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="index.php" class="back-link">← Quay lại trang chủ</a>
        
        <div class="search-form">
            <h2>Tìm kiếm Dịch vụ</h2>
            <form method="GET" action="search.php">
                <input type="text" name="search" class="search-input" 
                       placeholder="Nhập tên Dịch vụ, mã Dịch vụ, màu sắc, size..." 
                       value="<?php echo htmlspecialchars($search_query); ?>">
                <button type="submit" class="search-btn">Tìm kiếm</button>
            </form>
            
            <div class="filter-section">
                <div class="filter-group">
                    <label>Loại Dịch vụ:</label>
                    <select name="category" id="category">
                        <option value="">Tất cả</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['id_loai']; ?>">
                                <?php echo htmlspecialchars($category['ten_loai']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label>Khoảng giá:</label>
                    <select name="price_range" id="price_range">
                        <option value="">Tất cả</option>
                        <option value="0-500000">Dưới 500.000đ</option>
                        <option value="500000-1000000">500.000đ - 1.000.000đ</option>
                        <option value="1000000-2000000">1.000.000đ - 2.000.000đ</option>
                        <option value="2000000-999999999">Trên 2.000.000đ</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label>Sắp xếp:</label>
                    <select name="sort" id="sort">
                        <option value="newest">Mới nhất</option>
                        <option value="price_asc">Giá: Thấp đến cao</option>
                        <option value="price_desc">Giá: Cao đến thấp</option>
                        <option value="name_asc">Tên: A-Z</option>
                    </select>
                </div>
            </div>
        </div>
        
        <?php if (!empty($search_query)): ?>
            <div class="results-header">
                <h3>Kết quả tìm kiếm cho: "<?php echo htmlspecialchars($search_query); ?>"</h3>
                <p>Tìm thấy <?php echo count($search_results); ?> Dịch vụ</p>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($search_results)): ?>
            <div class="products-grid">
                <?php foreach ($search_results as $product): ?>
                    <div class="product-card">
                        <img src="<?php echo htmlspecialchars($product['hinh_anh']); ?>" 
                             alt="<?php echo htmlspecialchars($product['ten_san_pham']); ?>" 
                             class="product-image">
                        
                        <div class="product-info">
                            <div class="product-name"><?php echo htmlspecialchars($product['ten_san_pham']); ?></div>
                            <div class="product-code">Mã: <?php echo htmlspecialchars($product['ma_san_pham']); ?></div>
                            <div class="product-price"><?php echo number_format($product['gia'], 0, ',', '.'); ?>đ</div>
                            <div class="product-details">
                                Loại: <?php echo htmlspecialchars($product['ten_loai']); ?><br>
                                Màu: <?php echo htmlspecialchars($product['mau_sac']); ?><br>
                                Size: <?php echo htmlspecialchars($product['size']); ?><br>
                                Còn lại: <?php echo $product['so_luong']; ?> Dịch vụ
                            </div>
                            
                            <?php if ($product['so_luong'] > 0): ?>
                                <form method="POST" action="add_to_cart.php">
                                    <input type="hidden" name="product_id" value="<?php echo $product['id_san_pham']; ?>">
                                    <input type="hidden" name="quantity" value="1">
                                    <button type="submit" class="add-to-cart">Đăng ký khám hàng</button>
                                </form>
                            <?php else: ?>
                                <button class="add-to-cart" style="background-color: #6c757d;" disabled>Hết hàng</button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php elseif (!empty($search_query)): ?>
            <div class="no-results">
                <h3>Không tìm thấy Dịch vụ nào</h3>
                <p>Vui lòng thử lại với từ khóa khác hoặc kiểm tra chính tả</p>
            </div>
        <?php endif; ?>
    </div>
    
    <script>
        // Tự động tìm kiếm khi thay đổi filter
        document.getElementById('category').addEventListener('change', function() {
            applyFilters();
        });
        
        document.getElementById('price_range').addEventListener('change', function() {
            applyFilters();
        });
        
        document.getElementById('sort').addEventListener('change', function() {
            applyFilters();
        });
        
        function applyFilters() {
            const searchQuery = '<?php echo addslashes($search_query); ?>';
            const category = document.getElementById('category').value;
            const priceRange = document.getElementById('price_range').value;
            const sort = document.getElementById('sort').value;
            
            let url = 'search.php?search=' + encodeURIComponent(searchQuery);
            if (category) url += '&category=' + category;
            if (priceRange) url += '&price_range=' + priceRange;
            if (sort) url += '&sort=' + sort;
            
            window.location.href = url;
        }
    </script>
</body>
</html>