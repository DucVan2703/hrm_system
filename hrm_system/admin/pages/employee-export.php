<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
requireHR();

$db = getDB();

// Lấy các tham số lọc từ URL (nếu có)
$search = sanitize($_GET['search'] ?? '');
$pb_filter = (int)($_GET['pb'] ?? 0);
$tt_filter = sanitize($_GET['tt'] ?? '');

$where = []; $params = [];
if ($search) { $where[] = "(nv.ho_ten LIKE ? OR nv.ma_nv LIKE ? OR nv.email LIKE ?)"; $params = array_merge($params, ["%$search%", "%$search%", "%$search%"]); }
if ($pb_filter) { $where[] = "nv.id_phong_ban = ?"; $params[] = $pb_filter; }
if ($tt_filter) { $where[] = "nv.trang_thai = ?"; $params[] = $tt_filter; }
$whereStr = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$sql = "SELECT nv.*, pb.ten_pb, cv.ten_cv FROM nhan_vien nv LEFT JOIN phong_ban pb ON nv.id_phong_ban=pb.id LEFT JOIN chuc_vu cv ON nv.id_chuc_vu=cv.id $whereStr ORDER BY nv.id DESC";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$list = $stmt->fetchAll();

// Xuất CSV (tương thích Excel)
$filename = "Danh_sach_nhan_vien_" . date('Ymd_His') . ".csv";
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: no-cache, no-store, must-revalidate');

// BOM UTF-8 để Excel đọc được tiếng Việt
$output = fopen('php://output', 'w');
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Header
fputcsv($output, [
    'STT', 'Mã NV', 'Họ tên', 'Ngày sinh', 'Giới tính', 'CCCD', 
    'Điện thoại', 'Email', 'Địa chỉ', 'Phòng ban', 'Chức vụ', 
    'Lương cơ bản', 'Ngày vào làm', 'Trạng thái'
]);

// Data
$stt = 1;
foreach ($list as $row) {
    fputcsv($output, [
        $stt++,
        $row['ma_nv'],
        $row['ho_ten'],
        formatDate($row['ngay_sinh']),
        $row['gioi_tinh'],
        $row['cccd'],
        $row['so_dien_thoai'],
        $row['email'],
        $row['dia_chi'],
        $row['ten_pb'] ?? '',
        $row['ten_cv'] ?? '',
        $row['luong_co_ban'],
        formatDate($row['ngay_vao_lam']),
        $row['trang_thai'],
    ]);
}

fclose($output);
exit();
