<?php
// ============================================
// CẤU HÌNH KẾT NỐI DATABASE
// ============================================
define('DB_HOST', 'fdb1032.awardspace.net');
define('DB_USER', '4762137_quanlyluong');
define('DB_PASS', 'Nguyenvan272005@'); // Thay bằng mật khẩu hosting của bạn
define('DB_NAME', '4762137_quanlyluong');
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
$isHttps = false;
if (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] === 'on' || $_SERVER['HTTPS'] == 1)) {
    $isHttps = true;
} elseif (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
    $isHttps = true;
} elseif (isset($_SERVER['HTTP_FRONT_END_HTTPS']) && $_SERVER['HTTP_FRONT_END_HTTPS'] === 'on') {
    $isHttps = true;
}
$protocol = $isHttps ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'ducvan.id.vn';
$dynamic_base_url = $protocol . '://' . $host;

define('APP_NAME', 'Đại Học Thành Đông');
define('APP_VERSION', '1.0.0');
define('BASE_URL', $dynamic_base_url);
define('NGAY_CHUAN', 26); // Số ngày công chuẩn/tháng
define('BHXH_RATE', 0.08); // Tỷ lệ BHXH nhân viên đóng
define('BHYT_RATE', 0.015); // Tỷ lệ BHYT nhân viên đóng
define('TNCN_RATE', 0.05); // Tỷ lệ thuế TNCN tạm tính

// Bắt đầu session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cấu hình tương thích ngược cho mbstring extension nếu server thiếu
if (!function_exists('mb_substr')) {
    function mb_substr($str, $start, $length = null, $encoding = 'UTF-8') {
        if ($length === null) {
            return substr($str, $start);
        }
        return substr($str, $start, $length);
    }
}
if (!function_exists('mb_strtolower')) {
    function mb_strtolower($str, $encoding = 'UTF-8') {
        return strtolower($str);
    }
}
