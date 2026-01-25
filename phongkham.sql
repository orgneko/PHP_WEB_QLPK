-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th1 25, 2026 lúc 10:13 AM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `phongkham`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT 1,
  `size` varchar(10) DEFAULT NULL,
  `color` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`, `created_at`, `updated_at`) VALUES
(1, 'Áo thể thao', 'Các loại áo thể thao cho nam và nữ', '2025-07-08 13:41:52', '2025-07-08 13:41:52'),
(2, 'Quần thể thao', 'Quần short, quần dài thể thao', '2025-07-08 13:41:52', '2025-07-08 13:41:52'),
(3, 'Giày thể thao', 'Giày chạy bộ, giày tập gym', '2025-07-08 13:41:52', '2025-07-08 13:41:52'),
(5, 'Giày thể thao', 'Các loại giày thể thao, chạy bộ, đá bóng', '2025-07-14 13:39:06', '2025-07-14 13:39:06'),
(6, 'Áo thể thao', 'Áo thi đấu, áo tập luyện các môn thể thao', '2025-07-14 13:39:06', '2025-07-14 13:39:06'),
(7, 'Quần thể thao', 'Quần short, quần dài thể thao', '2025-07-14 13:39:06', '2025-07-14 13:39:06'),
(8, 'Dụng cụ thể thao', 'Bóng, băng bảo vệ, găng tay,...', '2025-07-14 13:39:06', '2025-07-14 13:39:06'),
(9, 'Phụ kiện', 'Túi đựng đồ, bình nước, phụ kiện thể thao', '2025-07-14 13:39:06', '2025-07-14 13:39:06');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `feedback`
--

CREATE TABLE `feedback` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `message` text NOT NULL,
  `status` enum('pending','replied') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `order_number` varchar(50) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `payment_method` enum('atm','cash') DEFAULT 'cash',
  `delivery_method` enum('post','express','direct') DEFAULT 'post',
  `delivery_address` text DEFAULT NULL,
  `status` varchar(50) DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `doctor_id` int(11) DEFAULT NULL,
  `appointment_date` datetime DEFAULT NULL,
  `fullname` varchar(255) DEFAULT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `note` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `order_number`, `total_amount`, `payment_method`, `delivery_method`, `delivery_address`, `status`, `created_at`, `doctor_id`, `appointment_date`, `fullname`, `phone_number`, `note`) VALUES
(1, NULL, '', 0.00, 'cash', 'post', NULL, 'confirmed', '2026-01-25 06:41:11', 3, '2026-01-25 13:42:00', 'Đặng Trần Xuân Bình', '0867 055 142', 'Đau dái'),
(3, NULL, 'BHH-6975BB91A372B', 0.00, 'cash', 'post', NULL, 'confirmed', '2026-01-25 06:43:29', 3, '2026-01-30 13:44:00', 'Đặng Trần Xuân Bình', '0867 055 142', 'Đau chim'),
(4, NULL, 'BHH-6975BB9F3E892', 0.00, 'cash', 'post', NULL, 'confirmed', '2026-01-25 06:43:43', 3, '2026-02-07 01:43:00', 'Đặng Trần Xuân Bình', '0867 055 142', 'Đau chim'),
(5, NULL, 'BHH-6975BCA42FA70', 0.00, 'cash', 'post', NULL, 'confirmed', '2026-01-25 06:48:04', 3, '2026-02-01 13:50:00', 'Đặng Trần Xuân Bình', '0867 055 142', 'Đau chim');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `size` varchar(10) DEFAULT NULL,
  `color` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `code` varchar(50) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `sale_price` decimal(10,2) DEFAULT NULL,
  `stock_quantity` int(11) DEFAULT 0,
  `sizes` varchar(255) DEFAULT NULL,
  `colors` varchar(255) DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `products`
--

INSERT INTO `products` (`id`, `name`, `code`, `category_id`, `supplier_id`, `description`, `price`, `sale_price`, `stock_quantity`, `sizes`, `colors`, `image_url`, `status`, `created_at`, `updated_at`) VALUES
(2, 'Quần short Adidas', 'AD002', 2, NULL, 'Quần short thể thao thoáng mát', 450000.00, NULL, 25, 'S,M,L,XL,XXL', 'Đen,Xám,Xanh navy', 'https://www.bing.com/th/id/OIP.IlSEdbc941Qn6fZtqEcCjwHaHa?w=175&h=185&c=8&rs=1&qlt=90&o=6&dpr=1.3&pid=3.1&rm=2', 'active', '2025-07-08 13:41:52', '2026-01-25 06:30:47'),
(4, 'Áo khoác Nike', 'NK004', 1, NULL, 'Áo khoác thể thao chống gió', 890000.00, NULL, 18, 'M,L,XL', 'Đen,Xanh,Đỏ', 'https://bizweb.dktcdn.net/thumb/large/100/340/361/products/ao-thun-terrex-xperior-climacool-trang-jn8134-hm30.jpg?v=1745035366060', 'active', '2025-07-08 13:41:52', '2026-01-25 06:30:47'),
(5, 'Áo T’shirt Das Mới Nhất Màu Đen ', 'A431', 1, NULL, '– Hàng xuất dư xịn, chất vải thun trơn, mịn màng, thoáng mát\r\n\r\n– Thiết kế với tông màu dễ mặc, logo ép vân cực sắc nét.', 150000.00, NULL, 5, 'S,M,L,XL', 'Đen', 'https://kingmensport.vn/wp-content/uploads/2022/03/z3260575456922_b04b23b27da5d321f9672cef84787454-768x768.jpg', 'active', '2025-07-25 08:01:43', '2026-01-25 06:30:47'),
(6, 'Áo Thun Nike Màu Đen ', 'A451', 1, NULL, '– Hàng xuất dư xịn, chất vải thun trơn, mịn màng, thoáng mát\r\n\r\n– Thiết kế với tông màu dễ mặc, logo ép vân cực sắc nét.', 170000.00, NULL, 11, 'M,L,XL', 'Đen', 'https://kingmensport.vn/wp-content/uploads/2022/04/z3339643037077_09fa0e995a6244987f34dced89a27a59-768x768.jpg', 'active', '2025-07-25 08:08:31', '2026-01-25 06:30:47'),
(8, 'Áo Thun Adidas Màu Trắng', 'A442', 1, NULL, '– Hàng xuất dư xịn, chất vải thun trơn, mịn màng, thoáng mát\r\n\r\n– Thiết kế với tông màu dễ mặc, logo ép vân cực sắc nét.', 150000.00, NULL, 10, 'M,L,XL', 'Trắng', 'https://kingmensport.vn/wp-content/uploads/2022/03/z3275989160785_99ed7e499762f85ee3f191a51d8fece2-768x768.jpg', 'active', '2025-07-25 08:08:31', '2026-01-25 06:30:47'),
(9, 'Quần Short Nike', 'Q27', 2, NULL, '– Chất thun thể thao cao cấp, thoáng mát, co dãn\r\n\r\n– Logo in sắc nét, đường may kĩ, đẹp.', 170000.00, NULL, 9, 'M,L,XL', 'Đen,Trắng,Xám đen', 'https://kingmensport.vn/wp-content/uploads/2021/06/27-768x768.jpg', 'active', '2025-07-25 08:13:22', '2026-01-25 06:30:47'),
(10, 'Quần Short Chất Si Gió Adidas ', 'Q32', 2, NULL, '– Chất si co dãn 4 chiều, thoáng mát\r\n\r\n– Logo thêu sắc nét, dễ phối đồ, đường may kĩ, đẹp.', 170000.00, NULL, 7, 'M,L,XL', 'Đen', 'https://kingmensport.vn/wp-content/uploads/2022/03/z3276094507830_08472cd8191299a1a55bdd2c632fcb46-600x600.jpg', 'active', '2025-07-25 08:13:22', '2026-01-25 06:30:47'),
(11, 'Quần Short Puma', 'Q31', 2, NULL, '– Chất thun thể thao cao cấp, thoáng mát, co dãn\r\n\r\n– Logo in sắc nét, đường may kĩ, đẹp.', 170000.00, NULL, 5, 'M,L,XL', 'Đen logo trắng', 'https://kingmensport.vn/wp-content/uploads/2022/03/z3262131869918_603f8ff97a7a00f4e984e79825a06549-768x768.jpg', 'active', '2025-07-25 08:17:18', '2026-01-25 06:30:47'),
(12, 'QUẦN JOGGER THỂ THAO NAM ADIDAS', 'Q08', 2, NULL, 'Kiểu dáng: Quần jogger basic adidas phối sọc trắng\r\n\r\nChất liệu: Vải da cá, mềm mát\r\n\r\nMàu sắc: Đen, Xám, Xám rêu, Xanh', 180000.00, NULL, 8, 'M,L,XL', 'Đen,Xám,Xám rêu', 'https://kingmensport.vn/wp-content/uploads/2019/09/Collage_Fotor-12-768x1024.jpg', 'active', '2025-07-25 08:17:18', '2026-01-25 06:30:47'),
(13, 'GIÀY WIKA GALAXY LIGHT', 'Q01', 3, NULL, '– Với thiết kế thân giày là chất liệu da tổng hợp mềm mại được kết hợp với bề mặt vân nổi hỗ trợ cầu thủ trong việc kiểm soát và cảm giác bóng tốt trong các điều kiện thời tiết khác nhau.\r\n– Đế giày được bố trí đều lớp đinh tròn nhằm tạo độ bám với bề mặt tiếp xúc, giữ thăng bằng và tránh trơn trượt kể cả khi trời mưa.\r\n– Thiết kế cổ chun mềm mại ôm chân vừa giúp giữ cố định phần cổ chân, tăng sự linh hoạt và nhạy bén khi vận động.\r\n– Màu sắc trẻ trung, bắt mắt: Bạc Cam – Cam – Xanh Dương – Đen\r\n– Size giày từ 39 – 43', 410000.00, NULL, 4, '39,40,41,42,43', 'Đỏ', 'https://kingmensport.vn/wp-content/uploads/2022/12/z3586163073623_bb14517fcb7b32766f4c57f81db408ee-768x768.jpg', 'active', '2025-07-25 08:24:17', '2026-01-25 06:30:47'),
(14, 'Giày Wika QH19NEO Xanh Ngọc', 'QH19', 3, NULL, '– Sản phẩm được tham gia thiết kế bởi chính cầu thủ quốc gia Nguyễn Quang Hải\r\n– Màu sắc trẻ trung, bắt mắt tạo cảm giác mới lạ cho người dùng.\r\n– QH19 NEO sử dụng chất liệu da Microfiber cao cấp\r\n– Sản phẩm QH19 NEO sở hữu bộ Đế giày làm từ chất liệu cao su tổng hợp mềm dẻo và được khâu toàn bộ\r\n– Phần lót giày được sản xuất theo công nghệ EVA tạo cảm giác êm ái vô cùng.', 420000.00, NULL, 2, '39,40,41,42,43', 'Xanh Ngọc', 'https://kingmensport.vn/wp-content/uploads/2021/04/size_637518506799896366_HasThumb-768x768.png', 'active', '2025-07-25 08:37:31', '2026-01-25 06:30:47'),
(15, 'GIÀY WIKA HĐ14 – MÀU HỒNG', 'HĐ14', 3, NULL, '– Đệm lót Eva tối ưu sự êm ái, tạo cảm giác trợ lực, giúp cho mọi đường bóng tới khung thành trở nên dễ dàng hơn.\r\n– Đệm gót giày bổ sung công nghệ Wi – Control siêu nhẹ, giúp người dùng thoải mái và nâng đỡ linh hoạt.\r\n– Định hình gót ổn định, cứng cáp, cố định bàn chân và bảo vệ cổ chân.\r\n– Đế giày được khâu tỉ mỉ toàn bộ, đinh giày cao su tổng hợp với kết cấu khoa học, duy trì sự bền bỉ và trụ vững vàng.', 490000.00, NULL, 4, '39,40,41,42,43', 'Hồng', 'https://kingmensport.vn/wp-content/uploads/2023/02/331131552_5933897656655855_510054232188727582_n-768x768.jpg', 'active', '2025-07-25 08:37:31', '2026-01-25 06:30:47'),
(16, 'Giầy Casual Nam Thời Trang ', 'S19', 3, NULL, '• Kiểu dáng: Giày casual thời trang\r\n• Chất liệu: Knit\r\n• Màu sắc: Đen, Trắng\r\n• Kích cỡ: 40-41-42-43-44\r\n• Chất liệu vải dệt Knit mềm mịn, đế cao su\r\n• Độ đàn hồi, co dãn tốt, ôm khít vừa chân', 390000.00, NULL, 7, '39,40,41,42,43', 'Trắng', 'https://kingmensport.vn/wp-content/uploads/2019/09/3-11-768x621.jpg', 'active', '2025-07-25 08:49:14', '2026-01-25 06:30:47'),
(17, '\r\nGiầy Thể Thao Casual Nam', 'S20', 3, NULL, '• Kiểu dáng: Giày thể thao casual\r\n• Chất liệu: Knit\r\n• Màu sắc: Đen, xám, xám tro\r\n• Kích cỡ: 40-41-42-43-44\r\n• Chất liệu vải dệt Knit mềm mịn, đế cao su\r\n• Độ đàn hồi, co dãn tốt, ôm khít vừa chân', 350000.00, NULL, 5, '39,40,41,42,43', 'Xám', 'https://kingmensport.vn/wp-content/uploads/2019/09/10-11-768x654.jpg', 'active', '2025-07-25 08:49:14', '2026-01-25 06:30:47');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `promotions`
--

CREATE TABLE `promotions` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `discount_percent` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` enum('active','inactive') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `suppliers`
--

CREATE TABLE `suppliers` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `image` varchar(255) DEFAULT 'https://via.placeholder.com/150'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `suppliers`
--

INSERT INTO `suppliers` (`id`, `name`, `email`, `phone`, `address`, `created_at`, `image`) VALUES
(1, 'TS.BS Nguyễn Văn A', 'bs.nguyenvana@bhh.com', '0901234567', 'Khoa Thần kinh', '2026-01-25 06:30:47', 'https://images.unsplash.com/photo-1612349317150-e413f6a5b16d?w=200&q=80'),
(2, 'ThS.BS Trần Thị B', 'bs.tranthib@bhh.com', '0902345678', 'Khoa Tim mạch', '2026-01-25 06:30:47', 'https://images.unsplash.com/photo-1594824476967-48c8b964273f?w=200&q=80'),
(3, 'BSCKII Lê Văn C', 'bs.levanc@bhh.com', '0903456789', 'Chấn thương chỉnh hình', '2026-01-25 06:30:47', 'https://images.unsplash.com/photo-1622253692010-333f2da6031d?w=200&q=80'),
(4, 'BS Phạm Thị D', 'bs.phamthid@bhh.com', '0904567890', 'Nha khoa', '2026-01-25 06:30:47', 'https://images.unsplash.com/photo-1559839734-2b71ea197ec2?w=200&q=80');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `role` enum('customer','admin') DEFAULT 'customer',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `full_name`, `phone`, `address`, `role`, `created_at`) VALUES
(1, 'admin', 'admin@sportswear.com', '$2y$10$313EYgYuJk8KduYxCqoeD.T5HPcV14eraELtyL3KJJ50ID6ZDaHF6', 'Quản trị viên', '0123456789', 'Hà Nội', 'admin', '2025-07-08 13:41:52'),
(3, 'pmanhdz', 'phanmanh1805@gmail.com', '$2y$10$Krisrs54NBLjc6mAbeknNOtsn/JMZQI9zRyHQpFrCXOcFYodRBJtG', 'phanmanh', '0976391541', 'hanoi', 'customer', '2025-07-08 13:49:10');

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Chỉ mục cho bảng `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Chỉ mục cho bảng `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_number` (`order_number`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `fk_orders_doctor` (`doctor_id`);

--
-- Chỉ mục cho bảng `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Chỉ mục cho bảng `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `supplier_id` (`supplier_id`);

--
-- Chỉ mục cho bảng `promotions`
--
ALTER TABLE `promotions`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT cho bảng `feedback`
--
ALTER TABLE `feedback`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT cho bảng `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT cho bảng `promotions`
--
ALTER TABLE `promotions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT cho bảng `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `feedback`
--
ALTER TABLE `feedback`
  ADD CONSTRAINT `feedback_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Các ràng buộc cho bảng `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_orders_doctor` FOREIGN KEY (`doctor_id`) REFERENCES `suppliers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Các ràng buộc cho bảng `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Các ràng buộc cho bảng `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`),
  ADD CONSTRAINT `products_ibfk_2` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
