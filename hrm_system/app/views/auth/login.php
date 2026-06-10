<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Đăng Nhập - Đại Học Thành Đông</title>
<meta name="description" content="Hệ thống Quản lý Nhân sự và Tiền lương - Đại Học Thành Đông">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;600;700;800;900&display=swap');

:root {
  --primary: #4f46e5;
  --primary-hover: #4338ca;
  --secondary: #06b6d4;
  --dark: #0f172a;
  --gray: #6b7280;
  --gray-light: #e5e7eb;
  --white: #ffffff;
  --font-primary: 'Inter', sans-serif;
  --font-heading: 'Outfit', sans-serif;
}

* {
  box-sizing: border-box;
  margin: 0;
  padding: 0;
}

body {
  font-family: var(--font-primary);
  background: #090a0f;
  color: #f3f4f6;
  min-height: 100vh;
  overflow: hidden;
}

.login-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 24px;
  position: relative;
  background: radial-gradient(circle at 50% 50%, rgb(17, 24, 39) 0%, rgb(9, 10, 15) 100%);
}

/* Glowing background ambient lights */
.login-page::before {
  content: '';
  position: absolute;
  width: 600px;
  height: 600px;
  background: radial-gradient(circle, rgba(79, 70, 229, 0.18) 0%, rgba(79, 70, 229, 0) 70%);
  top: 50%;
  left: 50%;
  transform: translate(-90%, -90%);
  pointer-events: none;
  z-index: 0;
}

.login-page::after {
  content: '';
  position: absolute;
  width: 600px;
  height: 600px;
  background: radial-gradient(circle, rgba(6, 182, 212, 0.12) 0%, rgba(6, 182, 212, 0) 70%);
  top: 50%;
  left: 50%;
  transform: translate(-10%, -10%);
  pointer-events: none;
  z-index: 0;
}

/* CENTERED LOGIN CARD */
.login-card {
  width: 100%;
  max-width: 440px;
  background: rgba(15, 23, 42, 0.55);
  backdrop-filter: blur(28px);
  -webkit-backdrop-filter: blur(28px);
  border: 1px solid rgba(255, 255, 255, 0.06);
  border-radius: 24px;
  padding: 48px 40px;
  position: relative;
  z-index: 1;
  box-shadow: 0 20px 50px rgba(0, 0, 0, 0.4);
}

.login-logo {
  text-align: center;
  margin-bottom: 36px;
}

.login-logo img {
  width: 96px;
  height: 96px;
  object-fit: contain;
  margin-bottom: 18px;
  background: var(--white);
  padding: 8px;
  border-radius: 24px;
  box-shadow: 0 10px 30px -5px rgba(79, 70, 229, 0.3);
  transition: transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.login-logo img:hover {
  transform: scale(1.08) rotate(3deg);
}

.login-logo h1 {
  font-family: var(--font-heading);
  font-size: 26px;
  font-weight: 800;
  color: var(--white);
  letter-spacing: -0.5px;
  margin-bottom: 6px;
}

.login-logo p {
  font-size: 13px;
  color: #9ca3af;
  font-weight: 500;
  letter-spacing: 0.2px;
}

.login-form {
  width: 100%;
}

.form-group {
  margin-bottom: 22px;
}

.form-label {
  display: block;
  font-size: 13px;
  font-weight: 600;
  color: #d1d5db;
  margin-bottom: 8px;
}

.form-label.required::after {
  content: ' *';
  color: #ef4444;
}

.form-control {
  width: 100%;
  background: rgba(255, 255, 255, 0.03);
  border: 1px solid rgba(255, 255, 255, 0.08);
  color: var(--white);
  border-radius: 14px;
  padding: 13px 16px 13px 44px;
  font-size: 14px;
  font-weight: 500;
  transition: all 0.3s ease;
}

.form-control:focus {
  background: rgba(255, 255, 255, 0.06);
  border-color: #818cf8;
  box-shadow: 0 0 0 4px rgba(129, 140, 248, 0.15);
  outline: none;
}

.form-control::placeholder {
  color: #4b5563;
}

.pw-wrapper {
  position: relative;
}

.show-pw-btn {
  position: absolute;
  right: 14px;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  color: #9ca3af;
  cursor: pointer;
  font-size: 15px;
  transition: color 0.2s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 6px;
}

.show-pw-btn:hover {
  color: var(--white);
}

.login-options {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 26px;
  font-size: 13px;
}

.remember-label {
  display: flex;
  align-items: center;
  gap: 8px;
  color: #9ca3af;
  cursor: pointer;
  user-select: none;
  font-weight: 500;
}

.remember-checkbox {
  accent-color: #6366f1;
  width: 16px;
  height: 16px;
  cursor: pointer;
  border-radius: 4px;
}

.forgot-link {
  color: #818cf8;
  font-weight: 600;
  text-decoration: none;
  transition: color 0.2s ease;
}

.forgot-link:hover {
  color: #06b6d4;
  text-decoration: underline;
}

.btn-primary {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  width: 100%;
  background: linear-gradient(135deg, #4f46e5 0%, #06b6d4 100%);
  color: var(--white);
  border: none;
  border-radius: 14px;
  padding: 14px 24px;
  font-size: 15px;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  box-shadow: 0 8px 24px -8px rgba(79, 70, 229, 0.6);
}

.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 12px 28px -6px rgba(79, 70, 229, 0.7);
  background: linear-gradient(135deg, #4338ca 0%, #0891b2 100%);
}

.btn-primary:active {
  transform: translateY(0);
}

.alert-danger {
  width: 100%;
  background: rgba(239, 68, 68, 0.1);
  border: 1px solid rgba(239, 68, 68, 0.2);
  color: #fca5a5;
  padding: 12px 16px;
  border-radius: 12px;
  font-size: 13px;
  font-weight: 500;
  margin-bottom: 20px;
  display: flex;
  align-items: center;
  gap: 10px;
  box-shadow: 0 4px 12px rgba(239, 68, 68, 0.05);
}

@media (max-width: 480px) {
  .login-card {
    padding: 32px 20px;
    box-shadow: none;
    background: transparent;
    border: none;
  }
  .login-page::before,
  .login-page::after {
    width: 300px;
    height: 300px;
  }
}
</style>
</head>
<body>
<div class="login-page">
  <!-- CENTERED LOGIN CARD -->
  <div class="login-card">
    <div class="login-logo">
      <img src="<?= Helper::asset('images/logo.png') ?>" alt="Logo Đại Học Thành Đông">
      <h1>Đại Học Thành Đông</h1>
      <p>Hệ thống thông tin Nhân sự & Tiền lương</p>
    </div>

    <?php if ($error): ?>
    <div class="alert alert-danger">
      <i class="fas fa-exclamation-circle"></i> <?= Helper::clean($error) ?>
    </div>
    <?php endif; ?>

    <form class="login-form" method="POST" action="">
      <div class="form-group">
        <label class="form-label required" for="username">Tên đăng nhập</label>
        <div style="position:relative;">
          <i class="fas fa-user" style="position:absolute;left:14px;top:50%;transform:translateY(-50%);color:var(--gray);font-size:14px;"></i>
          <input type="text" id="username" name="username" class="form-control" placeholder="Tên đăng nhập" value="<?= Helper::clean($_POST['username'] ?? '') ?>" required autofocus>
        </div>
      </div>

      <div class="form-group">
        <label class="form-label required" for="password">Mật khẩu</label>
        <div class="pw-wrapper">
          <i class="fas fa-lock" style="position:absolute;left:14px;top:50%;transform:translateY(-50%);color:var(--gray);font-size:14px;"></i>
          <input type="password" id="password" name="password" class="form-control" placeholder="Mật khẩu" required>
          <button type="button" class="show-pw-btn" onclick="togglePw()"><i class="fas fa-eye" id="eye-icon"></i></button>
        </div>
      </div>

      <div class="login-options">
        <label class="remember-label">
          <input type="checkbox" name="remember" class="remember-checkbox"> Ghi nhớ đăng nhập
        </label>
        <a href="<?= Helper::route('forgot-password.php') ?>" class="forgot-link">Quên mật khẩu?</a>
      </div>

      <button type="submit" class="btn btn-primary">
        <i class="fas fa-sign-in-alt"></i> Đăng nhập hệ thống
      </button>
    </form>
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
