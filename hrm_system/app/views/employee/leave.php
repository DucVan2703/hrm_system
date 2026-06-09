<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Đơn nghỉ phép</title>
<link rel="stylesheet" href="<?= Helper::asset('css/style.css') ?>">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<div class="layout employee-page">
<?php require __DIR__.'/../layouts/sidebar.php'; ?>
<div class="main-content">
<?php require __DIR__.'/../layouts/header.php'; ?>
<div class="page-content">

<div class="page-header">
    <div>
        <h1>Đơn nghỉ phép</h1>
        <p>Gửi đơn nghỉ phép và theo dõi trạng thái xử lý</p>
    </div>
</div>

<?php 
$flash = isset($_SESSION['flash']) ? $_SESSION['flash'] : null;
if ($flash): 
    unset($_SESSION['flash']);
?>
<div class="alert alert-<?= $flash['type'] ?>" style="margin-bottom: 16px;">
  <i class="fas fa-<?= $flash['type'] === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
  <?= Helper::clean($flash['message']) ?>
</div>
<?php endif; ?>

<div class="grid-2 employee-leave-grid">
    <div class="card">
        <div class="card-header">
            <span class="card-title"><i class="fas fa-calendar-plus"></i> Tạo đơn mới</span>
        </div>
        <div class="card-body">
            <form method="POST" class="pretty-form">
                <div class="form-row col-2">
                    <div class="form-group">
                        <label class="form-label required">Từ ngày</label>
                        <input class="form-control" type="date" name="tu_ngay" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label required">Đến ngày</label>
                        <input class="form-control" type="date" name="den_ngay" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label required">Lý do nghỉ phép</label>
                    <textarea class="form-control" name="ly_do" rows="5" placeholder="Nhập lý do nghỉ phép..." required></textarea>
                </div>
                <button class="btn btn-primary" type="submit"><i class="fas fa-paper-plane"></i> Gửi đơn</button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <span class="card-title"><i class="fas fa-clock-rotate-left"></i> Lịch sử đơn</span>
            <span style="font-size:12px;color:var(--gray);">Tổng: <b><?= count($rows ?? []) ?></b></span>
        </div>
        <div class="card-body">
            <?php if (empty($rows)): ?>
                <div class="empty-state"><i class="fas fa-calendar-check"></i><h3>Chưa có đơn nghỉ phép</h3><p>Đơn đã gửi sẽ hiển thị tại đây.</p></div>
            <?php else: ?>
                <div class="leave-list">
                    <?php foreach($rows as $r): ?>
                        <div class="leave-item">
                            <div>
                                <b><?= Helper::formatDate($r['tu_ngay'] ?? null) ?> - <?= Helper::formatDate($r['den_ngay'] ?? null) ?></b>
                                <p><?= Helper::clean($r['ly_do'] ?? '') ?></p>
                            </div>
                            <?= Helper::badgeDonPhep($r['trang_thai'] ?? 'Chờ duyệt') ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

</div>
</div>
</div>
<script src="<?= Helper::asset('js/main.js') ?>"></script>
</body>
</html>
