<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
requireLogin();
if (isAdmin()) { header('Location: '.BASE_URL.'/admin/pages/salary-sheet.php'); exit(); }

$pageTitle = 'Bảng lương cá nhân';
$db = getDB();
$id_nv = $_SESSION['id_nhan_vien'];

$thang = (int)($_GET['thang'] ?? date('n'));
$nam = (int)($_GET['nam'] ?? date('Y'));

$stmt = $db->prepare("
    SELECT bl.*, nv.ho_ten, nv.ma_nv, pb.ten_pb, cv.ten_cv
    FROM bang_luong bl
    JOIN nhan_vien nv ON bl.id_nhan_vien=nv.id
    LEFT JOIN phong_ban pb ON nv.id_phong_ban=pb.id
    LEFT JOIN chuc_vu cv ON nv.id_chuc_vu=cv.id
    WHERE bl.id_nhan_vien=? AND bl.thang=? AND bl.nam=?
");
$stmt->execute([$id_nv, $thang, $nam]);
$luong = $stmt->fetch();

// Lịch sử lương
$history = $db->prepare("SELECT thang, nam, thuc_linh, trang_thai FROM bang_luong WHERE id_nhan_vien=? ORDER BY nam DESC, thang DESC LIMIT 12");
$history->execute([$id_nv]); $history = $history->fetchAll();

$months = range(1,12); $years = range(date('Y')-2, date('Y'));
?>
<!DOCTYPE html>
<html lang="vi">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Bảng lương - <?= APP_NAME ?></title>
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
        <div><h1>Bảng lương cá nhân</h1><p>Xem chi tiết lương theo từng tháng</p></div>
        <?php if ($luong): ?>
        <button class="btn btn-warning" onclick="window.print()"><i class="fas fa-print"></i> In bảng lương</button>
        <?php endif; ?>
      </div>

      <!-- Filter -->
      <div class="card no-print" style="margin-bottom:16px;">
        <div class="card-body" style="padding:14px 20px;">
          <form method="GET" style="display:flex;gap:8px;align-items:center;">
            <select name="thang" class="filter-input">
              <?php foreach ($months as $m): ?><option value="<?= $m ?>" <?= $m==$thang?'selected':'' ?>>Tháng <?= $m ?></option><?php endforeach; ?>
            </select>
            <select name="nam" class="filter-input">
              <?php foreach ($years as $y): ?><option value="<?= $y ?>" <?= $y==$nam?'selected':'' ?>><?= $y ?></option><?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Xem</button>
          </form>
        </div>
      </div>

      <?php if (!$luong): ?>
      <div class="card"><div class="card-body"><div class="empty-state">
        <i class="fas fa-file-invoice-dollar"></i>
        <h3>Chưa có dữ liệu lương tháng <?= $thang ?>/<?= $nam ?></h3>
        <p>Bộ phận kế toán chưa tạo bảng lương cho tháng này</p>
      </div></div></div>
      <?php else: ?>

      <!-- Chi tiết lương -->
      <div class="grid-2">
        <div class="card">
          <div class="card-header">
            <span class="card-title"><i class="fas fa-file-invoice-dollar"></i> Chi tiết lương T<?= $thang ?>/<?= $nam ?></span>
            <?= badgeTrangThaiLuong($luong['trang_thai']) ?>
          </div>
          <div class="card-body">
            <table style="width:100%;border-collapse:collapse;">
              <?php
              $rows = [
                ['header', 'THU NHẬP'],
                ['Lương cơ bản', formatMoney($luong['luong_co_ban']), ''],
                ['Ngày làm: '.$luong['so_ngay_lam'].'/'.$luong['so_ngay_chuan'], '', ''],
                ['Lương theo công', formatMoney($luong['luong_theo_cong']), ''],
                ['Phụ cấp ăn trưa', formatMoney($luong['phu_cap_an_trua']), 'up'],
                ['Phụ cấp xăng xe', formatMoney($luong['phu_cap_xang_xe']), 'up'],
                ['Phụ cấp khác', formatMoney($luong['phu_cap_khac']), 'up'],
                ['Thưởng KPI', formatMoney($luong['thuong_kpi']), 'up'],
                ['Thưởng khác', formatMoney($luong['thuong_khac']), 'up'],
                ['header', 'KHẤU TRỪ'],
                ['Bảo hiểm xã hội (8%)', '- '.formatMoney($luong['bao_hiem_xa_hoi']), 'down'],
                ['Bảo hiểm y tế (1.5%)', '- '.formatMoney($luong['bao_hiem_y_te']), 'down'],
                ['Thuế TNCN', '- '.formatMoney($luong['thue_tncn']), 'down'],
                ['Phạt / khấu trừ', '- '.formatMoney($luong['phat_di_muon']+$luong['khau_tru_khac']), 'down'],
              ];
              foreach ($rows as $row):
                if ($row[0] === 'header'): ?>
                <tr><td colspan="2" style="padding:10px 0 4px;font-size:11px;font-weight:700;letter-spacing:1px;color:var(--gray);text-transform:uppercase;border-top:1px solid var(--border);"><?= $row[1] ?></td></tr>
                <?php else: ?>
                <tr>
                  <td style="padding:7px 0;font-size:13px;color:var(--gray);"><?= $row[0] ?></td>
                  <td style="padding:7px 0;font-size:13px;font-weight:600;text-align:right;color:<?= $row[2]==='up'?'var(--success)':($row[2]==='down'?'var(--danger)':'var(--dark)') ?>;"><?= $row[1] ?></td>
                </tr>
                <?php endif;
              endforeach; ?>
              <tr style="border-top:2px solid var(--primary);">
                <td style="padding:12px 0;font-weight:800;font-size:15px;">THỰC LĨNH</td>
                <td style="padding:12px 0;font-weight:800;font-size:20px;color:var(--success);text-align:right;"><?= formatMoney($luong['thuc_linh']) ?></td>
              </tr>
            </table>
          </div>
        </div>

        <!-- Lịch sử lương -->
        <div class="card no-print">
          <div class="card-header"><span class="card-title"><i class="fas fa-history"></i> Lịch sử lương 12 tháng gần nhất</span></div>
          <div class="card-body">
            <div class="chart-container" style="height:180px;margin-bottom:16px;">
              <canvas id="chartHistory"></canvas>
            </div>
            <table style="width:100%;border-collapse:collapse;">
              <?php foreach ($history as $h): ?>
              <tr style="border-bottom:1px solid var(--border);">
                <td style="padding:8px 0;font-size:13px;">Tháng <?= $h['thang'] ?>/<?= $h['nam'] ?></td>
                <td style="text-align:right;font-weight:700;color:var(--success);font-size:13px;"><?= formatMoney($h['thuc_linh']) ?></td>
                <td style="text-align:right;"><?= badgeTrangThaiLuong($h['trang_thai']) ?></td>
              </tr>
              <?php endforeach; ?>
            </table>
          </div>
        </div>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php if (!empty($history)): ?>
<script src="../../assets/js/main.js"></script>
<script>
const histData = <?= json_encode(array_reverse($history), JSON_UNESCAPED_UNICODE) ?>;
new Chart(document.getElementById('chartHistory'), {
  type: 'line',
  data: {
    labels: histData.map(d => `T${d.thang}/${d.nam}`),
    datasets: [{ label: 'Thực lĩnh', data: histData.map(d => parseFloat(d.thuc_linh)), borderColor: '#4f46e5', backgroundColor: 'rgba(79,70,229,.1)', tension: 0.4, fill: true, pointRadius: 4 }]
  },
  options: { responsive:true, maintainAspectRatio:false, plugins:{legend:{display:false}}, scales:{y:{ticks:{callback:v=>(v/1000000).toFixed(1)+'M'}}} }
});
</script>
<?php endif; ?>
</body></html>
