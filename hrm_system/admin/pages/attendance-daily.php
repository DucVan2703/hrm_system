<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
requireHR();

$pageTitle = 'Lịch sử chấm công chi tiết';
$db = getDB();

$ngay = $_GET['ngay'] ?? date('Y-m-d');
$id_pb = $_GET['id_phong_ban'] ?? '';

// Lấy danh sách phòng ban để lọc
$phong_bans = $db->query("SELECT id, ten_pb FROM phong_ban")->fetchAll();

// Lấy danh sách chấm công trong ngày
$sql = "SELECT nv.ma_nv, nv.ho_ten, pb.ten_pb, cc.gio_vao, cc.gio_ra, cc.trang_thai, cc.ghi_chu
        FROM nhan_vien nv
        LEFT JOIN phong_ban pb ON nv.id_phong_ban = pb.id
        LEFT JOIN cham_cong_chi_tiet cc ON nv.id = cc.id_nhan_vien AND cc.ngay = ?
        WHERE nv.trang_thai = 'Đang làm'";

$params = [$ngay];
if ($id_pb) {
    $sql .= " AND nv.id_phong_ban = ?";
    $params[] = $id_pb;
}
$sql .= " ORDER BY cc.gio_vao DESC, nv.ma_nv ASC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$list = $stmt->fetchAll();

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
</head>
<body>
<div class="layout">
    <?php include '../includes/sidebar.php'; ?>
    <div class="main-content">
        <?php include '../includes/header.php'; ?>
        <div class="page-content">
            <div class="page-header">
                <div>
                    <h1>Nhật ký chấm công hàng ngày</h1>
                    <p>Theo dõi thời gian ra vào chi tiết của nhân viên</p>
                </div>
            </div>

            <div class="card" style="margin-bottom: 20px;">
                <div class="card-body">
                    <form method="GET" style="display: flex; gap: 15px; align-items: flex-end;">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">Chọn ngày</label>
                            <input type="date" name="ngay" class="form-control" value="<?= $ngay ?>">
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">Phòng ban</label>
                            <select name="id_phong_ban" class="form-control">
                                <option value="">Tất cả phòng ban</option>
                                <?php foreach ($phong_bans as $pb): ?>
                                    <option value="<?= $pb['id'] ?>" <?= $id_pb == $pb['id'] ? 'selected' : '' ?>><?= clean($pb['ten_pb']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Lọc dữ liệu</button>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <span class="card-title"><i class="fas fa-list"></i> Danh sách chấm công ngày <?= formatDate($ngay) ?></span>
                </div>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Nhân viên</th>
                                <th>Phòng ban</th>
                                <th>Giờ vào</th>
                                <th>Giờ ra</th>
                                <th>Trạng thái</th>
                                <th>Ghi chú</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($list)): ?>
                                <tr><td colspan="6" style="text-align: center; padding: 20px;">Không có dữ liệu chấm công cho ngày này.</td></tr>
                            <?php else: foreach ($list as $row): ?>
                                <tr>
                                    <td>
                                        <strong><?= clean($row['ho_ten']) ?></strong><br>
                                        <small style="color: var(--gray);"><?= clean($row['ma_nv']) ?></small>
                                    </td>
                                    <td><?= clean($row['ten_pb'] ?? '—') ?></td>
                                    <td><?= $row['gio_vao'] ? "<strong>" . date('H:i', strtotime($row['gio_vao'])) . "</strong>" : '<span style="color:var(--gray);">Chưa vào</span>' ?></td>
                                    <td><?= $row['gio_ra'] ? "<strong>" . date('H:i', strtotime($row['gio_ra'])) . "</strong>" : '<span style="color:var(--gray);">Chưa ra</span>' ?></td>
                                    <td>
                                        <?php if ($row['gio_vao']): ?>
                                            <span class="badge badge-<?= ($row['trang_thai'] == 'Đúng giờ' ? 'success' : 'warning') ?>">
                                                <?= $row['trang_thai'] ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="badge badge-danger">Vắng mặt</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= clean($row['ghi_chu'] ?? '') ?></td>
                                </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="../../assets/js/main.js"></script>
</body>
</html>
