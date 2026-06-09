<?php

class EmployeeController extends BaseController
{
    public function index()
    {
        Helper::requireHR();
        $model = new EmployeeModel();

        // Xử lý POST (Thêm / Sửa / Xóa)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $type = $_POST['type'] ?? '';

            if ($type === 'save') {
                $id = (int)($_POST['id'] ?? 0);

                $data = [
                    'ho_ten' => Helper::sanitize($_POST['ho_ten'] ?? ''),
                    'ngay_sinh' => $_POST['ngay_sinh'] ?? null,
                    'gioi_tinh' => $_POST['gioi_tinh'] ?? 'Nam',
                    'cccd' => Helper::sanitize($_POST['cccd'] ?? ''),
                    'so_dien_thoai' => Helper::sanitize($_POST['so_dien_thoai'] ?? ''),
                    'email' => Helper::sanitize($_POST['email'] ?? ''),
                    'dia_chi' => Helper::sanitize($_POST['dia_chi'] ?? ''),
                    'id_phong_ban' => (int)($_POST['id_phong_ban'] ?? 0) ?: null,
                    'id_chuc_vu' => (int)($_POST['id_chuc_vu'] ?? 0) ?: null,
                    'luong_co_ban' => (float)str_replace(',', '', $_POST['luong_co_ban'] ?? 0),
                    'ngay_vao_lam' => $_POST['ngay_vao_lam'] ?? null,
                    'trang_thai' => $_POST['trang_thai'] ?? 'Đang làm',
                ];

                if (empty($data['ho_ten'])) {
                    $_SESSION['flash'] = [
                        'type' => 'danger',
                        'message' => 'Họ tên không được để trống!'
                    ];
                } else {

                    // Upload hình ảnh (ảnh thẻ)
                    if (
                        isset($_FILES['hinh_anh']) &&
                        $_FILES['hinh_anh']['error'] === UPLOAD_ERR_OK
                    ) {
                        $ext = pathinfo($_FILES['hinh_anh']['name'], PATHINFO_EXTENSION);
                        $filename = 'emp_' . ($id > 0 ? $id : 'new') . '_' . time() . '.' . $ext;
                        
                        $dir = __DIR__ . '/../../public/assets/uploads/employees/';
                        if (!is_dir($dir)) {
                            @mkdir($dir, 0777, true);
                        }
                        if (is_dir($dir) && is_writable($dir)) {
                            $target = $dir . $filename;
                            $db_path = 'public/assets/uploads/employees/' . $filename;
                        } else {
                            $target = __DIR__ . '/../../public/assets/uploads/' . $filename;
                            $db_path = 'public/assets/uploads/' . $filename;
                        }

                        if (move_uploaded_file($_FILES['hinh_anh']['tmp_name'], $target)) {
                            $data['hinh_anh'] = $db_path;
                        }
                    }

                    // Upload chữ ký
                    if (
                        isset($_FILES['chu_ky']) &&
                        $_FILES['chu_ky']['error'] === UPLOAD_ERR_OK
                    ) {
                        $ext = pathinfo($_FILES['chu_ky']['name'], PATHINFO_EXTENSION);
                        $filename = 'sig_' . ($id > 0 ? $id : 'new') . '_' . time() . '.' . $ext;

                        $dir = __DIR__ . '/../../public/assets/uploads/signatures/';
                        if (!is_dir($dir)) {
                            @mkdir($dir, 0777, true);
                        }
                        if (is_dir($dir) && is_writable($dir)) {
                            $target = $dir . $filename;
                            $db_path = 'public/assets/uploads/signatures/' . $filename;
                        } else {
                            $target = __DIR__ . '/../../public/assets/uploads/' . $filename;
                            $db_path = 'public/assets/uploads/' . $filename;
                        }

                        if (move_uploaded_file($_FILES['chu_ky']['tmp_name'], $target)) {
                            $data['chu_ky'] = $db_path;
                        }
                    }

                    $model->save($data, $id);

                    $_SESSION['flash'] = [
                        'type' => 'success',
                        'message' => ($id > 0 ? 'Cập nhật' : 'Thêm')
                            . ' nhân viên thành công!'
                    ];
                }

                $this->redirect('employee');
            }

            if ($type === 'delete') {
                $id = (int)($_POST['id'] ?? 0);

                $model->delete($id);

                $_SESSION['flash'] = [
                    'type' => 'success',
                    'message' => 'Đã xóa nhân viên thành công!'
                ];

                $this->redirect('employee');
            }
        }

        // Bộ lọc
        $search = Helper::sanitize($_GET['search'] ?? '');
        $pb_filter = (int)($_GET['pb'] ?? 0);
        $tt_filter = Helper::sanitize($_GET['tt'] ?? '');
        $page_num = max(1, (int)($_GET['page'] ?? 1));
        $per_page = 10;

        $total = $model->getTotal($search, $pb_filter, $tt_filter);
        $total_pages = ceil($total / $per_page);

        $page_num = max(
            1,
            min($page_num, max(1, $total_pages))
        );

        $offset = ($page_num - 1) * $per_page;

        $list = $model->getList(
            $search,
            $pb_filter,
            $tt_filter,
            $offset,
            $per_page
        );

        $phong_bans = $model->getPhongBans();
        $chuc_vus = $model->getChucVus();

        $this->view('employee/list', [
            'pageTitle' => 'Quản lý nhân viên',
            'list' => $list,
            'phong_bans' => $phong_bans,
            'chuc_vus' => $chuc_vus,
            'search' => $search,
            'pb_filter' => $pb_filter,
            'tt_filter' => $tt_filter,
            'total' => $total,
            'page_num' => $page_num,
            'total_pages' => $total_pages,
            'offset' => $offset
        ]);
    }

    public function detail($id = 0)
    {
        Helper::requireHR();
        $model = new EmployeeModel();

        $employee = $model->getById($id);

        if (!$employee) {
            die('Không tìm thấy nhân viên');
        }

        $this->view('employee/detail', [
            'pageTitle' => 'Chi tiết nhân viên',
            'employee' => $employee
        ]);
    }
    public function dashboard()
    {
        Helper::requireLogin();

        $employee = null;
        if (!empty($_SESSION['id_nhan_vien'])) {
            $db = Database::getInstance();
            $stmt = $db->prepare("
                SELECT nv.*, pb.ten_pb, cv.ten_cv
                FROM nhan_vien nv
                LEFT JOIN phong_ban pb ON nv.id_phong_ban = pb.id
                LEFT JOIN chuc_vu cv ON nv.id_chuc_vu = cv.id
                WHERE nv.id = ?
            ");
            $stmt->execute([$_SESSION['id_nhan_vien']]);
            $employee = $stmt->fetch();
        }

        $this->view('employee/dashboard', [
            'pageTitle' => 'Dashboard Nhân viên',
            'current_page' => 'employee-dashboard',
            'employee' => $employee
        ]);
    }
    public function profile()
    {
        Helper::requireLogin();
        $id = (int)($_SESSION['id_nhan_vien'] ?? 0);
        $model = new EmployeeModel();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($id <= 0) {
                $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Tài khoản chưa được liên kết với nhân viên nào.'];
                $this->redirect('employee/profile');
            }

            $data = [
                'so_dien_thoai' => Helper::sanitize($_POST['so_dien_thoai'] ?? ''),
                'email' => Helper::sanitize($_POST['email'] ?? ''),
                'dia_chi' => Helper::sanitize($_POST['dia_chi'] ?? ''),
            ];

            // Upload hình ảnh (ảnh thẻ)
            if (
                isset($_FILES['hinh_anh']) &&
                $_FILES['hinh_anh']['error'] === UPLOAD_ERR_OK
            ) {
                $ext = pathinfo($_FILES['hinh_anh']['name'], PATHINFO_EXTENSION);
                $filename = 'emp_' . $id . '_' . time() . '.' . $ext;

                $dir = __DIR__ . '/../../public/assets/uploads/employees/';
                if (!is_dir($dir)) {
                    @mkdir($dir, 0777, true);
                }
                if (is_dir($dir) && is_writable($dir)) {
                    $target = $dir . $filename;
                    $db_path = 'public/assets/uploads/employees/' . $filename;
                } else {
                    $target = __DIR__ . '/../../public/assets/uploads/' . $filename;
                    $db_path = 'public/assets/uploads/' . $filename;
                }

                if (move_uploaded_file($_FILES['hinh_anh']['tmp_name'], $target)) {
                    $data['hinh_anh'] = $db_path;
                }
            }

            // Upload chữ ký
            if (
                isset($_FILES['chu_ky']) &&
                $_FILES['chu_ky']['error'] === UPLOAD_ERR_OK
            ) {
                $ext = pathinfo($_FILES['chu_ky']['name'], PATHINFO_EXTENSION);
                $filename = 'sig_' . $id . '_' . time() . '.' . $ext;

                $dir = __DIR__ . '/../../public/assets/uploads/signatures/';
                if (!is_dir($dir)) {
                    @mkdir($dir, 0777, true);
                }
                if (is_dir($dir) && is_writable($dir)) {
                    $target = $dir . $filename;
                    $db_path = 'public/assets/uploads/signatures/' . $filename;
                } else {
                    $target = __DIR__ . '/../../public/assets/uploads/' . $filename;
                    $db_path = 'public/assets/uploads/' . $filename;
                }

                if (move_uploaded_file($_FILES['chu_ky']['tmp_name'], $target)) {
                    $data['chu_ky'] = $db_path;
                }
            }

            $model->save($data, $id);

            $_SESSION['flash'] = [
                'type' => 'success',
                'message' => 'Cập nhật thông tin hồ sơ cá nhân thành công!'
            ];
            $this->redirect('employee/profile');
        }

        $db = Database::getInstance();
        $stmt = $db->prepare("
            SELECT nv.*, pb.ten_pb, cv.ten_cv
            FROM nhan_vien nv
            LEFT JOIN phong_ban pb ON nv.id_phong_ban = pb.id
            LEFT JOIN chuc_vu cv ON nv.id_chuc_vu = cv.id
            WHERE nv.id = ?
        ");
        $stmt->execute([$id]);
        $employee = $stmt->fetch();

        $this->view('employee/profile', [
            'pageTitle' => 'Hồ sơ của tôi',
            'current_page' => 'my-profile',
            'employee' => $employee
        ]);
    }
    public function leave()
    {
        Helper::requireLogin();
        $id = $_SESSION['id_nhan_vien'] ?? 0;

        $db = Database::getInstance();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($id <= 0) {
                $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Tài khoản chưa được liên kết với nhân viên nào.'];
                $this->redirect('employee/leave');
            }

            $tu_ngay = $_POST['tu_ngay'] ?? '';
            $den_ngay = $_POST['den_ngay'] ?? '';
            $ly_do = Helper::sanitize($_POST['ly_do'] ?? '');

            $so_ngay = 1;
            if (!empty($tu_ngay) && !empty($den_ngay)) {
                $t1 = strtotime($tu_ngay);
                $t2 = strtotime($den_ngay);
                if ($t2 >= $t1) {
                    $so_ngay = round(($t2 - $t1) / (60 * 60 * 24)) + 1;
                }
            }

            $stmt = $db->prepare("
                INSERT INTO don_nghi_phep
                (
                    id_nhan_vien,
                    ngay_bat_dau,
                    ngay_ket_thuc,
                    so_ngay,
                    ly_do,
                    loai_phep,
                    trang_thai
                )
                VALUES
                (?, ?, ?, ?, ?, 'Phép năm', 'Chờ duyệt')
            ");

            $stmt->execute([
                $id,
                $tu_ngay,
                $den_ngay,
                $so_ngay,
                $ly_do
            ]);

            $_SESSION['flash'] = [
                'type' => 'success',
                'message' => 'Gửi đơn nghỉ phép thành công!'
            ];
            $this->redirect('employee/leave');
        }

        $stmt = $db->prepare("
            SELECT *
            FROM don_nghi_phep
            WHERE id_nhan_vien = ?
            ORDER BY id DESC
        ");

        $stmt->execute([$id]);
        $rows = $stmt->fetchAll();

        // Ánh xạ các cột cơ sở dữ liệu để tương thích với View
        foreach ($rows as &$row) {
            $row['tu_ngay'] = $row['ngay_bat_dau'];
            $row['den_ngay'] = $row['ngay_ket_thuc'];
        }
        unset($row);

        $this->view('employee/leave', [
            'pageTitle' => 'Đơn nghỉ phép',
            'current_page' => 'leave',
            'rows' => $rows
        ]);
    }
}