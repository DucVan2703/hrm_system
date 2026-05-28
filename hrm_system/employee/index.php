<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
requireLogin();

$user = getCurrentUser();

// Redirect theo vai trò
if (isAdmin()) {
    header('Location: ../admin/index.php');
    exit();
}

$db = getDB();
$id_nv = $_SESSION['id_nhan_vien'];

// Thông tin nhân viên
$stmt = $db->prepare("
    SELECT nv.*, pb.ten_pb, cv.ten_cv
    FROM nhan_vien nv
    LEFT JOIN phong_ban pb ON nv.id_phong_ban = pb.id
    LEFT JOIN chuc_vu cv ON nv.id_chuc_vu = cv.id
    WHERE nv.id = ?
");
$stmt->execute([$id_nv]);
$nv = $stmt->fetch();

// Lương tháng hiện tại
$thang = date('n'); $nam = date('Y');
$stmt = $db->prepare("SELECT * FROM bang_luong WHERE id_nhan_vien = ? AND thang = ? AND nam = ?");
$stmt->execute([$id_nv, $thang, $nam]);
$luong_hien_tai = $stmt->fetch();

// Công tháng hiện tại
$stmt = $db->prepare("SELECT * FROM cham_cong WHERE id_nhan_vien = ? AND thang = ? AND nam = ?");
$stmt->execute([$id_nv, $thang, $nam]);
$cong = $stmt->fetch();

// Đơn nghỉ phép chờ duyệt
$stmt = $db->prepare("SELECT COUNT(*) FROM don_nghi_phep WHERE id_nhan_vien = ? AND trang_thai = 'Chờ duyệt'");
$stmt->execute([$id_nv]);
$don_cho = $stmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Trang Cá Nhân - <?= APP_NAME ?></title>
<link rel="stylesheet" href="../assets/css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<div class="layout">
  <!-- SIDEBAR -->
  <?php include 'includes/sidebar.php'; ?>

  <div class="main-content">
    <!-- HEADER -->
    <?php include 'includes/header.php'; ?>

    <div class="page-content">
      <!-- Welcome Banner -->
      <div class="card" style="background:linear-gradient(135deg,#4f46e5,#06b6d4);color:#fff;margin-bottom:24px;">
        <div class="card-body" style="display:flex;align-items:center;gap:20px;">
          <div class="avatar avatar-xl" style="background:rgba(255,255,255,.2);font-size:32px;">
            <?= mb_substr($nv['ho_ten'], 0, 1) ?>
          </div>
          <div>
            <h2 style="font-size:22px;font-weight:800;margin-bottom:4px;">Xin chào, <?= clean($nv['ho_ten']) ?>! 👋</h2>
            <p style="opacity:.85;"><?= clean($nv['ten_cv'] ?? '—') ?> • <?= clean($nv['ten_pb'] ?? '—') ?></p>
            <p style="opacity:.7;font-size:12px;margin-top:4px;">Mã NV: <?= clean($nv['ma_nv']) ?> • Vào làm: <?= formatDate($nv['ngay_vao_lam']) ?></p>
          </div>
          <div style="margin-left:auto;text-align:right;">
            <p style="opacity:.75;font-size:12px;">Lương cơ bản</p>
            <p style="font-size:24px;font-weight:800;"><?= formatMoney($nv['luong_co_ban']) ?></p>
          </div>
        </div>
      </div>

      <!-- Stats -->
      <div class="stats-grid" style="grid-template-columns:repeat(4,1fr);">
        <div class="stat-card info">
          <div class="stat-icon"><i class="fas fa-calendar-check"></i></div>
          <div class="stat-info">
            <div class="stat-value"><?= $cong['so_ngay_lam'] ?? 0 ?></div>
            <div class="stat-label">Ngày công T<?= $thang ?></div>
          </div>
        </div>
        <div class="stat-card success">
          <div class="stat-icon"><i class="fas fa-money-bill-wave"></i></div>
          <div class="stat-info">
            <div class="stat-value" style="font-size:18px;"><?= $luong_hien_tai ? formatMoney($luong_hien_tai['thuc_linh']) : '—' ?></div>
            <div class="stat-label">Thực lĩnh T<?= $thang ?></div>
          </div>
        </div>
        <div class="stat-card warning">
          <div class="stat-icon"><i class="fas fa-umbrella-beach"></i></div>
          <div class="stat-info">
            <div class="stat-value"><?= $cong['so_ngay_phep'] ?? 0 ?></div>
            <div class="stat-label">Ngày phép đã dùng</div>
          </div>
        </div>
        <div class="stat-card primary">
          <div class="stat-icon"><i class="fas fa-clock"></i></div>
          <div class="stat-info">
            <div class="stat-value"><?= $don_cho ?></div>
            <div class="stat-label">Đơn chờ duyệt</div>
          </div>
        </div>
      </div>

      <div class="grid-2">
        <!-- Thông tin cá nhân -->
        <div class="card">
          <div class="card-header">
            <span class="card-title"><i class="fas fa-id-card"></i> Thông tin cá nhân</span>
            <a href="pages/profile.php" class="btn btn-outline btn-sm"><i class="fas fa-edit"></i> Cập nhật</a>
          </div>
          <div class="card-body">
            <table style="width:100%;border-collapse:collapse;">
              <?php
              $info = [
                'Họ tên' => $nv['ho_ten'],
                'Ngày sinh' => formatDate($nv['ngay_sinh']),
                'Giới tính' => $nv['gioi_tinh'],
                'CCCD' => $nv['cccd'] ?: '—',
                'Điện thoại' => $nv['so_dien_thoai'] ?: '—',
                'Email' => $nv['email'] ?: '—',
                'Địa chỉ' => $nv['dia_chi'] ?: '—',
                'Phòng ban' => $nv['ten_pb'] ?: '—',
                'Chức vụ' => $nv['ten_cv'] ?: '—',
                'Ngày vào làm' => formatDate($nv['ngay_vao_lam']),
              ];
              foreach ($info as $k => $v): ?>
              <tr>
                <td style="padding:7px 0;color:var(--gray);font-size:13px;width:120px;"><?= $k ?></td>
                <td style="padding:7px 0;font-size:13px;font-weight:500;"><?= clean($v) ?></td>
              </tr>
              <?php endforeach; ?>
            </table>
          </div>
        </div>

        <!-- Bảng lương tháng hiện tại -->
        <div class="card">
          <div class="card-header">
            <span class="card-title"><i class="fas fa-file-invoice-dollar"></i> Lương tháng <?= $thang ?>/<?= $nam ?></span>
            <a href="pages/salary.php" class="btn btn-outline btn-sm"><i class="fas fa-history"></i> Lịch sử</a>
          </div>
          <div class="card-body">
            <?php if ($luong_hien_tai): ?>
            <table style="width:100%;border-collapse:collapse;">
              <?php
              $rows = [
                ['Lương cơ bản', $luong_hien_tai['luong_co_ban'], ''],
                ['Ngày công', $luong_hien_tai['so_ngay_lam'].'/'.$luong_hien_tai['so_ngay_chuan'].' ngày', ''],
                ['Lương theo công', $luong_hien_tai['luong_theo_cong'], ''],
                ['Phụ cấp ăn trưa', $luong_hien_tai['phu_cap_an_trua'], ''],
                ['Phụ cấp xăng xe', $luong_hien_tai['phu_cap_xang_xe'], ''],
                ['Phụ cấp khác', $luong_hien_tai['phu_cap_khac'], ''],
                ['Thưởng KPI', $luong_hien_tai['thuong_kpi'], 'success'],
                ['Phạt / khấu trừ', -($luong_hien_tai['phat_di_muon'] + $luong_hien_tai['khau_tru_khac']), 'danger'],
                ['BHXH (8%)', -$luong_hien_tai['bao_hiem_xa_hoi'], 'danger'],
                ['BHYT (1.5%)', -$luong_hien_tai['bao_hiem_y_te'], 'danger'],
                ['Thuế TNCN', -$luong_hien_tai['thue_tncn'], 'danger'],
              ];
              foreach ($rows as [$label, $val, $cls]): ?>
              <tr>
                <td style="padding:6px 0;color:var(--gray);font-size:13px;"><?= $label ?></td>
                <td style="padding:6px 0;font-size:13px;font-weight:500;text-align:right;color:<?= $cls === 'success' ? 'var(--success)' : ($cls === 'danger' ? 'var(--danger)' : 'var(--dark)') ?>;">
                  <?= is_numeric($val) ? formatMoney($val) : $val ?>
                </td>
              </tr>
              <?php endforeach; ?>
              <tr style="border-top:2px solid var(--border);">
                <td style="padding:10px 0;font-weight:800;font-size:14px;">THỰC LĨNH</td>
                <td style="padding:10px 0;font-weight:800;font-size:16px;color:var(--success);text-align:right;"><?= formatMoney($luong_hien_tai['thuc_linh']) ?></td>
              </tr>
            </table>
            <?php else: ?>
            <div class="empty-state">
              <i class="fas fa-file-invoice-dollar"></i>
              <h3>Chưa có dữ liệu lương</h3>
              <p>Bảng lương tháng <?= $thang ?>/<?= $nam ?> chưa được tạo</p>
            </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<script src="../assets/js/main.js"></script>
</body>
</html>
