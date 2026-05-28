<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
requireHR();

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) { header('Location: employees.php'); exit(); }

$db = getDB();

// Lấy thông tin chi tiết nhân viên
$stmt = $db->prepare("
    SELECT nv.*, pb.ten_pb, cv.ten_cv 
    FROM nhan_vien nv 
    LEFT JOIN phong_ban pb ON nv.id_phong_ban = pb.id 
    LEFT JOIN chuc_vu cv ON nv.id_chuc_vu = cv.id 
    WHERE nv.id = ?
");
$stmt->execute([$id]);
$nv = $stmt->fetch();

if (!$nv) { header('Location: employees.php'); exit(); }

$pageTitle = 'Hồ sơ: ' . $nv['ho_ten'];

// Lấy danh sách hợp đồng
$stmt = $db->prepare("SELECT * FROM hop_dong WHERE id_nhan_vien = ? ORDER BY ngay_bat_dau DESC");
$stmt->execute([$id]);
$contracts = $stmt->fetchAll();

// Lấy lịch sử lương gần đây
$stmt = $db->prepare("SELECT * FROM bang_luong WHERE id_nhan_vien = ? ORDER BY nam DESC, thang DESC LIMIT 6");
$stmt->execute([$id]);
$salaries = $stmt->fetchAll();

// Lấy thống kê chấm công tháng này
$thang = date('n'); $nam = date('Y');
$stmt = $db->prepare("SELECT * FROM cham_cong WHERE id_nhan_vien = ? AND thang = ? AND nam = ?");
$stmt->execute([$id, $thang, $nam]);
$attendance = $stmt->fetch();
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
        .profile-container { display: grid; grid-template-columns: 320px 1fr; gap: 24px; }
        .profile-card { position: sticky; top: 24px; }
        .profile-header { text-align: center; padding: 30px 20px; }
        .profile-avatar { width: 120px; height: 120px; border-radius: 50%; background: var(--primary-light); color: var(--primary); font-size: 48px; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px; border: 4px solid #fff; box-shadow: var(--shadow); }
        .profile-name { font-size: 20px; font-weight: 700; color: var(--dark); margin-bottom: 5px; }
        .profile-role { color: var(--gray); font-size: 14px; margin-bottom: 15px; }
        
        .info-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; }
        .info-item { margin-bottom: 15px; }
        .info-label { font-size: 12px; color: var(--gray); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 5px; display: block; }
        .info-value { font-size: 15px; color: var(--dark); font-weight: 500; }
        
        .tab-content { padding-top: 20px; }
        .signature-box { border: 2px dashed var(--border); border-radius: 12px; padding: 20px; text-align: center; background: var(--bg); max-width: 300px; }
        .signature-img { max-width: 100%; max-height: 120px; mix-blend-mode: multiply; }
        
        @media (max-width: 1024px) {
            .profile-container { grid-template-columns: 1fr; }
            .profile-card { position: static; }
        }
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
                    <a href="employees.php" style="color: var(--gray); font-size: 14px; text-decoration: none; display: flex; align-items: center; gap: 5px; margin-bottom: 8px;">
                        <i class="fas fa-arrow-left"></i> Quay lại danh sách
                    </a>
                    <h1>Chi tiết hồ sơ nhân viên</h1>
                </div>
                <div class="page-actions">
                    <button class="btn btn-outline" onclick="window.print()"><i class="fas fa-print"></i> In hồ sơ</button>
                    <a href="employees.php?action=edit&id=<?= $id ?>" class="btn btn-primary"><i class="fas fa-edit"></i> Chỉnh sửa</a>
                </div>
            </div>

            <div class="profile-container">
                <!-- Cột trái: Card tổng quan -->
                <div class="card profile-card">
                    <div class="profile-header">
                        <div class="profile-avatar">
                            <?php if ($nv['hinh_anh']): ?>
                                <img src="../../<?= $nv['hinh_anh'] ?>" alt="Avatar" style="width:100%; height:100%; border-radius:50%; object-fit:cover;">
                            <?php else: ?>
                                <?= mb_substr($nv['ho_ten'], 0, 1) ?>
                            <?php endif; ?>
                        </div>
                        <h2 class="profile-name"><?= clean($nv['ho_ten']) ?></h2>
                        <div class="profile-role"><?= clean($nv['ten_cv'] ?? 'Chưa cập nhật chức vụ') ?></div>
                        <?= badgeTrangThaiNV($nv['trang_thai']) ?>
                    </div>
                    <div class="card-body" style="border-top: 1px solid var(--border);">
                        <div class="info-item">
                            <span class="info-label">Mã nhân viên</span>
                            <span class="info-value">#<?= $nv['ma_nv'] ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Phòng ban</span>
                            <span class="info-value"><?= clean($nv['ten_pb'] ?? '—') ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Ngày vào làm</span>
                            <span class="info-value"><?= formatDate($nv['ngay_vao_lam']) ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Thâm niên</span>
                            <span class="info-value">
                                <?php 
                                    $start = new DateTime($nv['ngay_vao_lam']);
                                    $now = new DateTime();
                                    $diff = $start->diff($now);
                                    echo ($diff->y > 0 ? $diff->y . ' năm ' : '') . $diff->m . ' tháng';
                                ?>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Cột phải: Chi tiết -->
                <div class="card">
                    <div class="card-body">
                        <div class="tabs">
                            <div class="tab-item active" data-tab="info">Thông tin cơ bản</div>
                            <div class="tab-item" data-tab="contracts">Hợp đồng</div>
                            <div class="tab-item" data-tab="salary">Lịch sử lương</div>
                        </div>

                        <div class="tab-content">
                            <!-- Tab: Thông tin cơ bản -->
                            <div class="tab-pane active" id="info">
                                <h3 style="margin-bottom: 20px; font-size: 18px; color: var(--primary);">Thông tin cá nhân</h3>
                                <div class="info-grid">
                                    <div class="info-item">
                                        <span class="info-label">Họ và tên</span>
                                        <span class="info-value"><?= clean($nv['ho_ten']) ?></span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Ngày sinh</span>
                                        <span class="info-value"><?= formatDate($nv['ngay_sinh']) ?></span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Giới tính</span>
                                        <span class="info-value"><?= $nv['gioi_tinh'] ?></span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Số CCCD</span>
                                        <span class="info-value"><?= clean($nv['cccd'] ?? '—') ?></span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Số điện thoại</span>
                                        <span class="info-value"><?= clean($nv['so_dien_thoai'] ?? '—') ?></span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Email cá nhân</span>
                                        <span class="info-value"><?= clean($nv['email'] ?? '—') ?></span>
                                    </div>
                                    <div class="info-item" style="grid-column: span 2;">
                                        <span class="info-label">Địa chỉ liên hệ</span>
                                        <span class="info-value"><?= clean($nv['dia_chi'] ?? '—') ?></span>
                                    </div>
                                </div>

                                <h3 style="margin: 30px 0 20px; font-size: 18px; color: var(--primary);">Chữ ký điện tử</h3>
                                <div class="signature-box">
                                    <?php if ($nv['chu_ky']): ?>
                                        <img src="../../<?= $nv['chu_ky'] ?>" alt="Chữ ký" class="signature-img">
                                        <p style="font-size: 11px; color: var(--gray); margin-top: 10px;">Chữ ký đã được xác thực</p>
                                    <?php else: ?>
                                        <div style="color: var(--gray); padding: 20px;">
                                            <i class="fas fa-signature" style="font-size: 32px; opacity: 0.3; margin-bottom: 10px;"></i>
                                            <p>Nhân viên chưa cập nhật chữ ký</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Tab: Hợp đồng -->
                            <div class="tab-pane" id="contracts">
                                <h3 style="margin-bottom: 20px; font-size: 18px; color: var(--primary);">Lịch sử hợp đồng lao động</h3>
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Mã HĐ</th>
                                            <th>Loại hợp đồng</th>
                                            <th>Ngày bắt đầu</th>
                                            <th>Ngày kết thúc</th>
                                            <th>Lương HĐ</th>
                                            <th>Trạng thái</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($contracts)): ?>
                                            <tr><td colspan="6" style="text-align:center; padding: 40px; color: var(--gray);">Chưa có dữ liệu hợp đồng</td></tr>
                                        <?php else: foreach ($contracts as $c): ?>
                                            <tr>
                                                <td><b><?= $c['ma_hd'] ?></b></td>
                                                <td><?= $c['loai_hop_dong'] ?></td>
                                                <td><?= formatDate($c['ngay_bat_dau']) ?></td>
                                                <td><?= $c['ngay_ket_thuc'] ? formatDate($c['ngay_ket_thuc']) : 'Không thời hạn' ?></td>
                                                <td><?= formatMoney($c['luong_hop_dong']) ?></td>
                                                <td><?= badgeTrangThaiHD($c['trang_thai']) ?></td>
                                            </tr>
                                        <?php endforeach; endif; ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Tab: Lịch sử lương -->
                            <div class="tab-pane" id="salary">
                                <h3 style="margin-bottom: 20px; font-size: 18px; color: var(--primary);">Lịch sử thu nhập (6 tháng gần nhất)</h3>
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Tháng/Năm</th>
                                            <th>Ngày công</th>
                                            <th>Tổng thu nhập</th>
                                            <th>Khấu trừ</th>
                                            <th>Thực lĩnh</th>
                                            <th>Trạng thái</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($salaries)): ?>
                                            <tr><td colspan="6" style="text-align:center; padding: 40px; color: var(--gray);">Chưa có dữ liệu lương</td></tr>
                                        <?php else: foreach ($salaries as $s): ?>
                                            <tr>
                                                <td><b>Tháng <?= $s['thang'] ?>/<?= $s['nam'] ?></b></td>
                                                <td><?= $s['so_ngay_lam'] ?>/<?= $s['so_ngay_chuan'] ?></td>
                                                <td><?= formatMoney($s['tong_thu_nhap']) ?></td>
                                                <td><span style="color: var(--danger);">-<?= formatMoney($s['tong_khau_tru']) ?></span></td>
                                                <td><b style="color: var(--success);"><?= formatMoney($s['thuc_linh']) ?></b></td>
                                                <td><?= badgeTrangThaiLuong($s['trang_thai']) ?></td>
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
    </div>
</div>

<script src="../../assets/js/main.js"></script>
</body>
</html>
