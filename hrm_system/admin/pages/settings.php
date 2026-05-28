<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
requireOnlyAdmin();

$pageTitle = 'Cài đặt hệ thống';
$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $configs = $_POST['config'] ?? [];
    $stmt = $db->prepare("UPDATE cau_hinh SET gia_tri = ? WHERE ma_cau_hinh = ?");
    foreach ($configs as $key => $value) {
        $stmt->execute([$value, $key]);
    }
    setFlash('success', 'Cập nhật cấu hình thành công!');
    header('Location: settings.php');
    exit();
}

$stmt = $db->query("SELECT * FROM cau_hinh ORDER BY id");
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
                    <h1>Cài đặt hệ thống</h1>
                    <p>Quản lý các tham số và thông tin chung của hệ thống</p>
                </div>
            </div>

            <?php if ($flash): ?>
                <div class="alert alert-<?= $flash['type'] ?>">
                    <i class="fas fa-info-circle"></i> <?= clean($flash['message']) ?>
                </div>
            <?php endif; ?>

            <div style="max-width: 800px;">
                <form method="POST">
                    <div class="card">
                        <div class="card-header">
                            <span class="card-title"><i class="fas fa-cog"></i> Cấu hình tham số</span>
                        </div>
                        <div class="card-body">
                            <?php foreach ($list as $config): ?>
                                <div class="form-group">
                                    <label class="form-label"><?= clean($config['mo_ta']) ?></label>
                                    <input type="text" name="config[<?= $config['ma_cau_hinh'] ?>]" 
                                           class="form-control" value="<?= clean($config['gia_tri']) ?>">
                                    <small style="color: var(--gray); font-size: 11px;">Mã: <?= $config['ma_cau_hinh'] ?></small>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="card-footer" style="padding: 16px 20px; border-top: 1px solid var(--border); background: var(--bg);">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Lưu thay đổi
                            </button>
                        </div>
                    </div>
                </form>

                <div class="card" style="margin-top: 20px;">
                    <div class="card-header">
                        <span class="card-title"><i class="fas fa-info-circle"></i> Thông tin hệ thống</span>
                    </div>
                    <div class="card-body">
                        <table class="table">
                            <tr>
                                <td style="width: 200px;">Phiên bản PHP</td>
                                <td><?= PHP_VERSION ?></td>
                            </tr>
                            <tr>
                                <td>Server Software</td>
                                <td><?= $_SERVER['SERVER_SOFTWARE'] ?></td>
                            </tr>
                            <tr>
                                <td>Database Driver</td>
                                <td>PDO (MySQL)</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="../../assets/js/main.js"></script>
</body>
</html>
