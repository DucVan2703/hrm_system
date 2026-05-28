<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
requireHR();
$pageTitle = 'Quản lý hợp đồng lao động';
$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['type'] ?? '';
    if ($type === 'save') {
        $id = (int)($_POST['id'] ?? 0);
        $data = [
            'id_nhan_vien' => (int)$_POST['id_nhan_vien'],
            'loai_hop_dong' => sanitize($_POST['loai_hop_dong'] ?? ''),
            'ngay_bat_dau' => $_POST['ngay_bat_dau'],
            'ngay_ket_thuc' => $_POST['ngay_ket_thuc'] ?: null,
            'luong_hop_dong' => (float)$_POST['luong_hop_dong'],
            'trang_thai' => sanitize($_POST['trang_thai'] ?? 'Đang hiệu lực'),
            'ghi_chu' => sanitize($_POST['ghi_chu'] ?? ''),
        ];
        if ($id > 0) {
            $db->prepare("UPDATE hop_dong SET id_nhan_vien=:id_nhan_vien,loai_hop_dong=:loai_hop_dong,ngay_bat_dau=:ngay_bat_dau,ngay_ket_thuc=:ngay_ket_thuc,luong_hop_dong=:luong_hop_dong,trang_thai=:trang_thai,ghi_chu=:ghi_chu WHERE id=:id")->execute(array_merge($data, ['id'=>$id]));
            setFlash('success','Cập nhật hợp đồng thành công!');
        } else {
            $data['ma_hd'] = genMaHD();
            $db->prepare("INSERT INTO hop_dong (ma_hd,id_nhan_vien,loai_hop_dong,ngay_bat_dau,ngay_ket_thuc,luong_hop_dong,trang_thai,ghi_chu) VALUES (:ma_hd,:id_nhan_vien,:loai_hop_dong,:ngay_bat_dau,:ngay_ket_thuc,:luong_hop_dong,:trang_thai,:ghi_chu)")->execute($data);
            setFlash('success','Tạo hợp đồng mới thành công! Mã: '.$data['ma_hd']);
        }
        header('Location: contracts.php'); exit();
    }
    if ($type === 'delete') {
        $db->prepare("DELETE FROM hop_dong WHERE id=?")->execute([(int)$_POST['id']]);
        setFlash('success','Đã xóa hợp đồng!');
        header('Location: contracts.php'); exit();
    }
}

$search = sanitize($_GET['search'] ?? '');
$tt_filter = sanitize($_GET['tt'] ?? '');
$where = []; $params = [];
if ($search) { $where[] = "(nv.ho_ten LIKE ? OR hd.ma_hd LIKE ?)"; $params[] = "%$search%"; $params[] = "%$search%"; }
if ($tt_filter) { $where[] = "hd.trang_thai=?"; $params[] = $tt_filter; }
$wStr = $where ? 'WHERE '.implode(' AND ',$where) : '';

$list = $db->prepare("SELECT hd.*, nv.ho_ten, nv.ma_nv FROM hop_dong hd LEFT JOIN nhan_vien nv ON hd.id_nhan_vien=nv.id $wStr ORDER BY hd.id DESC");
$list->execute($params); $list = $list->fetchAll();

$nhan_viens = $db->query("SELECT id,ma_nv,ho_ten FROM nhan_vien WHERE trang_thai='Đang làm' ORDER BY ho_ten")->fetchAll();
$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="vi">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Hợp đồng - <?= APP_NAME ?></title>
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
        <div><h1>Hợp đồng lao động</h1><p>Quản lý hợp đồng lao động của nhân viên</p></div>
        <button class="btn btn-primary" onclick="openModal('modalHD')"><i class="fas fa-plus"></i> Tạo hợp đồng</button>
      </div>

      <?php if ($flash): ?>
      <div class="alert alert-<?= $flash['type'] ?>"><i class="fas fa-info-circle"></i> <?= clean($flash['message']) ?></div>
      <?php endif; ?>

      <div class="card" style="margin-bottom:16px;">
        <div class="card-body" style="padding:14px 20px;">
          <form method="GET" style="display:flex;gap:10px;flex-wrap:wrap;">
            <input type="text" name="search" class="filter-input search-input" placeholder="🔍 Mã HĐ, tên nhân viên..." value="<?= clean($search) ?>">
            <select name="tt" class="filter-input">
              <option value="">Tất cả trạng thái</option>
              <option value="Đang hiệu lực" <?= $tt_filter==='Đang hiệu lực'?'selected':'' ?>>Đang hiệu lực</option>
              <option value="Hết hạn" <?= $tt_filter==='Hết hạn'?'selected':'' ?>>Hết hạn</option>
              <option value="Đã kết thúc" <?= $tt_filter==='Đã kết thúc'?'selected':'' ?>>Đã kết thúc</option>
            </select>
            <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Lọc</button>
            <a href="contracts.php" class="btn btn-outline"><i class="fas fa-times"></i> Xóa lọc</a>
          </form>
        </div>
      </div>

      <div class="card">
        <div class="table-responsive">
          <table class="table">
            <thead><tr><th>Mã HĐ</th><th>Nhân viên</th><th>Loại hợp đồng</th><th>Ngày bắt đầu</th><th>Ngày kết thúc</th><th>Lương HĐ</th><th>Trạng thái</th><th>Thao tác</th></tr></thead>
            <tbody>
              <?php if (empty($list)): ?>
              <tr><td colspan="8"><div class="empty-state"><i class="fas fa-file-contract"></i><h3>Không có hợp đồng</h3></div></td></tr>
              <?php else: foreach ($list as $hd): ?>
              <tr>
                <td><b><?= clean($hd['ma_hd']) ?></b></td>
                <td><?= clean($hd['ho_ten'] ?? '—') ?> <small style="color:var(--gray);">(<?= clean($hd['ma_nv'] ?? '') ?>)</small></td>
                <td><?= clean($hd['loai_hop_dong']) ?></td>
                <td><?= formatDate($hd['ngay_bat_dau']) ?></td>
                <td><?= $hd['ngay_ket_thuc'] ? formatDate($hd['ngay_ket_thuc']) : '<span style="color:var(--gray);">Không thời hạn</span>' ?></td>
                <td><b><?= formatMoney($hd['luong_hop_dong']) ?></b></td>
                <td><?= badgeTrangThaiHD($hd['trang_thai']) ?></td>
                <td>
                  <div style="display:flex;gap:4px;">
                    <button class="btn btn-sm btn-info btn-icon" onclick='openEditHD(<?= json_encode($hd,JSON_UNESCAPED_UNICODE) ?>)'><i class="fas fa-edit"></i></button>
                    <button class="btn btn-sm btn-danger btn-icon" onclick="confirmDelHD(<?= $hd['id'] ?>,'<?= addslashes($hd['ma_hd']) ?>')"><i class="fas fa-trash"></i></button>
                  </div>
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

<div class="modal-overlay" id="modalHD" style="display:none;">
  <div class="modal">
    <div class="modal-header">
      <span class="modal-title" id="modalHDTitle"><i class="fas fa-file-contract"></i> Tạo hợp đồng</span>
      <button class="modal-close" onclick="closeModal('modalHD')"><i class="fas fa-times"></i></button>
    </div>
    <form method="POST">
      <input type="hidden" name="type" value="save">
      <input type="hidden" name="id" id="hd_id" value="0">
      <div class="modal-body">
        <div class="form-group">
          <label class="form-label required">Nhân viên</label>
          <select name="id_nhan_vien" id="hd_nv" class="form-control" required>
            <option value="">-- Chọn nhân viên --</option>
            <?php foreach ($nhan_viens as $nv): ?>
            <option value="<?= $nv['id'] ?>"><?= clean($nv['ho_ten']) ?> (<?= clean($nv['ma_nv']) ?>)</option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-row col-2">
          <div class="form-group">
            <label class="form-label required">Loại hợp đồng</label>
            <select name="loai_hop_dong" id="hd_loai" class="form-control">
              <option>Thử việc</option><option>Xác định thời hạn</option><option>Không xác định thời hạn</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Trạng thái</label>
            <select name="trang_thai" id="hd_tt" class="form-control">
              <option>Đang hiệu lực</option><option>Hết hạn</option><option>Đã kết thúc</option><option>Gia hạn</option>
            </select>
          </div>
        </div>
        <div class="form-row col-2">
          <div class="form-group">
            <label class="form-label required">Ngày bắt đầu</label>
            <input type="date" name="ngay_bat_dau" id="hd_start" class="form-control" required>
          </div>
          <div class="form-group">
            <label class="form-label">Ngày kết thúc</label>
            <input type="date" name="ngay_ket_thuc" id="hd_end" class="form-control">
          </div>
        </div>
        <div class="form-group">
          <label class="form-label required">Lương hợp đồng (đ)</label>
          <input type="number" name="luong_hop_dong" id="hd_luong" class="form-control" min="0">
        </div>
        <div class="form-group">
          <label class="form-label">Ghi chú</label>
          <textarea name="ghi_chu" id="hd_ghichu" class="form-control"></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="closeModal('modalHD')">Hủy</button>
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Lưu hợp đồng</button>
      </div>
    </form>
  </div>
</div>

<div class="modal-overlay" id="modalDelHD" style="display:none;">
  <div class="modal modal-sm">
    <div class="modal-header"><span class="modal-title" style="color:var(--danger);"><i class="fas fa-trash"></i> Xóa hợp đồng</span><button class="modal-close" onclick="closeModal('modalDelHD')"><i class="fas fa-times"></i></button></div>
    <div class="modal-body"><p>Xóa hợp đồng: <b id="delHDName"></b>?</p></div>
    <div class="modal-footer">
      <button class="btn btn-outline" onclick="closeModal('modalDelHD')">Hủy</button>
      <form method="POST" style="display:inline;"><input type="hidden" name="type" value="delete"><input type="hidden" name="id" id="delHDId"><button type="submit" class="btn btn-danger"><i class="fas fa-trash"></i> Xóa</button></form>
    </div>
  </div>
</div>

<script src="../../assets/js/main.js"></script>
<script>
function openEditHD(hd) {
  document.getElementById('modalHDTitle').innerHTML = '<i class="fas fa-edit"></i> Sửa hợp đồng';
  document.getElementById('hd_id').value = hd.id;
  document.getElementById('hd_nv').value = hd.id_nhan_vien;
  document.getElementById('hd_loai').value = hd.loai_hop_dong;
  document.getElementById('hd_tt').value = hd.trang_thai;
  document.getElementById('hd_start').value = hd.ngay_bat_dau;
  document.getElementById('hd_end').value = hd.ngay_ket_thuc || '';
  document.getElementById('hd_luong').value = hd.luong_hop_dong;
  document.getElementById('hd_ghichu').value = hd.ghi_chu || '';
  openModal('modalHD');
}
function confirmDelHD(id, ma) {
  document.getElementById('delHDId').value = id;
  document.getElementById('delHDName').textContent = ma;
  openModal('modalDelHD');
}
</script>
</body></html>
