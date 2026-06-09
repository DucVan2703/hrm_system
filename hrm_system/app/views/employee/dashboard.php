<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard Nhân viên</title>
<link rel="stylesheet" href="<?= Helper::asset('css/style.css') ?>">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<div class="layout employee-page">
    <?php require __DIR__ . '/../layouts/sidebar.php'; ?>
    <div class="main-content">
        <?php require __DIR__ . '/../layouts/header.php'; ?>
        <div class="page-content">
            <div class="employee-hero">
                <div>
                    <span class="employee-kicker"><i class="fas fa-star"></i> Khu vực nhân viên</span>
                    <h1>Xin chào, <?= Helper::clean($_SESSION['ho_ten'] ?? 'Nhân viên') ?></h1>
                    <p>Chúc bạn một ngày làm việc hiệu quả. Các thông tin cá nhân và thao tác thường dùng được đặt ngay bên dưới để dễ theo dõi.</p>
                </div>
                <?php if (!empty($employee['hinh_anh'])): ?>
                    <img src="<?= BASE_URL . '/' . $employee['hinh_anh'] ?>" alt="Ảnh thẻ" class="employee-avatar-xl" style="object-fit:cover; border:3px solid #fff; box-shadow:var(--shadow-lg);">
                <?php else: ?>
                    <div class="employee-avatar-xl">
                        <?= mb_substr($_SESSION['ho_ten'] ?? 'N', 0, 1) ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="stats-grid employee-stats">
                <div class="stat-card primary">
                    <div class="stat-icon"><i class="fas fa-id-badge"></i></div>
                    <div class="stat-info">
                        <div class="stat-value"><?= Helper::clean($employee['ma_nv'] ?? '—') ?></div>
                        <div class="stat-label">Mã nhân viên</div>
                    </div>
                </div>
                <div class="stat-card info">
                    <div class="stat-icon"><i class="fas fa-building"></i></div>
                    <div class="stat-info">
                        <div class="stat-value employee-small-value"><?= Helper::clean($employee['ten_pb'] ?? 'Chưa cập nhật') ?></div>
                        <div class="stat-label">Phòng ban</div>
                    </div>
                </div>
                <div class="stat-card success">
                    <div class="stat-icon"><i class="fas fa-user-tie"></i></div>
                    <div class="stat-info">
                        <div class="stat-value employee-small-value"><?= Helper::clean($employee['ten_cv'] ?? 'Chưa cập nhật') ?></div>
                        <div class="stat-label">Chức vụ</div>
                    </div>
                </div>
            </div>

            <div class="employee-quick-grid">
                <a class="employee-action-card" href="<?= Helper::route('employee/profile') ?>">
                    <i class="fas fa-address-card"></i>
                    <div>
                        <h3>Hồ sơ của tôi</h3>
                        <p>Xem thông tin cá nhân, liên hệ và phòng ban.</p>
                    </div>
                </a>
                <a class="employee-action-card" href="<?= Helper::route('employee/leave') ?>">
                    <i class="fas fa-calendar-plus"></i>
                    <div>
                        <h3>Đơn nghỉ phép</h3>
                        <p>Gửi và theo dõi trạng thái đơn nghỉ phép.</p>
                    </div>
                </a>
                <a class="employee-action-card" href="<?= Helper::route('attendance/my') ?>">
                    <i class="fas fa-fingerprint"></i>
                    <div>
                        <h3>Chấm công cá nhân</h3>
                        <p>Kiểm tra dữ liệu chấm công của bản thân.</p>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>
<script src="<?= Helper::asset('js/main.js') ?>"></script>
</body>
</html>
