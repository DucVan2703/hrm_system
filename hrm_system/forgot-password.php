<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

if (isset($_SESSION['user_id'])) {
    header('Location: index.php'); exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    if (empty($email)) {
        $error = 'Vui lòng nhập email!';
    } else {
        $db = getDB();
        $stmt = $db->prepare("SELECT tk.id, nv.email FROM tai_khoan tk JOIN nhan_vien nv ON tk.id_nhan_vien=nv.id WHERE nv.email=?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            setFlash('success', 'Hướng dẫn đặt lại mật khẩu đã được gửi đến email của bạn!');
        } else {
            setFlash('warning', 'Không tìm thấy tài khoản với email này!');
        }
        header('Location: login.php'); exit();
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Quên mật khẩu - <?= APP_NAME ?></title>
<link rel="stylesheet" href="assets/css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body style="display:flex;align-items:center;justify-content:center;min-height:100vh;background:linear-gradient(135deg,#0f172a,#1e293b);">
<div style="background:#fff;border-radius:16px;padding:40px;width:100%;max-width:420px;box-shadow:0 20px 60px rgba(0,0,0,.3);">
  <div style="text-align:center;margin-bottom:28px;">
    <div style="width:56px;height:56px;border-radius:14px;background:linear-gradient(135deg,#4f46e5,#06b6d4);display:flex;align-items:center;justify-content:center;margin:0 auto 12px;font-size:24px;color:#fff;">
      <i class="fas fa-key"></i>
    </div>
    <h1 style="font-size:20px;font-weight:800;">Quên mật khẩu</h1>
    <p style="color:var(--gray);font-size:13px;margin-top:4px;">Nhập email để nhận hướng dẫn đặt lại mật khẩu</p>
  </div>

  <?php if ($error): ?><div class="alert alert-danger"><?= clean($error) ?></div><?php endif; ?>

  <form method="POST">
    <div class="form-group">
      <label class="form-label required">Email</label>
      <div style="position:relative;">
        <i class="fas fa-envelope" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--gray);"></i>
        <input type="email" name="email" class="form-control" style="padding-left:36px;" placeholder="email@company.vn" required>
      </div>
    </div>
    <button type="submit" class="btn btn-primary btn-lg" style="width:100%;justify-content:center;margin-bottom:12px;">
      <i class="fas fa-paper-plane"></i> Gửi yêu cầu
    </button>
    <a href="login.php" class="btn btn-outline btn-lg" style="width:100%;justify-content:center;">
      <i class="fas fa-arrow-left"></i> Quay lại đăng nhập
    </a>
  </form>
</div>
</body></html>
