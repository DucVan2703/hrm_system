<?php
// ============================================
// HỆ THỐNG CÁC HÀM TRỢ GIÚP TOÀN CỤC (GLOBAL HELPERS)
// ============================================

// Khởi chạy session nếu chưa bật
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. KIỂM TRA ĐĂNG NHẬP & PHÂN QUYỀN
function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: ' . BASE_URL . '/login.php');
        exit();
    }
}

function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header('Location: ' . BASE_URL . '/login.php');
        exit();
    }
}

function requireOnlyAdmin() {
    requireLogin();
    if (!isOnlyAdmin()) {
        header('Location: ' . BASE_URL . '/login.php');
        exit();
    }
}

function requireHR() {
    requireLogin();
    if (!isHR()) {
        header('Location: ' . BASE_URL . '/login.php');
        exit();
    }
}

function requireKetoan() {
    requireLogin();
    if (!isKetoan()) {
        header('Location: ' . BASE_URL . '/login.php');
        exit();
    }
}

function isAdmin() {
    return isset($_SESSION['vai_tro']) && in_array($_SESSION['vai_tro'], ['admin', 'hr', 'ketoan']);
}

function isOnlyAdmin() {
    return isset($_SESSION['vai_tro']) && $_SESSION['vai_tro'] === 'admin';
}

function isHR() {
    return isset($_SESSION['vai_tro']) && in_array($_SESSION['vai_tro'], ['admin', 'hr']);
}

function isKetoan() {
    return isset($_SESSION['vai_tro']) && in_array($_SESSION['vai_tro'], ['admin', 'ketoan']);
}

// 2. LẤY THÔNG TIN NGƯỜI DÙNG
function getCurrentUser() {
    static $currentUser = null;
    if ($currentUser !== null) return $currentUser;
    
    if (isset($_SESSION['user_id'])) {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM tai_khoan WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $currentUser = $stmt->fetch();
    }
    return $currentUser;
}

// 3. AN TOÀN DỮ LIỆU & LỌC ĐẦU VÀO
function sanitize($data) {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

function clean($data) {
    return htmlspecialchars($data ?? '', ENT_QUOTES, 'UTF-8');
}

// 4. THÔNG BÁO NHANH (FLASH MESSAGE)
function setFlash($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

// 5. ĐỊNH DẠNG HIỂN THỊ
function formatDate($date) {
    if (!$date || $date == '0000-00-00') return '—';
    return date('d/m/Y', strtotime($date));
}

function formatDateTime($datetime) {
    if (!$datetime || $datetime == '0000-00-00 00:00:00') return '—';
    return date('d/m/Y H:i', strtotime($datetime));
}

function formatMoney($amount) {
    return number_format((float)($amount ?? 0), 0, ',', '.') . ' VNĐ';
}

// 6. BADGES TRẠNG THÁI (GIAO DIỆN)
function badgeTrangThaiNV($status) {
    if ($status === 'Đang làm') {
        return '<span class="badge badge-success">Đang làm</span>';
    }
    return '<span class="badge badge-danger">Đã nghỉ việc</span>';
}

function badgeTrangThaiHD($status) {
    if ($status === 'Hiệu lực') {
        return '<span class="badge badge-success">Hiệu lực</span>';
    } elseif ($status === 'Hết hiệu lực') {
        return '<span class="badge badge-danger">Hết hiệu lực</span>';
    }
    return '<span class="badge badge-warning">Chờ ký</span>';
}

function badgeTrangThaiLuong($status) {
    if ($status === 'Đã thanh toán') {
        return '<span class="badge badge-success">Đã thanh toán</span>';
    } elseif ($status === 'Đã duyệt') {
        return '<span class="badge badge-info">Đã duyệt</span>';
    } elseif ($status === 'Chờ duyệt') {
        return '<span class="badge badge-warning">Chờ duyệt</span>';
    }
    return '<span class="badge badge-danger">Chưa duyệt</span>';
}

function badgeDonPhep($status) {
    if ($status === 'Đã duyệt') {
        return '<span class="badge badge-success">Đã duyệt</span>';
    } elseif ($status === 'Từ chối' || $status === 'Không duyệt') {
        return '<span class="badge badge-danger">Từ chối</span>';
    }
    return '<span class="badge badge-warning">Chờ duyệt</span>';
}

// 7. THỐNG KÊ DASHBOARD TỔNG QUAN
function getTongQuan() {
    $db = getDB();
    $tq = [];
    
    // Tổng nhân viên
    $tq['tong_nv'] = (int)$db->query("SELECT COUNT(*) FROM nhan_vien")->fetchColumn();
    
    // Đang làm việc
    $tq['dang_lam'] = (int)$db->query("SELECT COUNT(*) FROM nhan_vien WHERE trang_thai = 'Đang làm'")->fetchColumn();
    
    // Đã nghỉ việc
    $tq['nghi_viec'] = (int)$db->query("SELECT COUNT(*) FROM nhan_vien WHERE trang_thai = 'Đã nghỉ việc'")->fetchColumn();
    
    // Quỹ lương tháng hiện tại
    $thang = date('n');
    $nam = date('Y');
    $stmt = $db->prepare("SELECT COALESCE(SUM(thuc_linh), 0) FROM bang_luong WHERE thang = ? AND nam = ?");
    $stmt->execute([$thang, $nam]);
    $tq['quy_luong'] = (float)$stmt->fetchColumn();
    
    // Hợp đồng hiệu lực
    $tq['hop_dong_hh'] = (int)$db->query("SELECT COUNT(*) FROM hop_dong WHERE trang_thai = 'Hiệu lực'")->fetchColumn();
    
    // Đơn nghỉ phép chờ duyệt
    $tq['don_cho_duyet'] = (int)$db->query("SELECT COUNT(*) FROM don_nghi_phep WHERE trang_thai = 'Chờ duyệt'")->fetchColumn();
    
    return $tq;
}

// 8. PHÂN TRANG (PAGINATION)
function paginate($total, $per_page, $page_num) {
    $total_pages = ceil($total / $per_page);
    $page_num = max(1, min($page_num, max(1, $total_pages)));
    $offset = ($page_num - 1) * $per_page;
    return [
        'per_page' => $per_page,
        'offset' => $offset,
        'total_pages' => $total_pages,
        'page' => $page_num
    ];
}
