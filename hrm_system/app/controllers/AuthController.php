<?php
class AuthController extends BaseController {
    public function login() {
        if (isset($_SESSION['user_id'])) {
            if (Helper::isAdmin()) {
                $this->redirect('dashboard');
            } else {
                $this->redirect('employee/dashboard');
            }
        }

        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = Helper::sanitize($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';

            if (empty($username) || empty($password)) {
                $error = 'Vui lòng nhập đầy đủ tên đăng nhập và mật khẩu!';
            } else {
                $userModel = new UserModel();
                $user = $userModel->findByUsername($username);

                if ($user && password_verify($password, $user['mat_khau'])) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['ten_dang_nhap'];
                    $_SESSION['vai_tro'] = $user['vai_tro'] ?? 'nhanvien';
                    $_SESSION['ho_ten'] = $user['ho_ten'] ?? 'Admin';
                    $_SESSION['id_nhan_vien'] = $user['id_nhan_vien'];

                    $userModel->updateLastLogin($user['id']);

                    if (Helper::isAdmin()) {
                        $this->redirect('dashboard');
                    } else {
                        $this->redirect('employee/dashboard');
                    }
                } else {
                    $error = 'Tên đăng nhập hoặc mật khẩu không đúng!';
                }
            }
        }

        $this->view('auth/login', ['error' => $error]);
    }

    public function logout() {
        session_destroy();
        $this->redirect('auth/login');
    }
}
