<?php
session_start();
session_unset();   // Dọn sạch toàn bộ biến rác
session_destroy(); // Phá hủy hoàn toàn phiên làm việc

// Vì file đang ở actions, cần lùi 1 bước ra admin, rồi đi vào views để tìm login.php
header('Location: ../views/login.php');
exit();
