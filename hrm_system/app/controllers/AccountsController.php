<?php

require_once __DIR__ . '/../models/AdminCrudModel.php';

class AccountsController extends BaseController
{
    public function index()
    {
        Helper::requireOnlyAdmin();
        $model = new AdminCrudModel();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int)($_POST['id'] ?? 0);
            $type = $_POST['type'] ?? '';

            if ($type === 'delete') {
                if ($id === (int)($_SESSION['user_id'] ?? 0)) {
                    $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Không thể xóa tài khoản đang đăng nhập.'];
                } else {
                    $model->delete('tai_khoan', $id);
                    $_SESSION['flash'] = ['type' => 'success', 'message' => 'Đã xóa tài khoản thành công.'];
                }
                $this->redirect('accounts');
            }

            $username = Helper::sanitize($_POST['ten_dang_nhap'] ?? '');
            $password = $_POST['mat_khau'] ?? '';
            $role = Helper::sanitize($_POST['vai_tro'] ?? 'nhanvien');
            $status = (int)($_POST['trang_thai'] ?? 1);
            $employeeId = (int)($_POST['id_nhan_vien'] ?? 0) ?: null;
            $allowedRoles = ['admin', 'hr', 'ketoan', 'nhanvien'];

            if ($username === '') {
                $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Tên đăng nhập không được để trống.'];
                $this->redirect('accounts');
            }

            if (!in_array($role, $allowedRoles, true)) {
                $role = 'nhanvien';
            }

            if ($id === 0 && trim($password) === '') {
                $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Tài khoản mới bắt buộc phải nhập mật khẩu.'];
                $this->redirect('accounts');
            }

            $data = [
                'ten_dang_nhap' => $username,
                'id_nhan_vien' => $employeeId,
                'vai_tro' => $role,
                'trang_thai' => $status,
            ];

            if (trim($password) !== '') {
                $data['mat_khau'] = password_hash($password, PASSWORD_DEFAULT);
            }

            try {
                if ($id > 0) {
                    $model->update('tai_khoan', $data, $id);
                    $_SESSION['flash'] = ['type' => 'success', 'message' => 'Cập nhật tài khoản và phân quyền thành công.'];
                } else {
                    $model->insert('tai_khoan', $data);
                    $_SESSION['flash'] = ['type' => 'success', 'message' => 'Thêm tài khoản và phân quyền thành công.'];
                }
            } catch (PDOException $e) {
                $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Tên đăng nhập đã tồn tại hoặc dữ liệu chưa hợp lệ.'];
            }

            $this->redirect('accounts');
        }

        $employees = ['' => 'Không gắn nhân viên'];
        foreach ($model->employees() as $e) {
            $employees[$e['id']] = $e['ma_nv'] . ' - ' . $e['ho_ten'];
        }

        $this->view('admin/accounts', [
            'pageTitle' => 'Tài khoản & Phân quyền',
            'current_page' => 'accounts',
            'routeName' => 'accounts',
            'icon' => 'fa-user-shield',
            'tableTitle' => 'Danh sách tài khoản hệ thống',
            'rows' => $model->users(),
            'columns' => [
                'ten_dang_nhap' => 'Tên đăng nhập',
                'ho_ten' => 'Nhân viên',
                'vai_tro' => 'Vai trò',
                'trang_thai' => 'Trạng thái'
            ],
            'fields' => [
                ['name' => 'ten_dang_nhap', 'label' => 'Tên đăng nhập', 'required' => true],
                ['name' => 'mat_khau', 'label' => 'Mật khẩu mới', 'type' => 'password'],
                ['name' => 'id_nhan_vien', 'label' => 'Gắn với nhân viên', 'type' => 'select', 'options' => $employees],
                ['name' => 'vai_tro', 'label' => 'Vai trò', 'type' => 'select', 'options' => [
                    'admin' => 'Quản trị viên',
                    'hr' => 'Nhân sự',
                    'ketoan' => 'Kế toán',
                    'nhanvien' => 'Nhân viên'
                ]],
                ['name' => 'trang_thai', 'label' => 'Trạng thái', 'type' => 'select', 'options' => ['1' => 'Hoạt động', '0' => 'Tạm khóa']],
            ],
        ]);
    }
}
