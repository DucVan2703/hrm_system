<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Hồ sơ của tôi - Đại Học Thành Đông</title>
<link rel="stylesheet" href="<?= Helper::asset('css/style.css') ?>">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
.profile-signature-box {
  margin-top: 24px;
}
.signature-card-body {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 24px;
  background: #f8fafc;
  border: 1.5px dashed var(--border);
  border-radius: 14px;
  text-align: center;
  min-height: 120px;
}
.sig-img-container {
  background: #ffffff;
  padding: 12px;
  border-radius: 8px;
  box-shadow: var(--shadow);
  display: inline-flex;
  align-items: center;
  justify-content: center;
  max-width: 100%;
}
.sig-img {
  max-height: 80px;
  width: auto;
  object-fit: contain;
}
.sig-placeholder {
  color: var(--gray-light);
  font-size: 13px;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
}
.sig-placeholder i {
  font-size: 32px;
  opacity: 0.5;
}
</style>
</head>
<body>
<div class="layout employee-page">
<?php require __DIR__.'/../layouts/sidebar.php'; ?>
<div class="main-content">
<?php require __DIR__.'/../layouts/header.php'; ?>
<div class="page-content">

<div class="page-header">
    <div>
        <h1>Hồ sơ của tôi</h1>
        <p>Thông tin cá nhân được lưu trong hệ thống nhân sự</p>
    </div>
    <?php if (!empty($employee)): ?>
    <div class="page-actions">
        <button class="btn btn-primary" onclick="openEditProfile()"><i class="fas fa-user-edit"></i> Chỉnh sửa hồ sơ</button>
    </div>
    <?php endif; ?>
</div>

<?php 
$flash = isset($_SESSION['flash']) ? $_SESSION['flash'] : null;
if ($flash): 
    unset($_SESSION['flash']);
?>
<div class="alert alert-<?= $flash['type'] ?>">
  <i class="fas fa-<?= $flash['type'] === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
  <?= Helper::clean($flash['message']) ?>
</div>
<?php endif; ?>

<?php if (empty($employee)): ?>
    <div class="alert alert-warning"><i class="fas fa-triangle-exclamation"></i> Tài khoản này chưa được gắn với hồ sơ nhân viên.</div>
<?php else: ?>
<div class="profile-card">
    <div class="profile-top">
        <?php if (!empty($employee['hinh_anh'])): ?>
            <img src="<?= BASE_URL . '/' . $employee['hinh_anh'] ?>" alt="Ảnh thẻ" class="profile-avatar" style="object-fit:cover; border-radius:24px; box-shadow:var(--shadow-lg);">
        <?php else: ?>
            <div class="profile-avatar"><?= mb_substr($employee['ho_ten'] ?? 'N', 0, 1) ?></div>
        <?php endif; ?>
        <div>
            <h2><?= Helper::clean($employee['ho_ten'] ?? '') ?></h2>
            <p><?= Helper::clean($employee['ma_nv'] ?? '') ?> · <?= Helper::clean($employee['ten_cv'] ?? 'Chưa cập nhật chức vụ') ?></p>
            <?= Helper::badgeTrangThaiNV($employee['trang_thai'] ?? 'Đang làm') ?>
        </div>
    </div>

    <div class="profile-info-grid">
        <div class="profile-info-item"><span>Mã nhân viên</span><b><?= Helper::clean($employee['ma_nv'] ?? '—') ?></b></div>
        <div class="profile-info-item"><span>Phòng ban</span><b><?= Helper::clean($employee['ten_pb'] ?? '—') ?></b></div>
        <div class="profile-info-item"><span>Email</span><b><?= Helper::clean($employee['email'] ?? '—') ?></b></div>
        <div class="profile-info-item"><span>Số điện thoại</span><b><?= Helper::clean($employee['so_dien_thoai'] ?? '—') ?></b></div>
        <div class="profile-info-item"><span>Ngày vào làm</span><b><?= Helper::formatDate($employee['ngay_vao_lam'] ?? null) ?></b></div>
        <div class="profile-info-item"><span>Địa chỉ</span><b><?= Helper::clean($employee['dia_chi'] ?? '—') ?></b></div>
    </div>
</div>

<!-- Chữ ký điện tử -->
<div class="card profile-signature-box">
    <div class="card-header">
        <span class="card-title"><i class="fas fa-signature"></i> Chữ ký điện tử của tôi</span>
    </div>
    <div class="card-body">
        <div class="signature-card-body">
            <?php if (!empty($employee['chu_ky'])): ?>
                <div class="sig-img-container">
                    <img src="<?= BASE_URL . '/' . $employee['chu_ky'] ?>" alt="Chữ ký cá nhân" class="sig-img">
                </div>
            <?php else: ?>
                <div class="sig-placeholder">
                    <i class="fas fa-file-signature"></i>
                    <span>Chưa cập nhật chữ ký điện tử. Bấm Chỉnh sửa hồ sơ để tải lên chữ ký.</span>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php endif; ?>

</div>
</div>
</div>

<!-- MODAL CẬP NHẬT HỒ SƠ DÀNH CHO NHÂN VIÊN -->
<?php if (!empty($employee)): ?>
<div class="modal-overlay" id="modalEditProfile" style="display:none;">
  <div class="modal modal-lg">
    <div class="modal-header">
      <span class="modal-title"><i class="fas fa-user-edit"></i> Cập nhật hồ sơ cá nhân</span>
      <button class="modal-close" onclick="closeModal('modalEditProfile')"><i class="fas fa-times"></i></button>
    </div>
    <form method="POST" enctype="multipart/form-data">
      <div class="modal-body">
        <div class="form-row col-2">
          <div class="form-group">
            <label class="form-label">Họ và tên (Chỉ đọc)</label>
            <input type="text" class="form-control" value="<?= Helper::clean($employee['ho_ten'] ?? '') ?>" readonly style="background:#f1f5f9;color:var(--gray);">
          </div>
          <div class="form-group">
            <label class="form-label">Mã nhân viên (Chỉ đọc)</label>
            <input type="text" class="form-control" value="<?= Helper::clean($employee['ma_nv'] ?? '') ?>" readonly style="background:#f1f5f9;color:var(--gray);">
          </div>
        </div>

        <div class="form-row col-2">
          <div class="form-group">
            <label class="form-label required">Số điện thoại</label>
            <input type="text" name="so_dien_thoai" id="edit_sdt" class="form-control" value="<?= Helper::clean($employee['so_dien_thoai'] ?? '') ?>" required>
          </div>
          <div class="form-group">
            <label class="form-label required">Email liên hệ</label>
            <input type="email" name="email" id="edit_email" class="form-control" value="<?= Helper::clean($employee['email'] ?? '') ?>" required>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label required">Địa chỉ thường trú</label>
          <input type="text" name="dia_chi" id="edit_dia_chi" class="form-control" value="<?= Helper::clean($employee['dia_chi'] ?? '') ?>" required>
        </div>

        <div class="form-row col-2" style="margin-top:10px;">
          <div class="form-group">
            <label class="form-label">Ảnh thẻ mới (PNG/JPG)</label>
            <input type="file" name="hinh_anh" class="form-control" accept="image/*">
            <p style="font-size:11px;color:var(--gray);margin-top:4px;">Tải lên ảnh thẻ chân dung mới nếu muốn đổi ảnh đại diện.</p>
          </div>
          <div class="form-group">
            <label class="form-label">Ảnh chữ ký mới (PNG/JPG)</label>
            <input type="file" name="chu_ky" class="form-control" accept="image/*">
            <p style="font-size:11px;color:var(--gray);margin-top:4px;">Tải lên ảnh chữ ký mới nếu muốn đổi chữ ký điện tử.</p>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="closeModal('modalEditProfile')">Hủy</button>
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Lưu thay đổi</button>
      </div>
    </form>
  </div>
</div>
<?php endif; ?>

<script src="<?= Helper::asset('js/main.js') ?>"></script>
<script>
function openEditProfile() {
  openModal('modalEditProfile');
}
</script>
</body>
</html>
