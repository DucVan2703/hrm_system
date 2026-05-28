<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
requireHR();

$pageTitle = 'Quản lý phòng ban';
$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['type'] ?? '';
    if ($type === 'save') {
        $id = (int)($_POST['id'] ?? 0);
        $ten = sanitize($_POST['ten_pb'] ?? '');
        $mo_ta = sanitize($_POST['mo_ta'] ?? '');
        if (empty($ten)) { setFlash('danger', 'Tên phòng ban không được để trống!'); }
        else {
            if ($id > 0) {
                $db->prepare("UPDATE phong_ban SET ten_pb=?,mo_ta=? WHERE id=?")->execute([$ten, $mo_ta, $id]);
                setFlash('success', 'Cập nhật phòng ban thành công!');
            } else {
                $ma = 'PB' . str_pad($db->query("SELECT COALESCE(MAX(CAST(SUBSTRING(ma_pb,3) AS UNSIGNED)),0)+1 FROM phong_ban WHERE ma_pb LIKE 'PB%'")->fetchColumn(), 3, '0', STR_PAD_LEFT);
                $db->prepare("INSERT INTO phong_ban (ma_pb,ten_pb,mo_ta) VALUES (?,?,?)")->execute([$ma, $ten, $mo_ta]);
                setFlash('success', 'Thêm phòng ban thành công!');
            }
        }
        header('Location: departments.php'); exit();
    }
    if ($type === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        $cnt = $db->prepare("SELECT COUNT(*) FROM nhan_vien WHERE id_phong_ban=?"); $cnt->execute([$id]);
        if ($cnt->fetchColumn() > 0) { setFlash('danger', 'Không thể xóa phòng ban đang có nhân viên!'); }
        else { $db->prepare("DELETE FROM phong_ban WHERE id=?")->execute([$id]); setFlash('success', 'Đã xóa phòng ban!'); }
        header('Location: departments.php'); exit();
    }
}

$list = $db->query("SELECT pb.*, COUNT(nv.id) as so_nv FROM phong_ban pb LEFT JOIN nhan_vien nv ON pb.id=nv.id_phong_ban AND nv.trang_thai='Đang làm' GROUP BY pb.id ORDER BY pb.id")->fetchAll();
$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Phòng ban - <?= APP_NAME ?></title>
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
        <div><h1>Quản lý phòng ban</h1><p>Danh sách và quản lý các phòng ban trong nhà trường</p></div>
        <button class="btn btn-primary" onclick="openModal('modalPB')"><i class="fas fa-plus"></i> Thêm phòng ban</button>
      </div>

      <?php if ($flash): ?>
      <div class="alert alert-<?= $flash['type'] ?>"><i class="fas fa-info-circle"></i> <?= clean($flash['message']) ?></div>
      <?php endif; ?>

      <div class="card">
        <div class="table-responsive">
          <table class="table">
            <thead><tr><th>Mã PB</th><th>Tên phòng ban</th><th>Mô tả</th><th>Số NV</th><th>Thao tác</th></tr></thead>
            <tbody>
              <?php foreach ($list as $pb): ?>
              <tr>
                <td><span class="badge badge-primary"><?= clean($pb['ma_pb']) ?></span></td>
                <td><b><?= clean($pb['ten_pb']) ?></b></td>
                <td style="color:var(--gray);"><?= clean($pb['mo_ta'] ?: '—') ?></td>
                <td><span class="badge badge-success"><?= $pb['so_nv'] ?> NV</span></td>
                <td>
                  <div style="display:flex;gap:4px;">
                    <button class="btn btn-sm btn-info btn-icon" data-tooltip="Sửa" onclick='openEditPB(<?= json_encode($pb, JSON_UNESCAPED_UNICODE) ?>)'><i class="fas fa-edit"></i></button>
                    <button class="btn btn-sm btn-danger btn-icon" data-tooltip="Xóa" onclick="confirmDelPB(<?= $pb['id'] ?>,'<?= addslashes($pb['ten_pb']) ?>')"><i class="fas fa-trash"></i></button>
                  </div>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal -->
<div class="modal-overlay" id="modalPB" style="display:none;">
  <div class="modal modal-sm">
    <div class="modal-header">
      <span class="modal-title" id="modalPBTitle"><i class="fas fa-sitemap"></i> Thêm phòng ban</span>
      <button class="modal-close" onclick="closeModal('modalPB')"><i class="fas fa-times"></i></button>
    </div>
    <form method="POST">
      <input type="hidden" name="type" value="save">
      <input type="hidden" name="id" id="pb_id" value="0">
      <div class="modal-body">
        <div class="form-group">
          <label class="form-label required">Tên phòng ban</label>
          <input type="text" name="ten_pb" id="pb_ten" class="form-control" required>
        </div>
        <div class="form-group">
          <label class="form-label">Mô tả</label>
          <textarea name="mo_ta" id="pb_mota" class="form-control"></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="closeModal('modalPB')">Hủy</button>
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Lưu</button>
      </div>
    </form>
  </div>
</div>

<div class="modal-overlay" id="modalDelPB" style="display:none;">
  <div class="modal modal-sm">
    <div class="modal-header"><span class="modal-title" style="color:var(--danger);"><i class="fas fa-trash"></i> Xác nhận xóa</span><button class="modal-close" onclick="closeModal('modalDelPB')"><i class="fas fa-times"></i></button></div>
    <div class="modal-body"><p>Xóa phòng ban: <b id="delPBName"></b>?</p></div>
    <div class="modal-footer">
      <button class="btn btn-outline" onclick="closeModal('modalDelPB')">Hủy</button>
      <form method="POST" style="display:inline;"><input type="hidden" name="type" value="delete"><input type="hidden" name="id" id="delPBId"><button type="submit" class="btn btn-danger"><i class="fas fa-trash"></i> Xóa</button></form>
    </div>
  </div>
</div>

<script src="../../assets/js/main.js"></script>
<script>
function openEditPB(pb) {
  document.getElementById('modalPBTitle').innerHTML = '<i class="fas fa-edit"></i> Sửa phòng ban';
  document.getElementById('pb_id').value = pb.id;
  document.getElementById('pb_ten').value = pb.ten_pb;
  document.getElementById('pb_mota').value = pb.mo_ta || '';
  openModal('modalPB');
}
function confirmDelPB(id, name) {
  document.getElementById('delPBId').value = id;
  document.getElementById('delPBName').textContent = name;
  openModal('modalDelPB');
}
</script>
</body></html>
