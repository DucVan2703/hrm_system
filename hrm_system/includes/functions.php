<?php
// Autoload class tự động cho các file chạy trực tiếp ngoài MVC
spl_autoload_register(function ($class) {
    $paths = [
        __DIR__ . '/../core/',
        __DIR__ . '/../app/controllers/',
        __DIR__ . '/../app/models/'
    ];
    foreach ($paths as $path) {
        $file = $path . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// Khởi động session nếu chưa có
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Các hàm bổ trợ tương thích ngược (fallback) cho code cũ
if (!function_exists('sanitize')) {
    function sanitize($data) {
        return Helper::sanitize($data);
    }
}

if (!function_exists('clean')) {
    function clean($data) {
        return Helper::clean($data);
    }
}

if (!function_exists('setFlash')) {
    function setFlash($type, $message) {
        $_SESSION['flash'] = ['type' => $type, 'message' => $message];
    }
}

if (!function_exists('formatMoney')) {
    function formatMoney($amount) {
        return Helper::formatMoney($amount);
    }
}

if (!function_exists('formatDate')) {
    function formatDate($date) {
        return Helper::formatDate($date);
    }
}

if (!function_exists('badgeTrangThaiNV')) {
    function badgeTrangThaiNV($status) {
        return Helper::badgeTrangThaiNV($status);
    }
}

if (!function_exists('requireAdmin')) {
    function requireAdmin() {
        Helper::requireAdmin();
    }
}

if (!function_exists('getTongQuan')) {
    function getTongQuan() {
        $db = getDB();
        $tq = [];
        $tq['tong_nv'] = (int)$db->query("SELECT COUNT(*) FROM nhan_vien")->fetchColumn();
        $tq['dang_lam'] = (int)$db->query("SELECT COUNT(*) FROM nhan_vien WHERE trang_thai = 'Đang làm'")->fetchColumn();
        $tq['nghi_viec'] = (int)$db->query("SELECT COUNT(*) FROM nhan_vien WHERE trang_thai = 'Đã nghỉ việc'")->fetchColumn();
        
        $thang = date('n');
        $nam = date('Y');
        $stmt = $db->prepare("SELECT COALESCE(SUM(thuc_linh), 0) FROM bang_luong WHERE thang = ? AND nam = ?");
        $stmt->execute([$thang, $nam]);
        $tq['quy_luong'] = (float)$stmt->fetchColumn();
        
        $tq['hop_dong_hh'] = (int)$db->query("SELECT COUNT(*) FROM hop_dong WHERE trang_thai = 'Hiệu lực'")->fetchColumn();
        $tq['don_cho_duyet'] = (int)$db->query("SELECT COUNT(*) FROM don_nghi_phep WHERE trang_thai = 'Chờ duyệt'")->fetchColumn();
        
        return $tq;
    }
}
