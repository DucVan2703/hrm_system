<?php
$current_page = $current_page ?? '';
?>
<div class="sidebar" id="sidebar">
  <div class="sidebar-logo">
    <img src="<?= Helper::asset('images/logo.png') ?>" alt="Logo" style="width: 44px; height: 44px; object-fit: contain; border-radius: 8px;">
    <div class="logo-text">
      <h2>Đại Học Thành Đông</h2>
      <p>Quản lý lương CBGV</p>
    </div>
  </div>

  <nav class="sidebar-nav">
    <div class="nav-group">
      <span class="nav-group-title">Tổng Quan</span>
      <?php if (Helper::isAdmin()): ?>
      <a href="<?= Helper::route('dashboard') ?>" class="nav-item <?= $current_page === 'dashboard' ? 'active' : '' ?>">
        <i class="fas fa-home"></i> Dashboard
      </a>
      <?php else: ?>
      <a href="<?= Helper::route('employee/dashboard') ?>" class="nav-item <?= $current_page === 'employee-dashboard' ? 'active' : '' ?>">
        <i class="fas fa-home"></i> Trang nhân viên
      </a>
      <?php endif; ?>
    </div>

    <div class="nav-group">
      <span class="nav-group-title">Cá Nhân</span>
      <a href="<?= Helper::route('attendance/my') ?>" class="nav-item <?= $current_page === 'my-attendance' ? 'active' : '' ?>">
        <i class="fas fa-fingerprint"></i> Chấm công cá nhân
      </a>
  <a href="<?= Helper::route('employee/profile') ?>" 
   class="nav-item <?= $current_page === 'my-profile' ? 'active' : '' ?>">
    <i class="fas fa-id-card"></i> Hồ sơ của tôi
</a>
<a href="<?= Helper::route('employee/leave') ?>" 
   class="nav-item <?= $current_page === 'leave' ? 'active' : '' ?>">
    <i class="fas fa-calendar-plus"></i> Đơn nghỉ phép
</a>
    </div>

    <?php if (Helper::isHR()): ?>
    <div class="nav-group">
      <span class="nav-group-title">Quản Lý Nhân Sự</span>
      <a href="<?= Helper::route('employee') ?>" class="nav-item <?= $current_page === 'employees' ? 'active' : '' ?>">
        <i class="fas fa-users"></i> Nhân viên
      </a>
      <a href="<?= Helper::route('contracts') ?>" class="nav-item <?= $current_page === 'contracts' ? 'active' : '' ?>">
        <i class="fas fa-file-contract"></i> Hợp đồng
      </a>
      <a href="<?= Helper::route('departments') ?>" class="nav-item <?= $current_page === 'departments' ? 'active' : '' ?>">
        <i class="fas fa-sitemap"></i> Phòng ban
      </a>
      <a href="<?= Helper::route('positions') ?>" class="nav-item <?= $current_page === 'positions' ? 'active' : '' ?>">
        <i class="fas fa-user-tie"></i> Chức vụ
      </a>
      <a href="<?= Helper::route('leave-requests') ?>" class="nav-item <?= $current_page === 'leave-requests' ? 'active' : '' ?>">
        <i class="fas fa-calendar-alt"></i> Đơn nghỉ phép
      </a>
    </div>
    <?php endif; ?>

    <?php if (Helper::isHR()): ?>
    <div class="nav-group">
      <span class="nav-group-title">Công & Chấm Công</span>
      <a href="<?= Helper::route('attendance/daily') ?>" class="nav-item <?= $current_page === 'attendance-daily' ? 'active' : '' ?>">
        <i class="fas fa-calendar-check"></i> Điểm danh hàng ngày
      </a>
      <a href="<?= Helper::route('timekeeping') ?>" class="nav-item <?= $current_page === 'timekeeping' ? 'active' : '' ?>">
        <i class="fas fa-clock"></i> Bảng chấm công
      </a>
    </div>
    <?php endif; ?>

    <?php if (Helper::isKetoan()): ?>
    <div class="nav-group">
      <span class="nav-group-title">Lương & Phúc Lợi</span>
      <a href="<?= Helper::route('payroll') ?>" class="nav-item <?= $current_page === 'payroll' ? 'active' : '' ?>">
        <i class="fas fa-calculator"></i> Tính lương
      </a>
      <a href="<?= Helper::route('salary-sheet') ?>" class="nav-item <?= $current_page === 'salary-sheet' ? 'active' : '' ?>">
        <i class="fas fa-table"></i> Bảng lương tháng
      </a>
      <a href="<?= Helper::route('allowances') ?>" class="nav-item <?= $current_page === 'allowances' ? 'active' : '' ?>">
        <i class="fas fa-hand-holding-usd"></i> Danh mục phụ cấp
      </a>
    </div>
    <?php endif; ?>

    <div class="nav-group">
      <span class="nav-group-title">Báo Cáo</span>
      <a href="<?= Helper::route('reports') ?>" class="nav-item <?= $current_page === 'reports' ? 'active' : '' ?>">
        <i class="fas fa-chart-bar"></i> Báo cáo thống kê
      </a>
    </div>

    <?php if (Helper::isOnlyAdmin()): ?>
    <div class="nav-group">
      <span class="nav-group-title">Hệ Thống</span>
      <a href="<?= Helper::route('accounts') ?>" class="nav-item <?= $current_page === 'accounts' ? 'active' : '' ?>">
        <i class="fas fa-user-shield"></i> Tài khoản & Phân quyền
      </a>
      <a href="<?= Helper::route('chatbot') ?>" class="nav-item <?= $current_page === 'chatbot' ? 'active' : '' ?>">
        <i class="fas fa-robot"></i> Quản lý Trợ lý ảo
      </a>
      <a href="<?= Helper::route('settings') ?>" class="nav-item <?= $current_page === 'settings' ? 'active' : '' ?>">
        <i class="fas fa-cog"></i> Cấu hình hệ thống
      </a>
    </div>
    <?php endif; ?>

    <div class="nav-group">
      <a href="<?= Helper::route('auth/logout') ?>" class="nav-item" style="color:var(--danger);">
        <i class="fas fa-sign-out-alt"></i> Đăng xuất
      </a>
    </div>
  </nav>

  <div class="sidebar-footer">
    <div class="user-mini">
      <div class="user-mini-avatar"><?= mb_substr($_SESSION['ho_ten'] ?? 'A', 0, 1) ?></div>
      <div class="user-mini-info">
        <p><?= Helper::clean($_SESSION['ho_ten'] ?? 'Admin') ?></p>
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
