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
      <div class="page-header"><div><h1>Bao cao thong ke</h1><p>Tong hop nhan su, phong ban, quy luong va don nghi phep</p></div></div>
      <div class="card" style="margin-bottom:16px;"><div class="card-body" style="padding:14px 20px;">
        <form method="GET" style="display:flex;gap:8px;align-items:center;">
          <select name="thang" class="filter-input"><?php foreach ($months as $m): ?><option value="<?= $m ?>" <?= $m == $thang ? 'selected' : '' ?>>Thang <?= $m ?></option><?php endforeach; ?></select>
          <select name="nam" class="filter-input"><?php foreach ($years as $y): ?><option value="<?= $y ?>" <?= $y == $nam ? 'selected' : '' ?>><?= $y ?></option><?php endforeach; ?></select>
          <button class="btn btn-primary"><i class="fas fa-search"></i> Xem</button>
          <button type="button" class="btn btn-warning" onclick="window.print()"><i class="fas fa-print"></i> In</button>
        </form>
      </div></div>
      <div class="stats-grid">
        <div class="stat-card primary"><div class="stat-icon"><i class="fas fa-users"></i></div><div class="stat-info"><div class="stat-value"><?= $stats['employees'] ?></div><div class="stat-label">Tong nhan vien</div></div></div>
        <div class="stat-card success"><div class="stat-icon"><i class="fas fa-user-check"></i></div><div class="stat-info"><div class="stat-value"><?= $stats['active'] ?></div><div class="stat-label">Dang lam</div></div></div>
        <div class="stat-card info"><div class="stat-icon"><i class="fas fa-sitemap"></i></div><div class="stat-info"><div class="stat-value"><?= $stats['departments'] ?></div><div class="stat-label">Phong ban</div></div></div>
        <div class="stat-card warning"><div class="stat-icon"><i class="fas fa-money-bill-wave"></i></div><div class="stat-info"><div class="stat-value" style="font-size:16px;"><?= Helper::formatMoney($stats['payroll']) ?></div><div class="stat-label">Quy luong thang <?= $thang ?>/<?= $nam ?></div></div></div>
        <div class="stat-card danger"><div class="stat-icon"><i class="fas fa-calendar-alt"></i></div><div class="stat-info"><div class="stat-value"><?= $stats['leaves_pending'] ?></div><div class="stat-label">Don cho duyet</div></div></div>
      </div>
    </div>
  </div>
</div>
<script src="<?= Helper::asset('js/main.js') ?>"></script>
</body>
</html>
