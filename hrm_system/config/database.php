<?php
// ============================================
// CẤU HÌNH KẾT NỐI DATABASE
// ============================================
define('DB_HOST', 'sql208.infinityfree.com');
define('DB_USER', 'if0_41979793');
define('DB_PASS', 'Nguyenvan2005'); // Thay bằng mật khẩu hosting của bạn
define('DB_NAME', 'if0_41979793_quanlyluong');
define('DB_CHARSET', 'utf8mb4');

// Kết nối database
function getDB()
{
    static $pdo = null;
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die(json_encode(['error' => 'Lỗi kết nối CSDL: ' . $e->getMessage()]));
        }
    }
    return $pdo;
}

// Cấu hình ứng dụng
define('APP_NAME', 'Đại Học Thành Đông');
define('APP_VERSION', '1.0.0');
define('BASE_URL', 'http://ducvan.id.vn');
define('NGAY_CHUAN', 26); // Số ngày công chuẩn/tháng
define('BHXH_RATE', 0.08); // Tỷ lệ BHXH nhân viên đóng
define('BHYT_RATE', 0.015); // Tỷ lệ BHYT nhân viên đóng
define('TNCN_RATE', 0.05); // Tỷ lệ thuế TNCN tạm tính

// Bắt đầu session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
