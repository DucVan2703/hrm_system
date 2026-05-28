<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
requireAdmin();
$pageTitle = 'Quản lý chức vụ';
$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['type'] ?? '';
    if ($type === 'save') {
        $id = (int)($_POST['id'] ?? 0);
        $ten = sanitize($_POST['ten_cv'] ?? '');
        $mota = sanitize($_POST['mo_ta'] ?? '');
        $heso = (float)($_POST['he_so_luong'] ?? 1);
        if (empty($ten)) { setFlash('danger', 'Tên chức vụ không để trống!'); }
        else {
            if ($id > 0) {
                $db->prepare("UPDATE chuc_vu SET ten_cv=?,mo_ta=?,he_so_luong=? WHERE id=?")->execute([$ten,$mota,$heso,$id]);
                setFlash('success','Cập nhật chức vụ thành công!');
            } else {
                $ma = 'CV'.str_pad($db->query("SELECT COALESCE(MAX(CAST(SUBSTRING(ma_cv,3) AS UNSIGNED)),0)+1 FROM chuc_vu WHERE ma_cv LIKE 'CV%'")->fetchColumn(),3,'0',STR_PAD_LEFT);
                $db->prepare("INSERT INTO chuc_vu (ma_cv,ten_cv,mo_ta,he_so_luong) VALUES (?,?,?,?)")->execute([$ma,$ten,$mota,$heso]);
                setFlash('success','Thêm chức vụ thành công!');
            }
        }
        header('Location: positions.php'); exit();
    }
    if ($type === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        $db->prepare("DELETE FROM chuc_vu WHERE id=?")->execute([$id]);
        setFlash('success','Đã xóa chức vụ!');
        header('Location: positions.php'); exit();
    }
}

$list = $db->query("SELECT cv.*, COUNT(nv.id) as so_nv FROM chuc_vu cv LEFT JOIN nhan_vien nv ON cv.id=nv.id_chuc_vu GROUP BY cv.id ORDER BY cv.he_so_luong DESC")->fetchAll();
$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="vi">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Chức vụ - <?= APP_NAME ?></title>
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
        <div><h1>Quản lý chức vụ</h1><p>Danh sách các chức vụ và hệ số lương</p></div>
        <button class="btn btn-primary" onclick="openModal('modalCV')"><i class="fas fa-plus"></i> Thêm chức vụ</button>
      </div>

      <?php if ($flash): ?>
      <div class="alert alert-<?= $flash['type'] ?>"><i class="fas fa-info-circle"></i> <?= clean($flash['message']) ?></div>
      <?php endif; ?>

      <div class="card">
        <div class="table-responsive">
          <table class="table">
            <thead><tr><th>Mã CV</th><th>Tên chức vụ</th><th>Mô tả</th><th>Hệ số lương</th><th>Số NV</th><th>Thao tác</th></tr></thead>
            <tbody>
              <?php foreach ($list as $cv): ?>
              <tr>
                <td><span class="badge badge-info"><?= clean($cv['ma_cv']) ?></span></td>
                <td><b><?= clean($cv['ten_cv']) ?></b></td>
                <td style="color:var(--gray);"><?= clean($cv['mo_ta'] ?: '—') ?></td>
                <td><span class="badge badge-warning">x<?= $cv['he_so_luong'] ?></span></td>
                <td><?= $cv['so_nv'] ?> NV</td>
                <td>
                  <div style="display:flex;gap:4px;">
                    <button class="btn btn-sm btn-info btn-icon" onclick='openEditCV(<?= json_encode($cv,JSON_UNESCAPED_UNICODE) ?>)'><i class="fas fa-edit"></i></button>
                    <button class="btn btn-sm btn-danger btn-icon" onclick="confirmDelCV(<?= $cv['id'] ?>,'<?= addslashes($cv['ten_cv']) ?>')"><i class="fas fa-trash"></i></button>
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

<div class="modal-overlay" id="modalCV" style="display:none;">
  <div class="modal modal-sm">
    <div class="modal-header">
      <span class="modal-title" id="modalCVTitle"><i class="fas fa-user-tie"></i> Thêm chức vụ</span>
      <button class="modal-close" onclick="closeModal('modalCV')"><i class="fas fa-times"></i></button>
    </div>
    <form method="POST">
      <input type="hidden" name="type" value="save">
      <input type="hidden" name="id" id="cv_id" value="0">
      <div class="modal-body">
        <div class="form-group">
          <label class="form-label required">Tên chức vụ</label>
          <input type="text" name="ten_cv" id="cv_ten" class="form-control" required>
        </div>
        <div class="form-group">
          <label class="form-label">Hệ số lương</label>
          <input type="number" name="he_so_luong" id="cv_heso" class="form-control" value="1" step="0.1" min="0.1">
        </div>
        <div class="form-group">
          <label class="form-label">Mô tả</label>
          <textarea name="mo_ta" id="cv_mota" class="form-control"></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="closeModal('modalCV')">Hủy</button>
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Lưu</button>
      </div>
    </form>
  </div>
</div>

<div class="modal-overlay" id="modalDelCV" style="display:none;">
  <div class="modal modal-sm">
    <div class="modal-header"><span class="modal-title" style="color:var(--danger);"><i class="fas fa-trash"></i> Xác nhận xóa</span><button class="modal-close" onclick="closeModal('modalDelCV')"><i class="fas fa-times"></i></button></div>
    <div class="modal-body"><p>Xóa chức vụ: <b id="delCVName"></b>?</p></div>
    <div class="modal-footer">
      <button class="btn btn-outline" onclick="closeModal('modalDelCV')">Hủy</button>
      <form method="POST" style="display:inline;"><input type="hidden" name="type" value="delete"><input type="hidden" name="id" id="delCVId"><button type="submit" class="btn btn-danger"><i class="fas fa-trash"></i> Xóa</button></form>
    </div>
  </div>
</div>

<script src="../../assets/js/main.js"></script>
<script>
function openEditCV(cv) {
  document.getElementById('modalCVTitle').innerHTML = '<i class="fas fa-edit"></i> Sửa chức vụ';
  document.getElementById('cv_id').value = cv.id;
  document.getElementById('cv_ten').value = cv.ten_cv;
  document.getElementById('cv_heso').value = cv.he_so_luong;
  document.getElementById('cv_mota').value = cv.mo_ta || '';
  openModal('modalCV');
}
function confirmDelCV(id, name) {
  document.getElementById('delCVId').value = id;
  document.getElementById('delCVName').textContent = name;
  openModal('modalDelCV');
}
</script>
</body></html>
