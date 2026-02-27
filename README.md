
# 🏥 PHP_WEB_QLPK - Hệ Thống Quản Lý Phòng Khám

## 📖 Giới thiệu
PHP_WEB_QLPK là hệ thống quản lý phòng khám được xây dựng bằng PHP thuần và MySQL.
Dự án hỗ trợ quản lý bác sĩ, dịch vụ, bệnh nhân và các chức năng quản trị hệ thống.

---

## 🚀 Công nghệ sử dụng

- PHP (PDO)
- MySQL
- HTML/CSS
- JavaScript
- XAMPP (Apache + MySQL)
---
## ⚙️ Cài đặt & chạy project

### 1️⃣ Clone repository
git clone https://github.com/orgneko/PHP_WEB_QLPK.git
### 2️⃣ Import database

- Mở phpMyAdmin
- Tạo database: `phongkham`
- Import file SQL (nếu có)

### 3️⃣ Cấu hình kết nối DB
chỉnh file:
config/config.php
### 4️⃣ Chạy project
Đặt folder vào htdocs
Truy cập:
http://localhost/PHP_WEB_QLPK/

## 🔑 Chức năng chính
👨‍⚕️ Quản lý bác sĩ

Thêm / sửa / xóa bác sĩ

Phân công dịch vụ

🏥 Quản lý dịch vụ

Thêm dịch vụ

Gán bác sĩ phụ trách

👤 Quản lý bệnh nhân

Lưu thông tin bệnh nhân

Theo dõi lịch sử khám

🛠 Hướng phát triển

Tách backend & frontend theo mô hình MVC

Áp dụng REST API

Thêm xác thực JWT

Chuyển sang React frontend
