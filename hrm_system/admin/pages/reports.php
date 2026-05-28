<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
requireAdmin();
$pageTitle = 'Báo cáo thống kê';
$db = getDB();

// Thống kê theo tháng
$nam = (int)($_GET['nam'] ?? date('Y'));
$luongTheoThang = [];
for ($m = 1; $m <= 12; $m++) {
    $stmt = $db->prepare("SELECT COALESCE(SUM(thuc_linh),0) as tong, COUNT(id) as so_nv FROM bang_luong WHERE thang=? AND nam=?");
    $stmt->execute([$m, $nam]);
    $luongTheoThang[$m] = $stmt->fetch();
}

// Thống kê nhân sự
$pbStats = $db->query("SELECT pb.ten_pb, COUNT(nv.id) as so_nv, SUM(nv.luong_co_ban) as tong_luong FROM phong_ban pb LEFT JOIN nhan_vien nv ON pb.id=nv.id_phong_ban AND nv.trang_thai='Đang làm' GROUP BY pb.id ORDER BY so_nv DESC")->fetchAll();

// Nhân viên mới trong năm
$nvMoiTrongNam = $db->prepare("SELECT COUNT(*) FROM nhan_vien WHERE YEAR(ngay_vao_lam)=?"); $nvMoiTrongNam->execute([$nam]); $nvMoiCount = $nvMoiTrongNam->fetchColumn();
$nvNghiTrongNam = $db->query("SELECT COUNT(*) FROM nhan_vien WHERE trang_thai='Nghỉ việc'")->fetchColumn();

// Top lương cao
$topLuong = $db->prepare("SELECT nv.ho_ten, nv.ma_nv, pb.ten_pb, cv.ten_cv, bl.thuc_linh FROM bang_luong bl JOIN nhan_vien nv ON bl.id_nhan_vien=nv.id LEFT JOIN phong_ban pb ON nv.id_phong_ban=pb.id LEFT JOIN chuc_vu cv ON nv.id_chuc_vu=cv.id WHERE bl.thang=? AND bl.nam=? ORDER BY bl.thuc_linh DESC LIMIT 10");
$topLuong->execute([date('n'), $nam]);
$topLuong = $topLuong->fetchAll();

$years = range(date('Y')-2, date('Y'));
?>
<!DOCTYPE html>
<html lang="vi">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Báo cáo - <?= APP_NAME ?></title>
<link rel="stylesheet" href="../../assets/css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>
<body>
<div class="layout">
  <?php include '../includes/sidebar.php'; ?>
  <div class="main-content">
    <?php include '../includes/header.php'; ?>
    <div class="page-content">
      <div class="page-header">
        <div><h1>Báo cáo thống kê</h1><p>Phân tích và thống kê nhân sự, quỹ lương</p></div>
        <form method="GET" style="display:flex;gap:8px;">
          <select name="nam" class="filter-input">
            <?php foreach ($years as $y): ?><option value="<?= $y ?>" <?= $y==$nam?'selected':'' ?>><?= $y ?></option><?php endforeach; ?>
          </select>
          <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Xem</button>
        </form>
      </div>

      <!-- Stats -->
      <div class="stats-grid" style="grid-template-columns:repeat(4,1fr);">
        <div class="stat-card primary">
          <div class="stat-icon"><i class="fas fa-user-plus"></i></div>
          <div class="stat-info"><div class="stat-value"><?= $nvMoiCount ?></div><div class="stat-label">NV mới năm <?= $nam ?></div></div>
        </div>
        <div class="stat-card danger">
          <div class="stat-icon"><i class="fas fa-user-slash"></i></div>
          <div class="stat-info"><div class="stat-value"><?= $nvNghiTrongNam ?></div><div class="stat-label">Đã nghỉ việc</div></div>
        </div>
        <div class="stat-card success">
          <div class="stat-icon"><i class="fas fa-money-bill-wave"></i></div>
          <div class="stat-info">
            <div class="stat-value" style="font-size:16px;"><?= formatMoney(array_sum(array_column($luongTheoThang,'tong'))) ?></div>
            <div class="stat-label">Tổng quỹ lương <?= $nam ?></div>
          </div>
        </div>
        <div class="stat-card info">
          <div class="stat-icon"><i class="fas fa-chart-line"></i></div>
          <div class="stat-info">
            <div class="stat-value" style="font-size:16px;">
              <?php $avg = count(array_filter($luongTheoThang,fn($r)=>$r['tong']>0));
              echo $avg > 0 ? formatMoney(array_sum(array_column($luongTheoThang,'tong'))/$avg) : '—'; ?>
            </div>
            <div class="stat-label">Quỹ lương TB/tháng</div>
          </div>
        </div>
      </div>

      <div class="grid-2" style="margin-bottom:24px;">
        <!-- Biểu đồ lương 12 tháng -->
        <div class="card">
          <div class="card-header"><span class="card-title"><i class="fas fa-chart-bar"></i> Quỹ lương theo tháng <?= $nam ?></span></div>
          <div class="card-body"><div class="chart-container"><canvas id="chartLuongNam"></canvas></div></div>
        </div>
        <!-- Biểu đồ nhân viên phòng ban -->
        <div class="card">
          <div class="card-header"><span class="card-title"><i class="fas fa-chart-pie"></i> Phân bổ nhân viên theo phòng ban</span></div>
          <div class="card-body"><div class="chart-container"><canvas id="chartPB"></canvas></div></div>
        </div>
      </div>

      <div class="grid-2">
        <!-- Thống kê phòng ban -->
        <div class="card">
          <div class="card-header"><span class="card-title"><i class="fas fa-sitemap"></i> Thống kê theo phòng ban</span></div>
          <div class="table-responsive">
            <table class="table">
              <thead><tr><th>Phòng ban</th><th>Số NV</th><th>Tổng lương cơ bản</th></tr></thead>
              <tbody>
                <?php foreach ($pbStats as $pb): ?>
                <tr>
                  <td><b><?= clean($pb['ten_pb']) ?></b></td>
                  <td><span class="badge badge-primary"><?= $pb['so_nv'] ?> NV</span></td>
                  <td><?= formatMoney($pb['tong_luong'] ?? 0) ?></td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Top lương -->
        <div class="card">
          <div class="card-header"><span class="card-title"><i class="fas fa-trophy"></i> Top 10 lương cao nhất tháng <?= date('n') ?>/<?= $nam ?></span></div>
          <div class="table-responsive">
            <table class="table">
              <thead><tr><th>Nhân viên</th><th>Chức vụ</th><th>Thực lĩnh</th></tr></thead>
              <tbody>
                <?php if (empty($topLuong)): ?>
                <tr><td colspan="3" style="text-align:center;color:var(--gray);">Chưa có dữ liệu lương tháng này</td></tr>
                <?php else: $rank=1; foreach ($topLuong as $r): ?>
                <tr>
                  <td>
                    <div style="display:flex;align-items:center;gap:8px;">
                      <span style="width:22px;height:22px;border-radius:50%;background:<?= $rank<=3?'var(--warning)':'var(--border)' ?>;color:<?= $rank<=3?'#fff':'var(--gray)' ?>;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;flex-shrink:0;"><?= $rank++ ?></span>
                      <div><h4 style="font-size:13px;"><?= clean($r['ho_ten']) ?></h4><p style="font-size:11px;color:var(--gray);"><?= clean($r['ma_nv']) ?></p></div>
                    </div>
                  </td>
                  <td style="font-size:12px;"><?= clean($r['ten_cv'] ?? '—') ?></td>
                  <td><b style="color:var(--success);"><?= formatMoney($r['thuc_linh']) ?></b></td>
                </tr>
                <?php endforeach; endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="../../assets/js/main.js"></script>
<script>
const luongData = <?= json_encode(array_values(array_map(fn($r) => (float)$r['tong'], $luongTheoThang))) ?>;
const luongLabels = <?= json_encode(array_map(fn($m) => "T$m", range(1,12))) ?>;
new Chart(document.getElementById('chartLuongNam'), {
  type: 'bar',
  data: {
    labels: luongLabels,
    datasets: [{
      label: 'Quỹ lương', data: luongData,
      backgroundColor: luongData.map(v => v > 0 ? 'rgba(79,70,229,.2)' : 'rgba(200,200,200,.2)'),
      borderColor: luongData.map(v => v > 0 ? '#4f46e5' : '#ccc'),
      borderWidth: 2, borderRadius: 6,
    }]
  },
  options: { responsive:true, maintainAspectRatio:false, plugins:{legend:{display:false}}, scales:{y:{ticks:{callback:v=>(v/1000000).toFixed(0)+'M'}}} }
});

const pbData = <?= json_encode($pbStats, JSON_UNESCAPED_UNICODE) ?>;
new Chart(document.getElementById('chartPB'), {
  type: 'doughnut',
  data: {
    labels: pbData.map(d => d.ten_pb),
    datasets: [{ data: pbData.map(d => parseInt(d.so_nv)), backgroundColor: ['#4f46e5','#06b6d4','#10b981','#f59e0b','#ef4444','#8b5cf6'], borderWidth: 2, borderColor:'#fff' }]
  },
  options: { responsive:true, maintainAspectRatio:false, plugins:{legend:{position:'right',labels:{font:{size:11},boxWidth:12}}} }
});
</script>
</body></html>
