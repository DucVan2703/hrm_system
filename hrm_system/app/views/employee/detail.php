<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Chi tiết nhân viên - Đại Học Thành Đông</title>
<link rel="stylesheet" href="<?= Helper::asset('css/style.css') ?>">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
.profile-hero {
  background: linear-gradient(135deg, #0b1528 0%, #059669 100%);
  color: #fff;
  border-radius: 20px;
  padding: 30px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 20px;
  margin-bottom: 24px;
  box-shadow: var(--shadow-md);
  position: relative;
  overflow: hidden;
}
.profile-hero::after {
  content: '';
  position: absolute;
  width: 250px;
  height: 250px;
  background: rgba(16, 185, 129, 0.1);
  border-radius: 50%;
  right: -50px;
  top: -80px;
}
.profile-person { display: flex; align-items: center; gap: 20px; min-width: 0; position: relative; z-index: 1; }
.profile-avatar-img {
  width: 88px;
  height: 88px;
  border-radius: 20px;
  object-fit: cover;
  border: 3px solid rgba(255, 255, 255, 0.9);
  box-shadow: var(--shadow-lg);
  flex-shrink: 0;
}
.profile-avatar-letters {
  width: 88px;
  height: 88px;
  border-radius: 20px;
  background: linear-gradient(135deg, #10b981, #059669);
  border: 3px solid rgba(255, 255, 255, 0.9);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 36px;
  font-weight: 800;
  color: #fff;
  box-shadow: var(--shadow-lg);
  flex-shrink: 0;
}
.profile-title h1 { font-size: 26px; line-height: 1.2; margin-bottom: 6px; font-weight: 800; letter-spacing: -0.5px; }
.profile-title p { color: rgba(255, 255, 255, 0.85); font-size: 13.5px; font-weight: 500; }
.profile-actions { display: flex; gap: 10px; flex-wrap: wrap; justify-content: flex-end; position: relative; z-index: 1; }
.profile-actions .btn-outline { color: #fff; border-color: rgba(255, 255, 255, 0.4); background: rgba(255, 255, 255, 0.05); }
.profile-actions .btn-outline:hover { background: rgba(255, 255, 255, 0.15); color: #fff; border-color: rgba(255, 255, 255, 0.6); }

.detail-grid {
  display: grid;
  grid-template-columns: 1.15fr .85fr;
  gap: 24px;
}
.info-list {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
}
.info-item {
  border: 1px solid var(--border);
  border-radius: 12px;
  padding: 14px 16px;
  background: #f8fafc;
  transition: var(--transition);
}
.info-item:hover {
  background: #fff;
  border-color: var(--primary-light);
  box-shadow: var(--shadow);
}
.info-item.full { grid-column: 1 / -1; }
.info-label {
  color: var(--gray);
  font-size: 11.5px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: .8px;
  margin-bottom: 6px;
}
.info-value { font-weight: 700; color: var(--dark); word-break: break-word; font-size: 14px; }
.summary-card {
  border: 1px solid var(--border);
  border-radius: 14px;
  padding: 16px;
  display: flex;
  align-items: center;
  gap: 14px;
  margin-bottom: 14px;
  background: #f8fafc;
  transition: var(--transition);
}
.summary-card:hover {
  background: #fff;
  border-color: var(--primary-light);
  box-shadow: var(--shadow);
}
.summary-icon {
  width: 44px;
  height: 44px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(5, 150, 105, 0.08);
  color: var(--primary);
  font-size: 16px;
  flex-shrink: 0;
}
.summary-card p { color: var(--gray); font-size: 12px; margin-bottom: 3px; font-weight: 500; }
.summary-card b { font-size: 14.5px; color: var(--dark); font-weight: 700; }

.signature-container {
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
.signature-img-box {
  background: #ffffff;
  padding: 12px;
  border-radius: 8px;
  box-shadow: var(--shadow);
  display: inline-flex;
  align-items: center;
  justify-content: center;
  max-width: 100%;
}
.signature-img {
  max-height: 80px;
  width: auto;
  object-fit: contain;
}
.signature-placeholder {
  color: var(--gray-light);
  font-size: 13px;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
}
.signature-placeholder i {
  font-size: 32px;
  opacity: 0.5;
}

@media (max-width: 900px) {
  .profile-hero, .detail-grid { grid-template-columns: 1fr; }
  .profile-hero { flex-direction: column; align-items: flex-start; }
  .profile-actions { justify-content: flex-start; width: 100%; }
  .info-list { grid-template-columns: 1fr; }
}
</style>
</head>
<body>
<div class="layout">
  <?php
  $current_page = 'employees';
  require __DIR__ . '/../layouts/sidebar.php';
  ?>
  <div class="main-content">
    <?php require __DIR__ . '/../layouts/header.php'; ?>
    <div class="page-content">
      <div class="profile-hero">
        <div class="profile-person">
          <?php if (!empty($employee['hinh_anh'])): ?>
            <img src="<?= BASE_URL . '/' . $employee['hinh_anh'] ?>" alt="Ảnh thẻ" class="profile-avatar-img">
          <?php else: ?>
            <div class="profile-avatar-letters"><?= mb_substr($employee['ho_ten'] ?? 'N', 0, 1) ?></div>
          <?php endif; ?>
          <div class="profile-title">
            <h1><?= Helper::clean($employee['ho_ten'] ?? '') ?></h1>
            <p><?= Helper::clean($employee['ma_nv'] ?? '') ?> · <?= Helper::clean($employee['ten_pb'] ?? 'Chưa có phòng ban') ?> · <?= Helper::clean($employee['ten_cv'] ?? 'Chưa có chức vụ') ?></p>
          </div>
        </div>
        <div class="profile-actions">
          <a href="<?= Helper::route('employee') ?>" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Quay lại</a>
          <a href="mailto:<?= Helper::clean($employee['email'] ?? '') ?>" class="btn btn-primary"><i class="fas fa-envelope"></i> Gửi email</a>
        </div>
      </div>

      <div class="detail-grid">
        <!-- Cột trái: Thông tin cá nhân -->
        <div class="card">
          <div class="card-header">
            <span class="card-title"><i class="fas fa-id-card"></i> Thông tin cá nhân</span>
          </div>
          <div class="card-body">
            <div class="info-list">
              <div class="info-item">
                <div class="info-label">Mã nhân viên</div>
                <div class="info-value"><?= Helper::clean($employee['ma_nv'] ?? '') ?></div>
              </div>
              <div class="info-item">
                <div class="info-label">Trạng thái</div>
                <div class="info-value"><?= Helper::badgeTrangThaiNV($employee['trang_thai'] ?? '') ?></div>
              </div>
              <div class="info-item">
                <div class="info-label">Ngày sinh</div>
                <div class="info-value"><?= Helper::formatDate($employee['ngay_sinh'] ?? '') ?></div>
              </div>
              <div class="info-item">
                <div class="info-label">Giới tính</div>
                <div class="info-value"><?= Helper::clean($employee['gioi_tinh'] ?? '') ?></div>
              </div>
              <div class="info-item">
                <div class="info-label">CCCD</div>
                <div class="info-value"><?= Helper::clean($employee['cccd'] ?? '') ?></div>
              </div>
              <div class="info-item">
                <div class="info-label">Điện thoại</div>
                <div class="info-value"><?= Helper::clean($employee['so_dien_thoai'] ?? '') ?></div>
              </div>
              <div class="info-item full">
                <div class="info-label">Email</div>
                <div class="info-value"><?= Helper::clean($employee['email'] ?? '') ?></div>
              </div>
              <div class="info-item full">
                <div class="info-label">Địa chỉ</div>
                <div class="info-value"><?= Helper::clean($employee['dia_chi'] ?? '') ?></div>
              </div>
            </div>
          </div>
        </div>

        <!-- Cột phải: Công việc & Lương & Chữ ký -->
        <div>
          <!-- Công việc -->
          <div class="card" style="margin-bottom:20px;">
            <div class="card-header">
              <span class="card-title"><i class="fas fa-briefcase"></i> Công việc</span>
            </div>
            <div class="card-body">
              <div class="summary-card">
                <div class="summary-icon"><i class="fas fa-sitemap"></i></div>
                <div><p>Phòng ban</p><b><?= Helper::clean($employee['ten_pb'] ?? 'Chưa cập nhật') ?></b></div>
              </div>
              <div class="summary-card">
                <div class="summary-icon"><i class="fas fa-user-tie"></i></div>
                <div><p>Chức vụ</p><b><?= Helper::clean($employee['ten_cv'] ?? 'Chưa cập nhật') ?></b></div>
              </div>
              <div class="summary-card">
                <div class="summary-icon"><i class="fas fa-calendar-plus"></i></div>
                <div><p>Ngày vào làm</p><b><?= Helper::formatDate($employee['ngay_vao_lam'] ?? '') ?></b></div>
              </div>
            </div>
          </div>

          <!-- Lương -->
          <div class="card" style="margin-bottom:20px;">
            <div class="card-header">
              <span class="card-title"><i class="fas fa-money-bill-wave"></i> Lương</span>
            </div>
            <div class="card-body">
              <div class="summary-card" style="margin-bottom:0;">
                <div class="summary-icon"><i class="fas fa-coins"></i></div>
                <div><p>Lương cơ bản</p><b style="color:var(--success);font-size:18px;"><?= Helper::formatMoney($employee['luong_co_ban'] ?? 0) ?></b></div>
              </div>
            </div>
          </div>

          <!-- Chữ ký điện tử -->
          <div class="card">
            <div class="card-header">
              <span class="card-title"><i class="fas fa-signature"></i> Chữ ký điện tử</span>
            </div>
            <div class="card-body">
              <div class="signature-container">
                <?php if (!empty($employee['chu_ky'])): ?>
                  <div class="signature-img-box">
                    <img src="<?= BASE_URL . '/' . $employee['chu_ky'] ?>" alt="Chữ ký nhân viên" class="signature-img">
                  </div>
                <?php else: ?>
                  <div class="signature-placeholder">
                    <i class="fas fa-file-signature"></i>
                    <span>Chưa cập nhật chữ ký điện tử</span>
                  </div>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<script src="<?= Helper::asset('js/main.js') ?>"></script>
</body>
</html>
