<header class="header">
  <div class="header-left">
    <button class="header-btn" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>
    <span class="header-title"><?= $pageTitle ?? 'Dashboard' ?></span>
  </div>
  <div class="header-right">
    <button class="header-btn" data-tooltip="Thông báo" onclick="window.location='<?= BASE_URL ?>/admin/pages/leave-requests.php'">
      <i class="fas fa-bell"></i>
      <?php
      $db_h = getDB();
      $cnt_h = $db_h->query("SELECT COUNT(*) FROM don_nghi_phep WHERE trang_thai='Chờ duyệt'")->fetchColumn();
      if ($cnt_h > 0): ?>
      <span class="notif-dot"></span>
      <?php endif; ?>
    </button>
    <div class="dropdown">
      <div class="header-user" onclick="this.parentElement.querySelector('.dropdown-menu').classList.toggle('show')">
        <div class="header-user-avatar"><?= mb_substr($_SESSION['ho_ten'] ?? 'A', 0, 1) ?></div>
        <span><?= clean($_SESSION['ho_ten'] ?? 'Admin') ?></span>
        <i class="fas fa-chevron-down" style="font-size:10px;color:var(--gray);"></i>
      </div>
      <div class="dropdown-menu">
        <a href="<?= BASE_URL ?>/admin/pages/employee-detail.php?id=<?= $_SESSION['id_nhan_vien'] ?>" class="dropdown-item"><i class="fas fa-id-card"></i> Hồ sơ của tôi</a>
        <a href="<?= BASE_URL ?>/admin/pages/profile.php" class="dropdown-item"><i class="fas fa-user-edit"></i> Cập nhật thông tin</a>
        <a href="<?= BASE_URL ?>/admin/pages/accounts.php" class="dropdown-item"><i class="fas fa-user-shield"></i> Tài khoản</a>
        <div class="dropdown-divider"></div>
        <a href="<?= BASE_URL ?>/logout.php" class="dropdown-item danger"><i class="fas fa-sign-out-alt"></i> Đăng xuất</a>
      </div>
    </div>
  </div>
</header>
