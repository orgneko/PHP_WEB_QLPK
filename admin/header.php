<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin System - Phòng Khám BHH</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        body {
            background-color: #f4f6f9;
            overflow-x: hidden;
        }

        .wrapper {
            display: flex;
            width: 100%;
            align-items: stretch;
        }

        .sidebar {
            min-width: 250px;
            max-width: 250px;
            min-height: 100vh;
            background: #343a40;
            color: white;
            transition: all 0.3s;
        }

        .sidebar .sidebar-header {
            padding: 20px;
            background: #007bff;
        }

        .sidebar ul.components {
            padding: 20px 0;
            border-bottom: 1px solid #47748b;
        }

        .sidebar ul p {
            color: #fff;
            padding: 10px;
        }

        .sidebar ul li a {
            padding: 15px;
            font-size: 1.1em;
            display: block;
            color: #cfd8dc;
            text-decoration: none;
        }

        .sidebar ul li a:hover {
            color: #fff;
            background: #0069d9;
        }

        .sidebar ul li.active>a {
            color: #fff;
            background: #0069d9;
        }

        #content {
            width: 100%;
            padding: 20px;
            min-height: 100vh;
            transition: all 0.3s;
        }
    </style>
</head>

<body>

    <div class="wrapper">
        <nav class="sidebar">
            <div class="sidebar-header">
                <h3><i class="fas fa-user-md"></i> Admin BHH</h3>
            </div>

            <ul class="list-unstyled components">
                <li><a href="index.php"><i class="fas fa-tachometer-alt mr-2"></i> Dashboard</a></li>

                <li><a href="admin_bookings.php"><i class="fas fa-calendar-check mr-2"></i> Quản lý Lịch hẹn</a></li>

                <li><a href="customers.php"><i class="fas fa-users mr-2"></i> Bệnh nhân (Customers)</a></li>
                <li><a href="inventory.php"><i class="fas fa-boxes mr-2"></i> Kho thuốc (Inventory)</a></li>
                <li><a href="orders.php"><i class="fas fa-shopping-cart mr-2"></i> Đơn hàng cũ</a></li>
                <li><a href="change_password.php"><i class="fas fa-key mr-2"></i> Đổi mật khẩu</a></li>
            </ul>

            <div class="text-center mt-5">
                <a href="../index.php" class="btn btn-outline-light btn-sm">Về trang chủ Web</a>
                <br><br>
                <a href="logout.php" class="btn btn-danger btn-sm">Đăng xuất</a>
            </div>
        </nav>

        <div id="content">
            <nav class="navbar navbar-expand-lg navbar-light bg-light mb-4 shadow-sm">
                <div class="container-fluid">
                    <span class="navbar-text">
                        Xin chào, <strong>Admin</strong>
                    </span>
                </div>
            </nav>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>

</html>