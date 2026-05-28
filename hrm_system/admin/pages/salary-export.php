<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
requireAdmin();

$db = getDB();
$thang = (int)($_GET['thang'] ?? date('n'));
$nam = (int)($_GET['nam'] ?? date('Y'));

$stmt = $db->prepare("
    SELECT bl.*, nv.ma_nv, nv.ho_ten, pb.ten_pb, cv.ten_cv
    FROM bang_luong bl
    JOIN nhan_vien nv ON bl.id_nhan_vien=nv.id
    LEFT JOIN phong_ban pb ON nv.id_phong_ban=pb.id
    LEFT JOIN chuc_vu cv ON nv.id_chuc_vu=cv.id
    WHERE bl.thang=? AND bl.nam=?
    ORDER BY nv.ma_nv
");
$stmt->execute([$thang, $nam]);
$list = $stmt->fetchAll();

// Xuất CSV (tương thích Excel)
$filename = "Bang_luong_T{$thang}_{$nam}.csv";
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: no-cache, no-store, must-revalidate');

// BOM UTF-8 để Excel đọc được tiếng Việt
$output = fopen('php://output', 'w');
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Header
fputcsv($output, [
    'STT', 'Mã NV', 'Họ tên', 'Phòng ban', 'Chức vụ',
    'Ngày công', 'Ngày chuẩn', 'Lương cơ bản', 'Lương theo công',
    'PC ăn trưa', 'PC xăng xe', 'PC khác',
    'Thưởng KPI', 'Thưởng khác',
    'Phạt', 'Khấu trừ khác', 'BHXH', 'BHYT', 'Thuế TNCN',
    'Tổng thu nhập', 'Tổng khấu trừ', 'Thực lĩnh', 'Ký nhận', 'Trạng thái'
]);

// Data
$stt = 1;
$tongThucLinh = 0;
$tongThuNhap = 0;
$tongKhauTru = 0;

foreach ($list as $row) {
    fputcsv($output, [
        $stt++,
        $row['ma_nv'],
        $row['ho_ten'],
        $row['ten_pb'] ?? '',
        $row['ten_cv'] ?? '',
        $row['so_ngay_lam'],
        $row['so_ngay_chuan'],
        $row['luong_co_ban'],
        $row['luong_theo_cong'],
        $row['phu_cap_an_trua'],
        $row['phu_cap_xang_xe'],
        $row['phu_cap_khac'],
        $row['thuong_kpi'],
        $row['thuong_khac'],
        $row['phat_di_muon'],
        $row['khau_tru_khac'],
        $row['bao_hiem_xa_hoi'],
        $row['bao_hiem_y_te'],
        $row['thue_tncn'],
        $row['tong_thu_nhap'],
        $row['tong_khau_tru'],
        $row['thuc_linh'],
        '', // Ký nhận
        $row['trang_thai'],
    ]);
    $tongThucLinh += $row['thuc_linh'];
    $tongThuNhap += $row['tong_thu_nhap'];
    $tongKhauTru += $row['tong_khau_tru'];
}

// Footer
fputcsv($output, [
    '', '', '', '', 'TỔNG CỘNG',
    '', '', '', '', '', '', '', '', '', '', '', '', '', '',
    $tongThuNhap, $tongKhauTru, $tongThucLinh, '', ''
]);

// Thêm khoảng trống
fputcsv($output, []);
fputcsv($output, []);

// Hàng chức danh
fputcsv($output, [
    '', '', 'Người lập bảng', '', '', '', '', '', '', 'Kế toán trưởng', '', '', '', '', '', '', '', 'Giám đốc'
]);
fputcsv($output, [
    '', '', '(Ký, họ tên)', '', '', '', '', '', '', '(Ký, họ tên)', '', '', '', '', '', '', '', '(Ký, đóng dấu)'
]);

fclose($output);
exit();
