<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
requireHR();
$pageTitle = 'Quản lý chấm công';
$db = getDB();

$thang = (int)($_GET['thang'] ?? date('n'));
$nam = (int)($_GET['nam'] ?? date('Y'));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['type'] ?? '';
    if ($type === 'save') {
        $id_nv = (int)$_POST['id_nhan_vien'];
        $t = (int)$_POST['thang']; $n = (int)$_POST['nam'];
        $data = [
            'so_ngay_lam' => (float)$_POST['so_ngay_lam'],
            'so_ngay_nghi' => (int)$_POST['so_ngay_nghi'],
            'so_ngay_phep' => (int)$_POST['so_ngay_phep'],
            'so_ngay_vang' => (int)$_POST['so_ngay_vang'],
            'so_gio_tang_ca' => (float)$_POST['so_gio_tang_ca'],
            'ghi_chu' => sanitize($_POST['ghi_chu'] ?? ''),
        ];
        // Upsert
        $check = $db->prepare("SELECT id FROM cham_cong WHERE id_nhan_vien=? AND thang=? AND nam=?");
        $check->execute([$id_nv, $t, $n]);
        if ($check->fetch()) {
            $sql = "UPDATE cham_cong SET so_ngay_lam=:so_ngay_lam,so_ngay_nghi=:so_ngay_nghi,so_ngay_phep=:so_ngay_phep,so_ngay_vang=:so_ngay_vang,so_gio_tang_ca=:so_gio_tang_ca,ghi_chu=:ghi_chu WHERE id_nhan_vien=:id_nv AND thang=:t AND nam=:n";
        } else {
            $sql = "INSERT INTO cham_cong (id_nhan_vien,thang,nam,so_ngay_lam,so_ngay_nghi,so_ngay_phep,so_ngay_vang,so_gio_tang_ca,ghi_chu) VALUES (:id_nv,:t,:n,:so_ngay_lam,:so_ngay_nghi,:so_ngay_phep,:so_ngay_vang,:so_gio_tang_ca,:ghi_chu)";
        }
        $db->prepare($sql)->execute(array_merge($data, ['id_nv'=>$id_nv,'t'=>$t,'n'=>$n]));
        setFlash('success','Cập nhật chấm công thành công!');
        header("Location: timekeeping.php?thang=$t&nam=$n"); exit();
    }
}

// Danh sách chấm công
$stmt = $db->prepare("
    SELECT nv.id, nv.ma_nv, nv.ho_ten, nv.luong_co_ban,
           pb.ten_pb, cv.ten_cv,
           cc.id as cc_id, cc.so_ngay_lam, cc.so_ngay_nghi, cc.so_ngay_phep, cc.so_ngay_vang, cc.so_gio_tang_ca, cc.ghi_chu
    FROM nhan_vien nv
    LEFT JOIN phong_ban pb ON nv.id_phong_ban=pb.id
    LEFT JOIN chuc_vu cv ON nv.id_chuc_vu=cv.id
    LEFT JOIN cham_cong cc ON nv.id=cc.id_nhan_vien AND cc.thang=? AND cc.nam=?
    WHERE nv.trang_thai='Đang làm'
    ORDER BY nv.ma_nv
");
$stmt->execute([$thang, $nam]);
$list = $stmt->fetchAll();

$flash = getFlash();
$months = range(1, 12);
$years = range(date('Y') - 2, date('Y') + 1);
?>
<!DOCTYPE html>
<html lang="vi">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Chấm công - <?= APP_NAME ?></title>
<link rel="stylesheet" href="../../assets/css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<div class="layout">
  <?php include '../includes/sidebar.php'; ?>
  <div class="main-content">
    <?php include '../includes/header.php'; ?>
    <div class="page-content">
      <div class="page-header">
        <div><h1>Quản lý chấm công</h1><p>Theo dõi ngày công nhân viên theo tháng</p></div>
      </div>

      <?php if ($flash): ?>
      <div class="alert alert-<?= $flash['type'] ?>"><i class="fas fa-info-circle"></i> <?= clean($flash['message']) ?></div>
      <?php endif; ?>

      <!-- Filter -->
      <div class="card" style="margin-bottom:16px;">
        <div class="card-body" style="padding:14px 20px;">
          <form method="GET" style="display:flex;gap:10px;align-items:center;">
            <label style="font-weight:600;font-size:13px;">Tháng:</label>
            <select name="thang" class="filter-input">
              <?php foreach ($months as $m): ?>
              <option value="<?= $m ?>" <?= $m==$thang?'selected':'' ?>>Tháng <?= $m ?></option>
              <?php endforeach; ?>
            </select>
            <select name="nam" class="filter-input">
              <?php foreach ($years as $y): ?>
              <option value="<?= $y ?>" <?= $y==$nam?'selected':'' ?>><?= $y ?></option>
              <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Xem</button>
          </form>
        </div>
      </div>

      <div class="card">
        <div class="card-header">
          <span class="card-title"><i class="fas fa-clock"></i> Bảng chấm công Tháng <?= $thang ?>/<?= $nam ?></span>
          <span style="font-size:12px;color:var(--gray);">Ngày chuẩn: <b><?= NGAY_CHUAN ?></b> ngày</span>
        </div>
        <div class="table-responsive">
          <table class="table">
            <thead>
              <tr>
                <th>Nhân viên</th>
                <th>Phòng ban</th>
                <th style="text-align:center;">Ngày làm</th>
                <th style="text-align:center;">Ngày nghỉ</th>
                <th style="text-align:center;">Ngày phép</th>
                <th style="text-align:center;">Vắng mặt</th>
                <th style="text-align:center;">Tăng ca (h)</th>
                <th style="text-align:center;">Thao tác</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($list as $row): ?>
              <tr>
                <td>
                  <div class="nv-info">
                    <div class="avatar"><?= mb_substr($row['ho_ten'],0,1) ?></div>
                    <div><h4><?= clean($row['ho_ten']) ?></h4><p><?= clean($row['ma_nv']) ?></p></div>
                  </div>
                </td>
                <td><?= clean($row['ten_pb'] ?? '—') ?></td>
                <td style="text-align:center;">
                  <?php if ($row['cc_id']): ?>
                  <span style="font-weight:700;color:<?= $row['so_ngay_lam'] >= NGAY_CHUAN ? 'var(--success)' : 'var(--warning)' ?>;"><?= $row['so_ngay_lam'] ?></span>
                  <?php else: ?><span style="color:var(--gray);">—</span><?php endif; ?>
                </td>
                <td style="text-align:center;"><?= $row['cc_id'] ? $row['so_ngay_nghi'] : '—' ?></td>
                <td style="text-align:center;"><?= $row['cc_id'] ? $row['so_ngay_phep'] : '—' ?></td>
                <td style="text-align:center;">
                  <?php if ($row['cc_id']): ?>
                  <span style="color:<?= $row['so_ngay_vang'] > 0 ? 'var(--danger)' : 'inherit' ?>;"><?= $row['so_ngay_vang'] ?></span>
                  <?php else: ?>—<?php endif; ?>
                </td>
                <td style="text-align:center;"><?= $row['cc_id'] ? $row['so_gio_tang_ca'] : '—' ?></td>
                <td style="text-align:center;">
                  <button class="btn btn-sm btn-info btn-icon" data-tooltip="<?= $row['cc_id'] ? 'Sửa công' : 'Nhập công' ?>" onclick='openChamCong(<?= json_encode($row,JSON_UNESCAPED_UNICODE) ?>)'><i class="fas fa-<?= $row['cc_id'] ? 'edit' : 'plus' ?>"></i></button>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
            <tfoot>
              <tr style="background:var(--bg);font-weight:700;">
                <td colspan="2">TỔNG</td>
                <td style="text-align:center;"><?= array_sum(array_column($list, 'so_ngay_lam')) ?></td>
                <td style="text-align:center;"><?= array_sum(array_column($list, 'so_ngay_nghi')) ?></td>
                <td style="text-align:center;"><?= array_sum(array_column($list, 'so_ngay_phep')) ?></td>
                <td style="text-align:center;"><?= array_sum(array_column($list, 'so_ngay_vang')) ?></td>
                <td style="text-align:center;"><?= array_sum(array_column($list, 'so_gio_tang_ca')) ?></td>
                <td></td>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal nhập công -->
<div class="modal-overlay" id="modalCC" style="display:none;">
  <div class="modal">
    <div class="modal-header">
      <span class="modal-title" id="modalCCTitle"><i class="fas fa-clock"></i> Nhập chấm công</span>
      <button class="modal-close" onclick="closeModal('modalCC')"><i class="fas fa-times"></i></button>
    </div>
    <form method="POST">
      <input type="hidden" name="type" value="save">
      <input type="hidden" name="id_nhan_vien" id="cc_nv_id">
      <input type="hidden" name="thang" value="<?= $thang ?>">
      <input type="hidden" name="nam" value="<?= $nam ?>">
      <div class="modal-body">
        <div class="form-row col-2">
          <div class="form-group">
            <label class="form-label required">Số ngày làm việc</label>
            <input type="number" name="so_ngay_lam" id="cc_ngaylam" class="form-control" step="0.5" min="0" max="31">
          </div>
          <div class="form-group">
            <label class="form-label">Ngày nghỉ</label>
            <input type="number" name="so_ngay_nghi" id="cc_ngaynghi" class="form-control" min="0" max="31" value="0">
          </div>
        </div>
        <div class="form-row col-3">
          <div class="form-group">
            <label class="form-label">Ngày phép</label>
            <input type="number" name="so_ngay_phep" id="cc_phep" class="form-control" min="0" value="0">
          </div>
          <div class="form-group">
            <label class="form-label">Ngày vắng</label>
            <input type="number" name="so_ngay_vang" id="cc_vang" class="form-control" min="0" value="0">
          </div>
          <div class="form-group">
            <label class="form-label">Giờ tăng ca</label>
            <input type="number" name="so_gio_tang_ca" id="cc_tangca" class="form-control" step="0.5" min="0" value="0">
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Ghi chú</label>
          <textarea name="ghi_chu" id="cc_ghichu" class="form-control"></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="closeModal('modalCC')">Hủy</button>
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Lưu chấm công</button>
      </div>
    </form>
  </div>
</div>

<script src="../../assets/js/main.js"></script>
<script>
function openChamCong(row) {
  document.getElementById('modalCCTitle').innerHTML = '<i class="fas fa-clock"></i> Chấm công: ' + row.ho_ten + ' - T<?= $thang ?>/<?= $nam ?>';
  document.getElementById('cc_nv_id').value = row.id;
  document.getElementById('cc_ngaylam').value = row.so_ngay_lam || <?= NGAY_CHUAN ?>;
  document.getElementById('cc_ngaynghi').value = row.so_ngay_nghi || 0;
  document.getElementById('cc_phep').value = row.so_ngay_phep || 0;
  document.getElementById('cc_vang').value = row.so_ngay_vang || 0;
  document.getElementById('cc_tangca').value = row.so_gio_tang_ca || 0;
  document.getElementById('cc_ghichu').value = row.ghi_chu || '';
  openModal('modalCC');
}
</script>
</body></html>
