<?php $current_page = basename($_SERVER['PHP_SELF']); ?>
<div class="sidebar" id="sidebar">
  <div class="sidebar-logo">
    <img src="<?= BASE_URL ?>/assets/images/logo.png" alt="Logo" style="width: 44px; height: 44px; object-fit: contain; border-radius: 8px;">
    <div class="logo-text">
      <h2>Đại Học Thành Đông</h2>
      <p>Quản lý lương CBGV</p>
    </div>
  </div>

  <nav class="sidebar-nav">
    <div class="nav-group">
      <span class="nav-group-title">Tổng Quan</span>
      <a href="<?= BASE_URL ?>/admin/index.php" class="nav-item <?= $current_page === 'index.php' && strpos($_SERVER['PHP_SELF'], 'admin') !== false ? 'active' : '' ?>">
        <i class="fas fa-home"></i> Dashboard
      </a>
    </div>

    <div class="nav-group">
      <span class="nav-group-title">Cá Nhân</span>
      <a href="<?= BASE_URL ?>/admin/pages/my-attendance.php" class="nav-item <?= $current_page === 'my-attendance.php' ? 'active' : '' ?>">
        <i class="fas fa-fingerprint"></i> Chấm công cá nhân
      </a>
      <a href="<?= BASE_URL ?>/admin/pages/employee-detail.php?id=<?= $_SESSION['id_nhan_vien'] ?>" class="nav-item <?= $current_page === 'employee-detail.php' && ($_GET['id'] ?? 0) == ($_SESSION['id_nhan_vien'] ?? 0) ? 'active' : '' ?>">
        <i class="fas fa-id-card"></i> Hồ sơ của tôi
      </a>
    </div>

    <?php if (isHR()): ?>
    <div class="nav-group">
      <span class="nav-group-title">Quản Lý Nhân Sự</span>
      <a href="<?= BASE_URL ?>/admin/pages/employees.php" class="nav-item <?= $current_page === 'employees.php' ? 'active' : '' ?>">
        <i class="fas fa-users"></i> Nhân viên
      </a>
      <a href="<?= BASE_URL ?>/admin/pages/contracts.php" class="nav-item <?= $current_page === 'contracts.php' ? 'active' : '' ?>">
        <i class="fas fa-file-contract"></i> Hợp đồng
      </a>
      <a href="<?= BASE_URL ?>/admin/pages/departments.php" class="nav-item <?= $current_page === 'departments.php' ? 'active' : '' ?>">
        <i class="fas fa-sitemap"></i> Phòng ban
      </a>
      <a href="<?= BASE_URL ?>/admin/pages/positions.php" class="nav-item <?= $current_page === 'positions.php' ? 'active' : '' ?>">
        <i class="fas fa-user-tie"></i> Chức vụ
      </a>
      <a href="<?= BASE_URL ?>/admin/pages/leave-requests.php" class="nav-item <?= $current_page === 'leave-requests.php' ? 'active' : '' ?>">
        <i class="fas fa-calendar-alt"></i> Đơn nghỉ phép
      </a>
    </div>
    <?php endif; ?>

    <?php if (isHR()): ?>
    <div class="nav-group">
      <span class="nav-group-title">Công & Chấm Công</span>
      <a href="<?= BASE_URL ?>/admin/pages/attendance-daily.php" class="nav-item <?= $current_page === 'attendance-daily.php' ? 'active' : '' ?>">
        <i class="fas fa-calendar-check"></i> Điểm danh hàng ngày
      </a>
      <a href="<?= BASE_URL ?>/admin/pages/timekeeping.php" class="nav-item <?= $current_page === 'timekeeping.php' ? 'active' : '' ?>">
        <i class="fas fa-clock"></i> Bảng chấm công
      </a>
    </div>
    <?php endif; ?>

    <?php if (isKetoan()): ?>
    <div class="nav-group">
      <span class="nav-group-title">Lương & Phúc Lợi</span>
      <a href="<?= BASE_URL ?>/admin/pages/payroll.php" class="nav-item <?= $current_page === 'payroll.php' ? 'active' : '' ?>">
        <i class="fas fa-calculator"></i> Tính lương
      </a>
      <a href="<?= BASE_URL ?>/admin/pages/salary-sheet.php" class="nav-item <?= $current_page === 'salary-sheet.php' ? 'active' : '' ?>">
        <i class="fas fa-table"></i> Bảng lương tháng
      </a>
      <a href="<?= BASE_URL ?>/admin/pages/allowances.php" class="nav-item <?= $current_page === 'allowances.php' ? 'active' : '' ?>">
        <i class="fas fa-hand-holding-usd"></i> Danh mục phụ cấp
      </a>
    </div>
    <?php endif; ?>

    <div class="nav-group">
      <span class="nav-group-title">Báo Cáo</span>
      <a href="<?= BASE_URL ?>/admin/pages/reports.php" class="nav-item <?= $current_page === 'reports.php' ? 'active' : '' ?>">
        <i class="fas fa-chart-bar"></i> Báo cáo thống kê
      </a>
    </div>

    <?php if (isOnlyAdmin()): ?>
    <div class="nav-group">
      <span class="nav-group-title">Hệ Thống</span>
      <a href="<?= BASE_URL ?>/admin/pages/accounts.php" class="nav-item <?= $current_page === 'accounts.php' ? 'active' : '' ?>">
        <i class="fas fa-user-shield"></i> Tài khoản & Phân quyền
      </a>
      <a href="<?= BASE_URL ?>/admin/pages/quanly_troly.php" class="nav-item <?= $current_page === 'quanly_troly.php' ? 'active' : '' ?>">
        <i class="fas fa-robot"></i> Quản lý Trợ lý ảo
      </a>
      <a href="<?= BASE_URL ?>/admin/pages/settings.php" class="nav-item <?= $current_page === 'settings.php' ? 'active' : '' ?>">
        <i class="fas fa-cog"></i> Cấu hình hệ thống
      </a>
    </div>
    <?php endif; ?>

    <div class="nav-group">
      <a href="<?= BASE_URL ?>/logout.php" class="nav-item" style="color:var(--danger);">
        <i class="fas fa-sign-out-alt"></i> Đăng xuất
      </a>
    </div>
  </nav>

  <div class="sidebar-footer">
    <div class="user-mini">
      <div class="user-mini-avatar"><?= mb_substr($_SESSION['ho_ten'] ?? 'A', 0, 1) ?></div>
      <div class="user-mini-info">
        <p><?= clean($_SESSION['ho_ten'] ?? 'Admin') ?></p>
        <span>
          <?php 
          $vaitro = $_SESSION['vai_tro'] ?? 'admin';
          if($vaitro == 'admin') echo 'Quản trị viên';
          elseif($vaitro == 'hr') echo 'Nhân sự';
          elseif($vaitro == 'ketoan') echo 'Kế toán';
          else echo 'Nhân viên';
          ?>
        </span>
      </div>
    </div>
  </div>
</div>
