<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
requireLogin();
if (isAdmin()) { header('Location: '.BASE_URL.'/admin/index.php'); exit(); }

$pageTitle = 'Đơn nghỉ phép';
$db = getDB();
$id_nv = $_SESSION['id_nhan_vien'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['type'] ?? '';
    if ($type === 'gui_don') {
        $ngay_bd = $_POST['ngay_bat_dau'];
        $ngay_kt = $_POST['ngay_ket_thuc'];
        $so_ngay = max(1, (int)((strtotime($ngay_kt)-strtotime($ngay_bd))/86400)+1);
        $ly_do = sanitize($_POST['ly_do'] ?? '');
        $loai = sanitize($_POST['loai_phep'] ?? 'Phép năm');
        $db->prepare("INSERT INTO don_nghi_phep (id_nhan_vien,ngay_bat_dau,ngay_ket_thuc,so_ngay,ly_do,loai_phep) VALUES (?,?,?,?,?,?)")->execute([$id_nv,$ngay_bd,$ngay_kt,$so_ngay,$ly_do,$loai]);
        setFlash('success','Đã gửi đơn nghỉ phép! Vui lòng chờ phê duyệt.');
        header('Location: leave.php'); exit();
    }
}

$list = $db->prepare("SELECT * FROM don_nghi_phep WHERE id_nhan_vien=? ORDER BY ngay_gui DESC");
$list->execute([$id_nv]); $list = $list->fetchAll();
$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="vi">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Nghỉ phép - <?= APP_NAME ?></title>
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
        <div><h1>Đơn nghỉ phép</h1><p>Xin nghỉ phép và theo dõi trạng thái</p></div>
        <button class="btn btn-primary" onclick="openModal('modalDon')"><i class="fas fa-plus"></i> Xin nghỉ phép</button>
      </div>

      <?php if ($flash): ?><div class="alert alert-<?= $flash['type'] ?>"><i class="fas fa-info-circle"></i> <?= clean($flash['message']) ?></div><?php endif; ?>

      <!-- Thống kê -->
      <div class="stats-grid" style="grid-template-columns:repeat(3,1fr);margin-bottom:20px;">
        <div class="stat-card warning">
          <div class="stat-icon"><i class="fas fa-clock"></i></div>
          <div class="stat-info">
            <div class="stat-value"><?= count(array_filter($list,fn($d)=>$d['trang_thai']==='Chờ duyệt')) ?></div>
            <div class="stat-label">Đơn chờ duyệt</div>
          </div>
        </div>
        <div class="stat-card success">
          <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
          <div class="stat-info">
            <div class="stat-value"><?= count(array_filter($list,fn($d)=>$d['trang_thai']==='Đã duyệt')) ?></div>
            <div class="stat-label">Đơn đã duyệt</div>
          </div>
        </div>
        <div class="stat-card danger">
          <div class="stat-icon"><i class="fas fa-times-circle"></i></div>
          <div class="stat-info">
            <div class="stat-value"><?= count(array_filter($list,fn($d)=>$d['trang_thai']==='Từ chối')) ?></div>
            <div class="stat-label">Đơn từ chối</div>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-header"><span class="card-title"><i class="fas fa-calendar-minus"></i> Lịch sử đơn nghỉ phép</span></div>
        <div class="table-responsive">
          <table class="table">
            <thead><tr><th>Loại phép</th><th>Từ ngày</th><th>Đến ngày</th><th>Số ngày</th><th>Lý do</th><th>Trạng thái</th><th>Ngày gửi</th></tr></thead>
            <tbody>
              <?php if (empty($list)): ?>
              <tr><td colspan="7"><div class="empty-state"><i class="fas fa-calendar-minus"></i><h3>Chưa có đơn nghỉ phép nào</h3></div></td></tr>
              <?php else: foreach ($list as $d): ?>
              <tr>
                <td><?= clean($d['loai_phep']) ?></td>
                <td><?= formatDate($d['ngay_bat_dau']) ?></td>
                <td><?= formatDate($d['ngay_ket_thuc']) ?></td>
                <td style="text-align:center;"><b><?= $d['so_ngay'] ?> ngày</b></td>
                <td><?= clean($d['ly_do']) ?></td>
                <td><?= badgeDonPhep($d['trang_thai']) ?></td>
                <td style="font-size:12px;"><?= formatDateTime($d['ngay_gui']) ?></td>
              </tr>
              <?php endforeach; endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="modal-overlay" id="modalDon" style="display:none;">
  <div class="modal">
    <div class="modal-header">
      <span class="modal-title"><i class="fas fa-calendar-plus"></i> Xin nghỉ phép</span>
      <button class="modal-close" onclick="closeModal('modalDon')"><i class="fas fa-times"></i></button>
    </div>
    <form method="POST">
      <input type="hidden" name="type" value="gui_don">
      <div class="modal-body">
        <div class="form-group">
          <label class="form-label required">Loại phép</label>
          <select name="loai_phep" class="form-control">
            <option>Phép năm</option><option>Phép ốm</option><option>Phép cưới</option>
            <option>Phép tang</option><option>Phép không lương</option>
          </select>
        </div>
        <div class="form-row col-2">
          <div class="form-group">
            <label class="form-label required">Từ ngày</label>
            <input type="date" name="ngay_bat_dau" id="don_start" class="form-control" required min="<?= date('Y-m-d') ?>">
          </div>
          <div class="form-group">
            <label class="form-label required">Đến ngày</label>
            <input type="date" name="ngay_ket_thuc" id="don_end" class="form-control" required min="<?= date('Y-m-d') ?>">
          </div>
        </div>
        <div class="form-group">
          <label class="form-label required">Lý do</label>
          <textarea name="ly_do" class="form-control" required placeholder="Nêu rõ lý do xin nghỉ phép..."></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="closeModal('modalDon')">Hủy</button>
        <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Gửi đơn</button>
      </div>
    </form>
  </div>
</div>

<script src="../../assets/js/main.js"></script>
<script>
document.getElementById('don_start').addEventListener('change', function() {
  document.getElementById('don_end').min = this.value;
});
</script>
</body></html>
