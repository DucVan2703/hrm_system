<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
requireHR();
$pageTitle = 'Đơn nghỉ phép';
$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['type'] ?? '';
    if ($type === 'duyet') {
        $id = (int)$_POST['id'];
        $tt = sanitize($_POST['trang_thai']);
        $ghichu = sanitize($_POST['ghi_chu'] ?? '');
        $db->prepare("UPDATE don_nghi_phep SET trang_thai=?,ghi_chu_duyet=?,nguoi_duyet=?,ngay_duyet=NOW() WHERE id=?")->execute([$tt,$ghichu,$_SESSION['user_id'],$id]);
        setFlash('success', $tt === 'Đã duyệt' ? 'Đã duyệt đơn nghỉ phép!' : 'Đã từ chối đơn!');
        header('Location: leave-requests.php'); exit();
    }
}

$tt_filter = sanitize($_GET['tt'] ?? '');
$where = $tt_filter ? "WHERE d.trang_thai='$tt_filter'" : '';
$list = $db->query("
    SELECT d.*, nv.ho_ten, nv.ma_nv, pb.ten_pb
    FROM don_nghi_phep d
    LEFT JOIN nhan_vien nv ON d.id_nhan_vien=nv.id
    LEFT JOIN phong_ban pb ON nv.id_phong_ban=pb.id
    $where
    ORDER BY d.ngay_gui DESC
")->fetchAll();

$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="vi">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Đơn nghỉ phép - <?= APP_NAME ?></title>
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
        <div><h1>Đơn nghỉ phép</h1><p>Quản lý và duyệt đơn xin nghỉ phép của nhân viên</p></div>
      </div>

      <?php if ($flash): ?>
      <div class="alert alert-<?= $flash['type'] ?>"><i class="fas fa-info-circle"></i> <?= clean($flash['message']) ?></div>
      <?php endif; ?>

      <div class="card" style="margin-bottom:16px;">
        <div class="card-body" style="padding:14px 20px;">
          <div style="display:flex;gap:8px;">
            <a href="leave-requests.php" class="btn <?= !$tt_filter ? 'btn-primary' : 'btn-outline' ?>">Tất cả</a>
            <a href="?tt=Chờ duyệt" class="btn <?= $tt_filter==='Chờ duyệt' ? 'btn-warning' : 'btn-outline' ?>">⏳ Chờ duyệt</a>
            <a href="?tt=Đã duyệt" class="btn <?= $tt_filter==='Đã duyệt' ? 'btn-success' : 'btn-outline' ?>">✅ Đã duyệt</a>
            <a href="?tt=Từ chối" class="btn <?= $tt_filter==='Từ chối' ? 'btn-danger' : 'btn-outline' ?>">❌ Từ chối</a>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="table-responsive">
          <table class="table">
            <thead><tr><th>Nhân viên</th><th>Loại phép</th><th>Từ ngày</th><th>Đến ngày</th><th>Số ngày</th><th>Lý do</th><th>Ngày gửi</th><th>Trạng thái</th><th>Thao tác</th></tr></thead>
            <tbody>
              <?php if (empty($list)): ?>
              <tr><td colspan="9"><div class="empty-state"><i class="fas fa-calendar-minus"></i><h3>Không có đơn nào</h3></div></td></tr>
              <?php else: foreach ($list as $d): ?>
              <tr>
                <td>
                  <div class="nv-info">
                    <div class="avatar avatar-sm"><?= mb_substr($d['ho_ten'],0,1) ?></div>
                    <div><h4><?= clean($d['ho_ten']) ?></h4><p><?= clean($d['ten_pb'] ?? '') ?></p></div>
                  </div>
                </td>
                <td><?= clean($d['loai_phep']) ?></td>
                <td><?= formatDate($d['ngay_bat_dau']) ?></td>
                <td><?= formatDate($d['ngay_ket_thuc']) ?></td>
                <td style="text-align:center;"><b><?= $d['so_ngay'] ?> ngày</b></td>
                <td style="max-width:150px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="<?= clean($d['ly_do']) ?>"><?= clean($d['ly_do']) ?></td>
                <td><?= formatDateTime($d['ngay_gui']) ?></td>
                <td><?= badgeDonPhep($d['trang_thai']) ?></td>
                <td>
                  <?php if ($d['trang_thai'] === 'Chờ duyệt'): ?>
                  <button class="btn btn-sm btn-success" onclick='openDuyet(<?= json_encode($d,JSON_UNESCAPED_UNICODE) ?>)'><i class="fas fa-check"></i> Duyệt/Từ chối</button>
                  <?php else: ?>
                  <span style="font-size:12px;color:var(--gray);"><?= formatDate($d['ngay_duyet']) ?></span>
                  <?php endif; ?>
                </td>
              </tr>
              <?php endforeach; endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="modal-overlay" id="modalDuyet" style="display:none;">
  <div class="modal">
    <div class="modal-header">
      <span class="modal-title"><i class="fas fa-calendar-check"></i> Xử lý đơn nghỉ phép</span>
      <button class="modal-close" onclick="closeModal('modalDuyet')"><i class="fas fa-times"></i></button>
    </div>
    <form method="POST">
      <input type="hidden" name="type" value="duyet">
      <input type="hidden" name="id" id="duyet_id">
      <div class="modal-body">
        <div id="duyet_info" style="background:var(--bg);border-radius:8px;padding:12px;margin-bottom:16px;font-size:13px;"></div>
        <div class="form-group">
          <label class="form-label required">Quyết định</label>
          <select name="trang_thai" id="duyet_tt" class="form-control">
            <option value="Đã duyệt">✅ Duyệt đơn</option>
            <option value="Từ chối">❌ Từ chối</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Ghi chú</label>
          <textarea name="ghi_chu" class="form-control" placeholder="Lý do từ chối hoặc ghi chú..."></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="closeModal('modalDuyet')">Hủy</button>
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Xác nhận</button>
      </div>
    </form>
  </div>
</div>

<script src="../../assets/js/main.js"></script>
<script>
function openDuyet(d) {
  document.getElementById('duyet_id').value = d.id;
  document.getElementById('duyet_info').innerHTML = `
    <p><b>Nhân viên:</b> ${d.ho_ten} (${d.ma_nv})</p>
    <p><b>Loại phép:</b> ${d.loai_phep}</p>
    <p><b>Thời gian:</b> ${d.ngay_bat_dau} đến ${d.ngay_ket_thuc} (${d.so_ngay} ngày)</p>
    <p><b>Lý do:</b> ${d.ly_do}</p>
  `;
  openModal('modalDuyet');
}
</script>
</body></html>
