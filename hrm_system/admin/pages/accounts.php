<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
requireOnlyAdmin();
$pageTitle = 'Quản lý tài khoản';
$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['type'] ?? '';
    if ($type === 'save') {
        $id = (int)($_POST['id'] ?? 0);
        $username = sanitize($_POST['ten_dang_nhap']);
        $vai_tro = sanitize($_POST['vai_tro']);
        $id_nv = (int)($_POST['id_nhan_vien'] ?? 0) ?: null;
        if ($id > 0) {
            $newpw = $_POST['mat_khau'] ?? '';
            if ($newpw) {
                $db->prepare("UPDATE tai_khoan SET ten_dang_nhap=?,vai_tro=?,id_nhan_vien=?,mat_khau=? WHERE id=?")->execute([$username,$vai_tro,$id_nv,password_hash($newpw,PASSWORD_DEFAULT),$id]);
            } else {
                $db->prepare("UPDATE tai_khoan SET ten_dang_nhap=?,vai_tro=?,id_nhan_vien=? WHERE id=?")->execute([$username,$vai_tro,$id_nv,$id]);
            }
            setFlash('success','Cập nhật tài khoản thành công!');
        } else {
            $pw = $_POST['mat_khau'] ?? 'password';
            // Check trùng username
            $chk = $db->prepare("SELECT id FROM tai_khoan WHERE ten_dang_nhap=?"); $chk->execute([$username]);
            if ($chk->fetch()) { setFlash('danger','Tên đăng nhập đã tồn tại!'); }
            else {
                $db->prepare("INSERT INTO tai_khoan (ten_dang_nhap,mat_khau,vai_tro,id_nhan_vien) VALUES (?,?,?,?)")->execute([$username,password_hash($pw,PASSWORD_DEFAULT),$vai_tro,$id_nv]);
                setFlash('success','Tạo tài khoản thành công!');
            }
        }
        header('Location: accounts.php'); exit();
    }
    if ($type === 'delete') {
        $id = (int)$_POST['id'];
        if ($id == $_SESSION['user_id']) { setFlash('danger','Không thể xóa tài khoản đang đăng nhập!'); }
        else { $db->prepare("DELETE FROM tai_khoan WHERE id=?")->execute([$id]); setFlash('success','Đã xóa tài khoản!'); }
        header('Location: accounts.php'); exit();
    }
    if ($type === 'toggle') {
        $id = (int)$_POST['id'];
        $db->prepare("UPDATE tai_khoan SET trang_thai = 1-trang_thai WHERE id=?")->execute([$id]);
        setFlash('success','Đã thay đổi trạng thái tài khoản!');
        header('Location: accounts.php'); exit();
    }
}

$list = $db->query("SELECT tk.*, nv.ho_ten, nv.ma_nv FROM tai_khoan tk LEFT JOIN nhan_vien nv ON tk.id_nhan_vien=nv.id ORDER BY tk.id")->fetchAll();
$nv_list = $db->query("SELECT id,ma_nv,ho_ten FROM nhan_vien WHERE trang_thai='Đang làm' ORDER BY ho_ten")->fetchAll();
$flash = getFlash();

$vaiTroLabels = ['admin'=>'Admin','hr'=>'Nhân sự','ketoan'=>'Kế toán','nhanvien'=>'Nhân viên'];
?>
<!DOCTYPE html>
<html lang="vi">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tài khoản - <?= APP_NAME ?></title>
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
        <div><h1>Quản lý tài khoản</h1><p>Tạo, phân quyền và quản lý tài khoản người dùng</p></div>
        <button class="btn btn-primary" onclick="openModal('modalTK')"><i class="fas fa-user-plus"></i> Tạo tài khoản</button>
      </div>

      <?php if ($flash): ?><div class="alert alert-<?= $flash['type'] ?>"><i class="fas fa-info-circle"></i> <?= clean($flash['message']) ?></div><?php endif; ?>

      <div class="card">
        <div class="table-responsive">
          <table class="table">
            <thead><tr><th>Tài khoản</th><th>Nhân viên</th><th>Vai trò</th><th>Trạng thái</th><th>Đăng nhập cuối</th><th>Thao tác</th></tr></thead>
            <tbody>
              <?php foreach ($list as $tk): ?>
              <tr>
                <td>
                  <div style="display:flex;align-items:center;gap:8px;">
                    <div class="avatar avatar-sm" style="background:<?= $tk['vai_tro']==='admin' ? 'linear-gradient(135deg,#ef4444,#f97316)' : 'linear-gradient(135deg,#4f46e5,#06b6d4)' ?>;"><?= mb_substr($tk['ten_dang_nhap'],0,1) ?></div>
                    <div>
                      <b><?= clean($tk['ten_dang_nhap']) ?></b>
                      <?php if ($tk['id'] == $_SESSION['user_id']): ?><span class="badge badge-primary" style="font-size:9px;">Bạn</span><?php endif; ?>
                    </div>
                  </div>
                </td>
                <td><?= $tk['ho_ten'] ? clean($tk['ho_ten']).' ('.clean($tk['ma_nv']).')' : '<span style="color:var(--gray);">—</span>' ?></td>
                <td>
                  <?php $vr = $tk['vai_tro'];
                  $clsMap = ['admin'=>'danger','hr'=>'info','ketoan'=>'warning','nhanvien'=>'secondary'];
                  ?>
                  <span class="badge badge-<?= $clsMap[$vr] ?? 'secondary' ?>"><?= $vaiTroLabels[$vr] ?? $vr ?></span>
                </td>
                <td>
                  <?= $tk['trang_thai'] ? '<span class="badge badge-success">Hoạt động</span>' : '<span class="badge badge-danger">Bị khóa</span>' ?>
                </td>
                <td style="font-size:12px;"><?= formatDateTime($tk['lan_dang_nhap_cuoi']) ?></td>
                <td>
                  <div style="display:flex;gap:4px;">
                    <button class="btn btn-sm btn-info btn-icon" data-tooltip="Sửa" onclick='openEditTK(<?= json_encode($tk,JSON_UNESCAPED_UNICODE) ?>)'><i class="fas fa-edit"></i></button>
                    <form method="POST" style="display:inline;">
                      <input type="hidden" name="type" value="toggle">
                      <input type="hidden" name="id" value="<?= $tk['id'] ?>">
                      <button type="submit" class="btn btn-sm btn-warning btn-icon" data-tooltip="<?= $tk['trang_thai'] ? 'Khóa' : 'Mở khóa' ?>"><i class="fas fa-<?= $tk['trang_thai'] ? 'lock' : 'unlock' ?>"></i></button>
                    </form>
                    <?php if ($tk['id'] != $_SESSION['user_id']): ?>
                    <button class="btn btn-sm btn-danger btn-icon" data-tooltip="Xóa" onclick="confirmDelTK(<?= $tk['id'] ?>,'<?= addslashes($tk['ten_dang_nhap']) ?>')"><i class="fas fa-trash"></i></button>
                    <?php endif; ?>
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

<div class="modal-overlay" id="modalTK" style="display:none;">
  <div class="modal">
    <div class="modal-header">
      <span class="modal-title" id="modalTKTitle"><i class="fas fa-user-plus"></i> Tạo tài khoản</span>
      <button class="modal-close" onclick="closeModal('modalTK')"><i class="fas fa-times"></i></button>
    </div>
    <form method="POST">
      <input type="hidden" name="type" value="save">
      <input type="hidden" name="id" id="tk_id" value="0">
      <div class="modal-body">
        <div class="form-row col-2">
          <div class="form-group">
            <label class="form-label required">Tên đăng nhập</label>
            <input type="text" name="ten_dang_nhap" id="tk_username" class="form-control" required>
          </div>
          <div class="form-group">
            <label class="form-label" id="tk_pw_label">Mật khẩu</label>
            <input type="password" name="mat_khau" id="tk_password" class="form-control" placeholder="Mặc định: password">
          </div>
        </div>
        <div class="form-row col-2">
          <div class="form-group">
            <label class="form-label">Vai trò</label>
            <select name="vai_tro" id="tk_vaitro" class="form-control">
              <option value="nhanvien">Nhân viên</option>
              <option value="hr">Nhân sự (HR)</option>
              <option value="ketoan">Kế toán</option>
              <option value="admin">Admin</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Liên kết nhân viên</label>
            <select name="id_nhan_vien" id="tk_nv" class="form-control">
              <option value="">-- Không liên kết --</option>
              <?php foreach ($nv_list as $nv): ?>
              <option value="<?= $nv['id'] ?>"><?= clean($nv['ho_ten']) ?> (<?= clean($nv['ma_nv']) ?>)</option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="alert alert-info" style="font-size:12px;">
          <i class="fas fa-info-circle"></i> Mật khẩu mặc định: <b>password</b>. Nhân viên nên đổi sau khi đăng nhập lần đầu.
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="closeModal('modalTK')">Hủy</button>
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Lưu</button>
      </div>
    </form>
  </div>
</div>

<div class="modal-overlay" id="modalDelTK" style="display:none;">
  <div class="modal modal-sm">
    <div class="modal-header"><span class="modal-title" style="color:var(--danger);"><i class="fas fa-trash"></i> Xóa tài khoản</span><button class="modal-close" onclick="closeModal('modalDelTK')"><i class="fas fa-times"></i></button></div>
    <div class="modal-body"><p>Xóa tài khoản: <b id="delTKName"></b>?</p></div>
    <div class="modal-footer">
      <button class="btn btn-outline" onclick="closeModal('modalDelTK')">Hủy</button>
      <form method="POST" style="display:inline;"><input type="hidden" name="type" value="delete"><input type="hidden" name="id" id="delTKId"><button type="submit" class="btn btn-danger"><i class="fas fa-trash"></i> Xóa</button></form>
    </div>
  </div>
</div>

<script src="../../assets/js/main.js"></script>
<script>
function openEditTK(tk) {
  document.getElementById('modalTKTitle').innerHTML = '<i class="fas fa-edit"></i> Sửa tài khoản';
  document.getElementById('tk_id').value = tk.id;
  document.getElementById('tk_username').value = tk.ten_dang_nhap;
  document.getElementById('tk_vaitro').value = tk.vai_tro;
  document.getElementById('tk_nv').value = tk.id_nhan_vien || '';
  document.getElementById('tk_pw_label').textContent = 'Mật khẩu mới (để trống = không đổi)';
  document.getElementById('tk_password').placeholder = 'Để trống nếu không đổi';
  openModal('modalTK');
}
function confirmDelTK(id, name) {
  document.getElementById('delTKId').value = id;
  document.getElementById('delTKName').textContent = name;
  openModal('modalDelTK');
}
</script>
</body></html>
