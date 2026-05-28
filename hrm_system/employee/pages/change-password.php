<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
requireLogin();
$pageTitle = 'Đổi mật khẩu';
$db = getDB();

$error = ''; $success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old = $_POST['old_password'] ?? '';
    $new1 = $_POST['new_password'] ?? '';
    $new2 = $_POST['confirm_password'] ?? '';
    
    $stmt = $db->prepare("SELECT mat_khau FROM tai_khoan WHERE id=?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    
    if (!password_verify($old, $user['mat_khau'])) { $error = 'Mật khẩu hiện tại không đúng!'; }
    elseif (strlen($new1) < 6) { $error = 'Mật khẩu mới phải có ít nhất 6 ký tự!'; }
    elseif ($new1 !== $new2) { $error = 'Xác nhận mật khẩu không khớp!'; }
    else {
        $db->prepare("UPDATE tai_khoan SET mat_khau=? WHERE id=?")->execute([password_hash($new1,PASSWORD_DEFAULT),$_SESSION['user_id']]);
        $success = 'Đổi mật khẩu thành công!';
    }
}

if (isAdmin()) { header('Location: '.BASE_URL.'/admin/index.php'); exit(); }
?>
<!DOCTYPE html>
<html lang="vi">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Đổi mật khẩu - <?= APP_NAME ?></title>
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
        <div><h1>Đổi mật khẩu</h1><p>Cập nhật mật khẩu tài khoản của bạn</p></div>
      </div>

      <div style="max-width:480px;">
        <?php if ($error): ?><div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?= clean($error) ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= clean($success) ?></div><?php endif; ?>

        <div class="card">
          <div class="card-header"><span class="card-title"><i class="fas fa-key"></i> Thay đổi mật khẩu</span></div>
          <div class="card-body">
            <form method="POST">
              <div class="form-group">
                <label class="form-label required">Mật khẩu hiện tại</label>
                <input type="password" name="old_password" class="form-control" required>
              </div>
              <div class="form-group">
                <label class="form-label required">Mật khẩu mới</label>
                <input type="password" name="new_password" class="form-control" required minlength="6">
                <p class="form-text">Tối thiểu 6 ký tự</p>
              </div>
              <div class="form-group">
                <label class="form-label required">Xác nhận mật khẩu mới</label>
                <input type="password" name="confirm_password" class="form-control" required>
              </div>
              <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;">
                <i class="fas fa-save"></i> Đổi mật khẩu
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<script src="../../assets/js/main.js"></script>
</body></html>
