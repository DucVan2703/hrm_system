<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard - <?= Helper::clean($pageTitle) ?></title>
<meta name="description" content="Tổng quan hệ thống quản lý nhân sự">
<link rel="stylesheet" href="<?= Helper::asset('css/style.css') ?>">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>
<body>
<div class="layout">
  <?php 
  $current_page = 'dashboard';
  require __DIR__ . '/../layouts/sidebar.php'; 
  ?>
  <div class="main-content">
    <?php require __DIR__ . '/../layouts/header.php'; ?>
    <div class="page-content">

      <!-- Page Header -->
      <div class="page-header">
        <div>
          <h1>Dashboard</h1>
          <p>Tổng quan hệ thống quản lý nhân sự và tiền lương</p>
        </div>
        <div class="page-actions">
          <span style="font-size:13px;color:var(--gray);">
            <i class="fas fa-clock"></i> <?= date('d/m/Y H:i') ?>
          </span>
        </div>
      </div>

      <!-- STAT CARDS -->
      <div class="stats-grid">
        <div class="stat-card primary">
          <div class="stat-icon"><i class="fas fa-users"></i></div>
          <div class="stat-info">
            <div class="stat-value"><?= $tq['tong_nv'] ?></div>
            <div class="stat-label">Tổng nhân viên</div>
          </div>
        </div>
        <div class="stat-card success">
          <div class="stat-icon"><i class="fas fa-user-check"></i></div>
          <div class="stat-info">
            <div class="stat-value"><?= $tq['dang_lam'] ?></div>
            <div class="stat-label">Đang làm việc</div>
          </div>
        </div>
        <div class="stat-card danger">
          <div class="stat-icon"><i class="fas fa-user-slash"></i></div>
          <div class="stat-info">
            <div class="stat-value"><?= $tq['nghi_viec'] ?></div>
            <div class="stat-label">Đã nghỉ việc</div>
          </div>
        </div>
        <div class="stat-card warning">
          <div class="stat-icon"><i class="fas fa-money-bill-wave"></i></div>
          <div class="stat-info">
            <div class="stat-value" style="font-size:18px;"><?= Helper::formatMoney($tq['quy_luong']) ?></div>
            <div class="stat-label">Quỹ lương T<?= date('n/Y') ?></div>
          </div>
        </div>
        <div class="stat-card info">
          <div class="stat-icon"><i class="fas fa-file-contract"></i></div>
          <div class="stat-info">
            <div class="stat-value"><?= $tq['hop_dong_hh'] ?></div>
            <div class="stat-label">Hợp đồng hiệu lực</div>
          </div>
        </div>
        <div class="stat-card warning">
          <div class="stat-icon"><i class="fas fa-calendar-check"></i></div>
          <div class="stat-info">
            <div class="stat-value"><?= $tq['don_cho_duyet'] ?></div>
            <div class="stat-label">Đơn chờ duyệt</div>
          </div>
        </div>
      </div>

      <!-- CHARTS ROW -->
      <div class="grid-2" style="margin-bottom:24px;">
        <!-- Biểu đồ lương -->
        <div class="card">
          <div class="card-header">
            <span class="card-title"><i class="fas fa-chart-line"></i> Quỹ lương 6 tháng gần nhất</span>
          </div>
          <div class="card-body">
            <div class="chart-container">
              <canvas id="chartLuong"></canvas>
            </div>
          </div>
        </div>

        <!-- Phân bổ nhân viên -->
        <div class="card">
          <div class="card-header">
            <span class="card-title"><i class="fas fa-chart-pie"></i> Nhân viên theo phòng ban</span>
          </div>
          <div class="card-body">
            <div class="chart-container" style="height:200px;">
              <canvas id="chartPB"></canvas>
            </div>
          </div>
        </div>
      </div>

      <!-- BOTTOM ROW -->
      <div class="grid-2">
        <!-- Nhân viên mới -->
        <div class="card">
          <div class="card-header">
            <span class="card-title"><i class="fas fa-user-plus"></i> Nhân viên mới nhất</span>
            <a href="<?= Helper::route('employee') ?>" class="btn btn-outline btn-sm">Xem tất cả</a>
          </div>
          <div class="table-responsive">
            <table class="table">
              <thead>
                <tr><th>Nhân viên</th><th>Phòng ban</th><th>Trạng thái</th><th>Ngày vào</th></tr>
              </thead>
              <tbody>
                <?php foreach ($nvMoi as $nv): ?>
                <tr>
                  <td>
                    <div class="nv-info">
                      <div class="avatar"><?= mb_substr($nv['ho_ten'], 0, 1) ?></div>
                      <div><h4><?= Helper::clean($nv['ho_ten']) ?></h4><p><?= Helper::clean($nv['ma_nv']) ?></p></div>
                    </div>
                  </td>
                  <td><?= Helper::clean($nv['ten_pb'] ?? '—') ?></td>
                  <td><?= Helper::badgeTrangThaiNV($nv['trang_thai']) ?></td>
                  <td><?= Helper::formatDate($nv['ngay_vao_lam']) ?></td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Thống kê nhanh phòng ban -->
        <div class="card">
          <div class="card-header">
            <span class="card-title"><i class="fas fa-sitemap"></i> Nhân viên theo phòng ban</span>
            <a href="<?= Helper::route('departments') ?>" class="btn btn-outline btn-sm">Quản lý</a>
          </div>
          <div class="card-body">
            <?php
            $maxNv = max(array_column($pbStats, 'so_nv') ?: [1]);
            foreach ($pbStats as $pb):
              $pct = $maxNv > 0 ? round($pb['so_nv'] / $maxNv * 100) : 0;
            ?>
            <div style="margin-bottom:14px;">
              <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:5px;">
                <span><?= Helper::clean($pb['ten_pb']) ?></span>
                <b><?= $pb['so_nv'] ?> NV</b>
              </div>
              <div class="progress">
                <div class="progress-bar primary" style="width:<?= $pct ?>%"></div>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>

<script src="<?= Helper::asset('js/main.js') ?>"></script>
<script>
// Biểu đồ lương
const chartData = <?= json_encode($chartData, JSON_UNESCAPED_UNICODE) ?>;
new Chart(document.getElementById('chartLuong'), {
  type: 'bar',
  data: {
    labels: chartData.map(d => d.label),
    datasets: [{
      label: 'Quỹ lương (đ)',
      data: chartData.map(d => d.value),
      backgroundColor: 'rgba(79,70,229,.15)',
      borderColor: '#4f46e5',
      borderWidth: 2,
      borderRadius: 6,
      fill: true,
    }]
  },
  options: {
    responsive: true, maintainAspectRatio: false,
    plugins: { legend: { display: false } },
    scales: {
      y: { ticks: { callback: v => (v/1000000).toFixed(0) + 'M' }, grid: { color: '#f1f5f9' } },
      x: { grid: { display: false } }
    }
  }
});

// Biểu đồ phòng ban
const pbStats = <?= json_encode($pbStats, JSON_UNESCAPED_UNICODE) ?>;
new Chart(document.getElementById('chartPB'), {
  type: 'doughnut',
  data: {
    labels: pbStats.map(d => d.ten_pb),
    datasets: [{
      data: pbStats.map(d => parseInt(d.so_nv)),
      backgroundColor: ['#4f46e5','#06b6d4','#10b981','#f59e0b','#ef4444','#8b5cf6'],
      borderWidth: 2, borderColor: '#fff',
    }]
  },
  options: {
    responsive: true, maintainAspectRatio: false,
    plugins: { legend: { position: 'right', labels: { font: { size: 11 }, boxWidth: 12 } } }
  }
});
</script>
</body>
</html>
