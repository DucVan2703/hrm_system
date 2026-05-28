<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
requireAdmin();
$pageTitle = 'Bảng lương';
$db = getDB();

$thang = (int)($_GET['thang'] ?? date('n'));
$nam = (int)($_GET['nam'] ?? date('Y'));

$stmt = $db->prepare("
    SELECT bl.*, nv.ma_nv, nv.ho_ten, nv.chu_ky, pb.ten_pb, cv.ten_cv
    FROM bang_luong bl
    JOIN nhan_vien nv ON bl.id_nhan_vien=nv.id
    LEFT JOIN phong_ban pb ON nv.id_phong_ban=pb.id
    LEFT JOIN chuc_vu cv ON nv.id_chuc_vu=cv.id
    WHERE bl.thang=? AND bl.nam=?
    ORDER BY cv.id ASC, nv.ma_nv ASC
");
$stmt->execute([$thang,$nam]);
$list = $stmt->fetchAll();

$tongQuyLuong = array_sum(array_column($list,'thuc_linh'));
$months = range(1,12); $years = range(date('Y')-2, date('Y')+1);
?>
<!DOCTYPE html>
<html lang="vi">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Bảng lương - <?= APP_NAME ?></title>
<link rel="stylesheet" href="../../assets/css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
@media print {
  .no-print { display:none!important; }
  .main-content { margin-left:0!important; }
}
</style>
</head>
<body>
<div class="layout">
  <div class="no-print"><?php include '../includes/sidebar.php'; ?></div>
  <div class="main-content">
    <div class="no-print"><?php include '../includes/header.php'; ?></div>
    <div class="page-content">
      <div class="page-header no-print">
        <div><h1>Bảng lương cán bộ giảng viên</h1><p>Xem và in bảng lương toàn trường</p></div>
        <div class="page-actions">
          <button class="btn btn-warning" onclick="window.print()"><i class="fas fa-print"></i> In bảng lương</button>
          <a href="salary-export.php?thang=<?= $thang ?>&nam=<?= $nam ?>" class="btn btn-success"><i class="fas fa-file-excel"></i> Xuất Excel</a>
        </div>
      </div>

      <!-- Filter -->
      <div class="card no-print" style="margin-bottom:16px;">
        <div class="card-body" style="padding:14px 20px;">
          <form method="GET" style="display:flex;gap:8px;align-items:center;">
            <select name="thang" class="filter-input">
              <?php foreach ($months as $m): ?><option value="<?= $m ?>" <?= $m==$thang?'selected':'' ?>>Tháng <?= $m ?></option><?php endforeach; ?>
            </select>
            <select name="nam" class="filter-input">
              <?php foreach ($years as $y): ?><option value="<?= $y ?>" <?= $y==$nam?'selected':'' ?>><?= $y ?></option><?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Xem</button>
          </form>
        </div>
      </div>

      <!-- In đầu trang -->
      <div style="text-align:center;margin-bottom:16px;display:none;" class="print-header">
        <h2 style="font-size:18px;font-weight:800;"><?= mb_strtoupper(getSetting('APP_NAME', APP_NAME)) ?></h2>
        <h3 style="font-size:15px;font-weight:700;margin-top:4px;">BẢNG LƯƠNG CÁN BỘ GIẢNG VIÊN THÁNG <?= $thang ?>/<?= $nam ?></h3>
      </div>
      <style>.print-header { display:none; } @media print { .print-header { display:block!important; } }</style>

      <!-- Stats -->
      <div class="stats-grid no-print" style="grid-template-columns:repeat(4,1fr);margin-bottom:16px;">
        <div class="stat-card primary">
          <div class="stat-icon"><i class="fas fa-users"></i></div>
          <div class="stat-info"><div class="stat-value"><?= count($list) ?></div><div class="stat-label">Nhân viên</div></div>
        </div>
        <div class="stat-card success">
          <div class="stat-icon"><i class="fas fa-money-bill-wave"></i></div>
          <div class="stat-info"><div class="stat-value" style="font-size:16px;"><?= formatMoney($tongQuyLuong) ?></div><div class="stat-label">Tổng thực lĩnh</div></div>
        </div>
        <div class="stat-card info">
          <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
          <div class="stat-info"><div class="stat-value"><?= count(array_filter($list, fn($r) => $r['trang_thai']==='Đã duyệt' || $r['trang_thai']==='Đã thanh toán')) ?></div><div class="stat-label">Đã duyệt</div></div>
        </div>
        <div class="stat-card warning">
          <div class="stat-icon"><i class="fas fa-clock"></i></div>
          <div class="stat-info"><div class="stat-value"><?= count(array_filter($list, fn($r) => $r['trang_thai']==='Nháp')) ?></div><div class="stat-label">Chờ duyệt</div></div>
        </div>
      </div>

      <div class="card">
        <div class="card-header">
          <span class="card-title"><i class="fas fa-file-invoice-dollar"></i> Bảng lương cán bộ giảng viên tháng <?= $thang ?>/<?= $nam ?></span>
        </div>
        <div class="table-responsive">
          <table class="table" id="bangLuongTable" style="font-size:12px;">
            <thead>
              <tr>
                <th>STT</th>
                <th>Mã NV</th>
                <th>Họ tên</th>
                <th>Phòng ban</th>
                <th>Ngày công</th>
                <th>Lương cơ bản</th>
                <th>Lương theo công</th>
                <th>Phụ cấp</th>
                <th>Thưởng</th>
                <th>Khấu trừ</th>
                <th>BHXH+BHYT</th>
                <th>Thuế TNCN</th>
                <th style="color:var(--success);">Thực lĩnh</th>
                <th>Ký nhận</th>
                <th class="no-print">Trạng thái</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($list)): ?>
              <tr><td colspan="14"><div class="empty-state"><i class="fas fa-file-invoice-dollar"></i><h3>Chưa có dữ liệu lương</h3><p>Vào trang Tính lương để tạo bảng lương</p></div></td></tr>
              <?php else: $stt=1; foreach ($list as $row): ?>
              <tr>
                <td><?= $stt++ ?></td>
                <td><?= clean($row['ma_nv']) ?></td>
                <td><b><?= clean($row['ho_ten']) ?></b></td>
                <td><?= clean($row['ten_pb'] ?? '—') ?></td>
                <td style="text-align:center;"><?= $row['so_ngay_lam'] ?>/<?= $row['so_ngay_chuan'] ?></td>
                <td><?= formatMoney($row['luong_co_ban']) ?></td>
                <td><?= formatMoney($row['luong_theo_cong']) ?></td>
                <td><?= formatMoney($row['phu_cap_an_trua'] + $row['phu_cap_xang_xe'] + $row['phu_cap_khac']) ?></td>
                <td><?= formatMoney($row['thuong_kpi'] + $row['thuong_khac']) ?></td>
                <td style="color:var(--danger);"><?= formatMoney($row['phat_di_muon'] + $row['khau_tru_khac']) ?></td>
                <td style="color:var(--danger);"><?= formatMoney($row['bao_hiem_xa_hoi'] + $row['bao_hiem_y_te']) ?></td>
                <td style="color:var(--danger);"><?= formatMoney($row['thue_tncn']) ?></td>
                <td><b style="color:var(--success);"><?= formatMoney($row['thuc_linh']) ?></b></td>
                <td style="text-align:center; min-width:100px;">
                  <?php if ($row['chu_ky']): ?>
                    <img src="<?= BASE_URL . '/' . $row['chu_ky'] ?>" style="max-height:40px; filter:contrast(1.1); mix-blend-mode:multiply;">
                  <?php endif; ?>
                </td>
                <td class="no-print"><?= badgeTrangThaiLuong($row['trang_thai']) ?></td>
              </tr>
              <?php endforeach; endif; ?>
            </tbody>
            <tfoot>
              <tr style="font-weight:800;background:rgba(79,70,229,.06);">
                <td colspan="6" style="text-align:right;font-weight:800;">TỔNG CỘNG</td>
                <td><?= formatMoney(array_sum(array_column($list,'luong_theo_cong'))) ?></td>
                <td><?= formatMoney(array_sum(array_column($list,'phu_cap_an_trua'))+array_sum(array_column($list,'phu_cap_xang_xe'))+array_sum(array_column($list,'phu_cap_khac'))) ?></td>
                <td><?= formatMoney(array_sum(array_column($list,'thuong_kpi'))+array_sum(array_column($list,'thuong_khac'))) ?></td>
                <td><?= formatMoney(array_sum(array_column($list,'phat_di_muon'))+array_sum(array_column($list,'khau_tru_khac'))) ?></td>
                <td><?= formatMoney(array_sum(array_column($list,'bao_hiem_xa_hoi'))+array_sum(array_column($list,'bao_hiem_y_te'))) ?></td>
                <td><?= formatMoney(array_sum(array_column($list,'thue_tncn'))) ?></td>
                <td style="color:var(--success);font-size:14px;"><?= formatMoney($tongQuyLuong) ?></td>
                <td></td>
                <td class="no-print"></td>
              </tr>
            </tfoot>
          </table>
        </div>
        <div style="padding:16px 20px;border-top:1px solid var(--border);display:flex;justify-content:space-between;align-items:center;" class="no-print">
          <span style="font-size:13px;color:var(--gray);">Tổng quỹ lương: <b style="font-size:16px;color:var(--success);"><?= formatMoney($tongQuyLuong) ?></b></span>
        </div>
        <!-- Print footer -->
        <div style="display:none;margin-top:40px;padding:0 20px;" class="print-footer">
          <div style="display:grid;grid-template-columns: 1fr 1fr 1fr;text-align:center;">
            <div>
              <p style="font-weight:700;margin-bottom:60px;">Người lập bảng</p>
              <p style="font-size:12px;color:#666;">(Ký, họ tên)</p>
            </div>
            <div>
              <p style="font-weight:700;margin-bottom:60px;">Kế toán trưởng</p>
              <p style="font-size:12px;color:#666;">(Ký, họ tên)</p>
            </div>
            <div>
              <p style="font-weight:700;margin-bottom:60px;">Giám đốc</p>
              <p style="font-size:12px;color:#666;">(Ký, đóng dấu)</p>
            </div>
          </div>
        </div>
        <style>@media print { .print-footer { display:block!important; } }</style>
      </div>
    </div>
  </div>
</div>
<script src="../../assets/js/main.js"></script>
</body></html>
