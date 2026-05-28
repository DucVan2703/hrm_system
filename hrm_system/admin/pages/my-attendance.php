<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
requireLogin();
if (!isAdmin()) { header('Location: '.BASE_URL.'/employee/pages/attendance.php'); exit(); }

$pageTitle = 'Chấm công hàng ngày';
$db = getDB();
$id_nv = $_SESSION['id_nhan_vien'];
$today = date('Y-m-d');
$now = date('H:i:s');

// Cấu hình giờ giấc (có thể lấy từ Setting nếu có)
$start_time = "08:00:00";
$end_time = "17:00:00";

// Xử lý chấm công
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'checkin') {
        $stmt = $db->prepare("SELECT id FROM cham_cong_chi_tiet WHERE id_nhan_vien = ? AND ngay = ?");
        $stmt->execute([$id_nv, $today]);
        if (!$stmt->fetch()) {
            $trang_thai = ($now > $start_time) ? 'Đi muộn' : 'Đúng giờ';
            $stmt = $db->prepare("INSERT INTO cham_cong_chi_tiet (id_nhan_vien, ngay, gio_vao, trang_thai) VALUES (?, ?, ?, ?)");
            $stmt->execute([$id_nv, $today, $now, $trang_thai]);
            setFlash('success', 'Chấm công VÀO thành công lúc ' . $now . ($trang_thai == 'Đi muộn' ? ' (Đi muộn)' : ''));
        }
    } elseif ($action === 'checkout') {
        $stmt = $db->prepare("SELECT id, gio_vao FROM cham_cong_chi_tiet WHERE id_nhan_vien = ? AND ngay = ?");
        $stmt->execute([$id_nv, $today]);
        $current = $stmt->fetch();
        if ($current) {
            $stmt = $db->prepare("UPDATE cham_cong_chi_tiet SET gio_ra = ? WHERE id = ?");
            $stmt->execute([$now, $current['id']]);
            setFlash('success', 'Chấm công RA thành công lúc ' . $now);
        }
    }
    header('Location: my-attendance.php');
    exit();
}

// Lấy trạng thái hôm nay
$stmt = $db->prepare("SELECT * FROM cham_cong_chi_tiet WHERE id_nhan_vien = ? AND ngay = ?");
$stmt->execute([$id_nv, $today]);
$today_record = $stmt->fetch();

// Lấy lịch sử tháng này
$thang = date('m');
$nam = date('Y');
$stmt = $db->prepare("SELECT * FROM cham_cong_chi_tiet WHERE id_nhan_vien = ? AND MONTH(ngay) = ? AND YEAR(ngay) = ? ORDER BY ngay DESC");
$stmt->execute([$id_nv, $thang, $nam]);
$history = $stmt->fetchAll();

$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .clock-container { text-align: center; padding: 40px 20px; }
        .digital-clock { font-size: 48px; font-weight: 800; color: var(--primary); margin-bottom: 10px; font-family: monospace; }
        .date-display { font-size: 18px; color: var(--gray); margin-bottom: 30px; }
        .attendance-actions { display: flex; gap: 20px; justify-content: center; }
        .btn-attendance { padding: 15px 40px; font-size: 16px; font-weight: 600; border-radius: 12px; cursor: pointer; transition: all 0.3s; display: flex; flex-direction: column; align-items: center; gap: 8px; border: none; }
        .btn-checkin { background: var(--success); color: white; }
        .btn-checkout { background: var(--warning); color: white; }
        .btn-attendance:disabled { background: var(--border); color: var(--gray); cursor: not-allowed; }
        .status-badge { padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; }
        .status-on { background: #dcfce7; color: #15803d; }
        .status-off { background: #fee2e2; color: #b91c1c; }
    </style>
</head>
<body>
<div class="layout">
    <?php include '../includes/sidebar.php'; ?>
    <div class="main-content">
        <?php include '../includes/header.php'; ?>
        <div class="page-content">
            <div class="page-header">
                <div>
                    <h1>Chấm công hàng ngày</h1>
                    <p>Ghi nhận thời gian ra vào làm việc</p>
                </div>
            </div>

            <?php if ($flash): ?>
                <div class="alert alert-<?= $flash['type'] ?>">
                    <i class="fas fa-info-circle"></i> <?= clean($flash['message']) ?>
                </div>
            <?php endif; ?>

            <div class="grid-2">
                <div class="card">
                    <div class="card-header">
                        <span class="card-title"><i class="fas fa-fingerprint"></i> Máy chấm công</span>
                    </div>
                    <div class="card-body">
                        <div class="clock-container">
                            <div class="digital-clock" id="clock">00:00:00</div>
                            <div class="date-display"><?= date('l, d/m/Y') ?></div>
                            
                            <form method="POST" class="attendance-actions">
                                <?php if (!$today_record): ?>
                                    <button type="submit" name="action" value="checkin" class="btn-attendance btn-checkin">
                                        <i class="fas fa-sign-in-alt fa-2x"></i>
                                        CHẤM CÔNG VÀO
                                    </button>
                                <?php elseif (!$today_record['gio_ra']): ?>
                                    <div style="text-align:center;">
                                        <div class="status-badge status-on" style="margin-bottom:15px;">Đã vào lúc: <?= $today_record['gio_vao'] ?></div>
                                        <button type="submit" name="action" value="checkout" class="btn-attendance btn-checkout">
                                            <i class="fas fa-sign-out-alt fa-2x"></i>
                                            CHẤM CÔNG RA
                                        </button>
                                    </div>
                                <?php else: ?>
                                    <div style="text-align:center;">
                                        <div class="status-badge status-on" style="margin-bottom:10px;">Vào: <?= $today_record['gio_vao'] ?></div>
                                        <div class="status-badge status-off">Ra: <?= $today_record['gio_ra'] ?></div>
                                        <p style="margin-top:20px; color:var(--gray);">Hôm nay bạn đã hoàn thành chấm công!</p>
                                    </div>
                                <?php endif; ?>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <span class="card-title"><i class="fas fa-history"></i> Lịch sử tháng <?= $thang ?></span>
                    </div>
                    <div class="card-body" style="padding:0;">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Ngày</th>
                                    <th>Giờ vào</th>
                                    <th>Giờ ra</th>
                                    <th>Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($history)): ?>
                                    <tr><td colspan="4" style="text-align:center; padding:20px;">Chưa có dữ liệu</td></tr>
                                <?php else: foreach ($history as $row): ?>
                                    <tr>
                                        <td><?= formatDate($row['ngay']) ?></td>
                                        <td><?= $row['gio_vao'] ?: '—' ?></td>
                                        <td><?= $row['gio_ra'] ?: '—' ?></td>
                                        <td>
                                            <?php
                                            $cls = ($row['trang_thai'] == 'Đúng giờ') ? 'success' : 'warning';
                                            echo "<span class='badge badge-$cls'>{$row['trang_thai']}</span>";
                                            ?>
                                        </td>
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

<script>
    function updateClock() {
        const now = new Date();
        const h = String(now.getHours()).padStart(2, '0');
        const m = String(now.getMinutes()).padStart(2, '0');
        const s = String(now.getSeconds()).padStart(2, '0');
        document.getElementById('clock').textContent = `${h}:${m}:${s}`;
    }
    setInterval(updateClock, 1000);
    updateClock();
</script>
<script src="../../assets/js/main.js"></script>
</body>
</html>
