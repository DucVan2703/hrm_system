<?php
class Helper {
    // 1. AN TOÀN DỮ LIỆU & LỌC ĐẦU VÀO
    public static function sanitize($data) {
        if (is_array($data)) {
            return array_map([self::class, 'sanitize'], $data);
        }
        return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
    }

    public static function clean($data) {
        return htmlspecialchars($data ?? '', ENT_QUOTES, 'UTF-8');
    }

    // 2. ĐỊNH DẠNG HIỂN THỊ
    public static function formatDate($date) {
        if (!$date || $date == '0000-00-00') return '—';
        return date('d/m/Y', strtotime($date));
    }

    public static function formatDateTime($datetime) {
        if (!$datetime || $datetime == '0000-00-00 00:00:00') return '—';
        return date('d/m/Y H:i', strtotime($datetime));
    }

    public static function formatMoney($amount) {
        return number_format((float)($amount ?? 0), 0, ',', '.') . ' VNĐ';
    }

    // 3. BADGES TRẠNG THÁI (GIAO DIỆN)
    public static function badgeTrangThaiNV($status) {
        if ($status === 'Đang làm') {
            return '<span class="badge badge-success">Đang làm</span>';
        }
        return '<span class="badge badge-danger">Đã nghỉ việc</span>';
    }

    public static function badgeTrangThaiHD($status) {
        if ($status === 'Hiệu lực') {
            return '<span class="badge badge-success">Hiệu lực</span>';
        } elseif ($status === 'Hết hiệu lực') {
            return '<span class="badge badge-danger">Hết hiệu lực</span>';
        }
        return '<span class="badge badge-warning">Chờ ký</span>';
    }

    public static function badgeTrangThaiLuong($status) {
        if ($status === 'Đã thanh toán') {
            return '<span class="badge badge-success">Đã thanh toán</span>';
        } elseif ($status === 'Đã duyệt') {
            return '<span class="badge badge-info">Đã duyệt</span>';
        } elseif ($status === 'Chờ duyệt') {
            return '<span class="badge badge-warning">Chờ duyệt</span>';
        }
        return '<span class="badge badge-danger">Chưa duyệt</span>';
    }

    public static function badgeDonPhep($status) {
        if ($status === 'Đã duyệt') {
            return '<span class="badge badge-success">Đã duyệt</span>';
        } elseif ($status === 'Từ chối' || $status === 'Không duyệt') {
            return '<span class="badge badge-danger">Từ chối</span>';
        }
        return '<span class="badge badge-warning">Chờ duyệt</span>';
    }

    // 4. KIỂM TRA PHÂN QUYỀN
    public static function requireLogin() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . self::route('auth/login'));
            exit();
        }
    }

    public static function requireAdmin() {
        self::requireLogin();
        if (!self::isAdmin()) {
            header('Location: ' . self::route('auth/login'));
            exit();
        }
    }
public static function requireKetoan()
{
    self::requireLogin();

    if (!self::isKetoan()) {
        header('Location: ' . self::route('auth/login'));
        exit();
    }
}

public static function requireHR()
{
    self::requireLogin();

    if (!self::isHR()) {
        header('Location: ' . self::route('auth/login'));
        exit();
    }
}
    public static function requireOnlyAdmin() {
        self::requireLogin();
        if (!self::isOnlyAdmin()) {
            header('Location: ' . self::route('auth/login'));
            exit();
        }
    }

    public static function isAdmin() {
        return isset($_SESSION['vai_tro']) && in_array($_SESSION['vai_tro'], ['admin', 'hr', 'ketoan']);
    }

    public static function isOnlyAdmin() {
        return isset($_SESSION['vai_tro']) && $_SESSION['vai_tro'] === 'admin';
    }

    public static function isHR() {
        return isset($_SESSION['vai_tro']) && in_array($_SESSION['vai_tro'], ['admin', 'hr']);
    }

    public static function isKetoan() {
        return isset($_SESSION['vai_tro']) && in_array($_SESSION['vai_tro'], ['admin', 'ketoan']);
    }

    // Route và Asset helper
    public static function route($path) {
        $config = require __DIR__ . '/../config/db_config.php';
        return rtrim($config['base_url'], '/') . '/' . ltrim($path, '/');
    }

    public static function asset($path) {
        $config = require __DIR__ . '/../config/db_config.php';
        return rtrim($config['base_url'], '/') . '/public/assets/' . ltrim($path, '/');
    }
}
