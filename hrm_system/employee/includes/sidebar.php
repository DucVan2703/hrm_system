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
      <a href="<?= BASE_URL ?>/employee/index.php" class="nav-item <?= $current_page === 'index.php' && strpos($_SERVER['PHP_SELF'], 'employee') !== false ? 'active' : '' ?>">
        <i class="fas fa-home"></i> Dashboard
      </a>
    </div>

    <div class="nav-group">
      <span class="nav-group-title">Cá Nhân</span>
      <a href="<?= BASE_URL ?>/employee/pages/profile.php" class="nav-item <?= $current_page === 'profile.php' ? 'active' : '' ?>">
        <i class="fas fa-user"></i> Hồ sơ của tôi
      </a>
      <a href="<?= BASE_URL ?>/employee/pages/attendance.php" class="nav-item <?= $current_page === 'attendance.php' ? 'active' : '' ?>">
        <i class="fas fa-calendar-check"></i> Chấm công
      </a>
      <a href="<?= BASE_URL ?>/employee/pages/salary.php" class="nav-item <?= $current_page === 'salary.php' ? 'active' : '' ?>">
        <i class="fas fa-file-invoice-dollar"></i> Bảng lương
      </a>
      <a href="<?= BASE_URL ?>/employee/pages/leave.php" class="nav-item <?= $current_page === 'leave.php' ? 'active' : '' ?>">
        <i class="fas fa-calendar-minus"></i> Xin nghỉ phép
      </a>
    </div>

    <div class="nav-group">
      <span class="nav-group-title">Hệ Thống</span>
      <a href="<?= BASE_URL ?>/employee/pages/change-password.php" class="nav-item <?= $current_page === 'change-password.php' ? 'active' : '' ?>">
        <i class="fas fa-key"></i> Đổi mật khẩu
      </a>
      <a href="<?= BASE_URL ?>/logout.php" class="nav-item" style="color:var(--danger);">
        <i class="fas fa-sign-out-alt"></i> Đăng xuất
      </a>
    </div>
  </nav>

  <div class="sidebar-footer">
    <div class="user-mini">
      <div class="user-mini-avatar"><?= mb_substr($_SESSION['ho_ten'] ?? 'A', 0, 1) ?></div>
      <div class="user-mini-info">
        <p><?= clean($_SESSION['ho_ten'] ?? 'Nhân viên') ?></p>
        <span>
          <?php 
          $vaitro = $_SESSION['vai_tro'] ?? 'nhanvien';
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
