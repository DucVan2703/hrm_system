<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Đăng Nhập - Đại Học Thành Đông</title>
<meta name="description" content="Hệ thống Quản lý Nhân sự và Tiền lương">
<link rel="stylesheet" href="<?= Helper::asset('css/style.css') ?>">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
.login-bg-text {
  color: rgba(255,255,255,.07); font-size: 120px; font-weight: 900;
  position: absolute; user-select: none; letter-spacing: -4px;
}
.login-features { list-style: none; }
.login-features li {
  display: flex; align-items: center; gap: 12px;
  color: rgba(255,255,255,.8); font-size: 15px; margin-bottom: 16px;
}
.login-features li i {
  width: 40px; height: 40px; border-radius: 10px;
  background: rgba(255,255,255,.1);
  display: flex; align-items: center; justify-content: center;
  color: var(--primary-light); flex-shrink: 0; font-size: 16px;
}
.login-left-content { max-width: 420px; position: relative; z-index: 1; }
.login-left-content h2 { font-size: 36px; font-weight: 800; color: #fff; margin-bottom: 12px; }
.login-left-content p { color: rgba(255,255,255,.6); font-size: 15px; margin-bottom: 36px; }

.show-pw-btn {
  position: absolute; right: 12px; top: 50%; transform: translateY(-50%);
  background: none; border: none; color: var(--gray); cursor: pointer; font-size: 14px;
}
.pw-wrapper { position: relative; }

@media (max-width: 768px) {
  .login-left { display: none; }
  .login-right { width: 100%; padding: 32px 24px; }
}
</style>
</head>
<body>
<div class="login-page">
  <!-- LEFT -->
  <div class="login-left">
    <div class="login-left-content">
      <h2>Quản lý Nhân sự<br>Chuyên nghiệp</h2>
      <p>Hệ thống quản lý nhân sự và tiền lương toàn diện, giúp doanh nghiệp vận hành hiệu quả hơn.</p>
      <ul class="login-features">
        <li><i><span class="fas fa-users"></span></i>Quản lý hồ sơ nhân viên toàn diện</li>
        <li><i><span class="fas fa-calculator"></span></i>Tính lương tự động theo ngày công</li>
        <li><i><span class="fas fa-file-contract"></span></i>Quản lý hợp đồng lao động</li>
        <li><i><span class="fas fa-chart-bar"></span></i>Báo cáo thống kê chi tiết</li>
      </ul>
    </div>
  </div>

  <!-- RIGHT -->
  <div class="login-right">
    <div class="login-logo">
      <img src="<?= Helper::asset('images/logo.png') ?>" alt="Logo" style="width: 80px; height: 80px; object-fit: contain; margin-bottom: 12px; background: #fff; padding: 4px; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
      <h1>Đại Học Thành Đông</h1>
      <p>Quản lý bảng lương Cán bộ giảng viên</p>
    </div>

    <?php if ($error): ?>
    <div class="alert alert-danger" style="width:100%;margin-bottom:16px;">
      <i class="fas fa-exclamation-circle"></i> <?= Helper::clean($error) ?>
    </div>
    <?php endif; ?>

    <form class="login-form" method="POST" action="">
      <div class="form-group">
        <label class="form-label required" for="username">Tên đăng nhập</label>
        <div style="position:relative;">
          <i class="fas fa-user" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--gray);"></i>
          <input type="text" id="username" name="username" class="form-control" style="padding-left:36px;" placeholder="Nhập tên đăng nhập" value="<?= Helper::clean($_POST['username'] ?? '') ?>" required autofocus>
        </div>
      </div>

      <div class="form-group">
        <label class="form-label required" for="password">Mật khẩu</label>
        <div class="pw-wrapper" style="position:relative;">
          <i class="fas fa-lock" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--gray);"></i>
          <input type="password" id="password" name="password" class="form-control" style="padding-left:36px;padding-right:40px;" placeholder="Nhập mật khẩu" required>
          <button type="button" class="show-pw-btn" onclick="togglePw()"><i class="fas fa-eye" id="eye-icon"></i></button>
        </div>
      </div>

      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
        <label style="display:flex;align-items:center;gap:6px;font-size:13px;cursor:pointer;">
          <input type="checkbox" name="remember"> Ghi nhớ đăng nhập
        </label>
        <a href="<?= Helper::route('forgot-password.php') ?>" style="font-size:13px;color:var(--primary);font-weight:500;">Quên mật khẩu?</a>
      </div>

      <button type="submit" class="btn btn-primary btn-lg" style="width:100%;justify-content:center;">
        <i class="fas fa-sign-in-alt"></i> Đăng nhập
      </button>
    </form>

    <div style="margin-top:24px;padding:14px;background:var(--bg);border-radius:8px;width:100%;">
      <p style="font-size:12px;color:var(--gray);font-weight:600;margin-bottom:6px;">Tài khoản demo:</p>
      <p style="font-size:12px;color:var(--gray);">👑 Admin: <b>admin</b> / <b>password</b></p>
      <p style="font-size:12px;color:var(--gray);">👤 Nhân viên: <b>nv_dung</b> / <b>password</b></p>
    </div>
  </div>
</div>

<script>
function togglePw() {
  const inp = document.getElementById('password');
  const icon = document.getElementById('eye-icon');
  if (inp.type === 'password') {
    inp.type = 'text';
    icon.className = 'fas fa-eye-slash';
  } else {
    inp.type = 'password';
    icon.className = 'fas fa-eye';
  }
}
</script>
</body>
</html>
