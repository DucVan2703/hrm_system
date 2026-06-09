<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= Helper::clean($pageTitle) ?></title>
<link rel="stylesheet" href="<?= Helper::asset('css/style.css') ?>">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<div class="layout">
  <?php require __DIR__ . '/../layouts/sidebar.php'; ?>
  <div class="main-content">
    <?php require __DIR__ . '/../layouts/header.php'; ?>
    <div class="page-content">
      <div class="page-header"><div><h1>Cau hinh he thong</h1><p>Xem nhanh thong tin ket noi va duong dan ung dung</p></div></div>
      <?php $flash = $_SESSION['flash'] ?? null; if ($flash): unset($_SESSION['flash']); ?><div class="alert alert-<?= Helper::clean($flash['type']) ?>"><i class="fas fa-info-circle"></i> <?= Helper::clean($flash['message']) ?></div><?php endif; ?>
      <div class="card">
        <div class="card-header"><span class="card-title"><i class="fas fa-cog"></i> Cau hinh hien tai</span></div>
        <div class="card-body">
          <div class="form-row col-2">
            <div class="form-group"><label class="form-label">Host</label><input class="form-control" value="<?= Helper::clean($config['host'] ?? '') ?>" readonly></div>
            <div class="form-group"><label class="form-label">Database</label><input class="form-control" value="<?= Helper::clean($config['dbname'] ?? '') ?>" readonly></div>
            <div class="form-group"><label class="form-label">Username</label><input class="form-control" value="<?= Helper::clean($config['username'] ?? '') ?>" readonly></div>
            <div class="form-group"><label class="form-label">Base URL</label><input class="form-control" value="<?= Helper::clean($config['base_url'] ?? '') ?>" readonly></div>
          </div>
          <form method="POST"><button class="btn btn-primary"><i class="fas fa-check"></i> Kiem tra</button></form>
        </div>
      </div>
    </div>
  </div>
</div>
<script src="<?= Helper::asset('js/main.js') ?>"></script>
</body>
</html>
