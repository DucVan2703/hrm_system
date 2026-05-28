<header class="header">
  <div class="header-left">
    <button class="header-btn" id="toggle-sidebar" onclick="toggleSidebar()">
      <i class="fas fa-bars"></i>
    </button>
    <span class="header-title"><?= $pageTitle ?? 'Trang chủ' ?></span>
  </div>
  <div class="header-right">
    <div class="dropdown">
      <div class="header-user" onclick="this.parentElement.querySelector('.dropdown-menu').classList.toggle('show')">
        <div class="header-user-avatar"><?= mb_substr($_SESSION['ho_ten'] ?? 'U', 0, 1) ?></div>
        <span><?= clean($_SESSION['ho_ten'] ?? 'Người dùng') ?></span>
        <i class="fas fa-chevron-down" style="font-size:10px;color:var(--gray);"></i>
      </div>
      <div class="dropdown-menu">
        <a href="<?= BASE_URL ?>/employee/pages/profile.php" class="dropdown-item"><i class="fas fa-id-card"></i> Hồ sơ</a>
        <a href="<?= BASE_URL ?>/employee/pages/change-password.php" class="dropdown-item"><i class="fas fa-key"></i> Đổi mật khẩu</a>
        <div class="dropdown-divider"></div>
        <a href="<?= BASE_URL ?>/logout.php" class="dropdown-item danger"><i class="fas fa-sign-out-alt"></i> Đăng xuất</a>
      </div>
    </div>
  </div>
</header>
