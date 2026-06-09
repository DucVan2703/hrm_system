<?php

require_once __DIR__ . '/../models/AdminCrudModel.php';

class AttendanceController extends BaseController
{
    public function daily()
    {
        Helper::requireHR();
        $this->manage('attendance-daily');
    }

    public function my()
    {
        Helper::requireLogin();
        $this->manage('my-attendance', true);
    }

    public function manage($currentPage, $onlyMine = false)
    {
        $model = new AdminCrudModel();
        $thang = (int)($_GET['thang'] ?? date('n'));
        $nam = (int)($_GET['nam'] ?? date('Y'));

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$onlyMine) {
            $postThang = (int)($_POST['thang'] ?? $thang);
            $postNam = (int)($_POST['nam'] ?? $nam);

            // Chấm công toàn bộ nhân viên từ tài khoản admin/hr.
            if (isset($_POST['bulk_attendance']) && is_array($_POST['attendance'] ?? null)) {
                $updated = 0;

                foreach ($_POST['attendance'] as $idNhanVien => $item) {
                    $idNhanVien = (int)$idNhanVien;
                    if ($idNhanVien <= 0 || !$model->employeeExists($idNhanVien)) {
                        continue;
                    }

                    $model->saveAttendance([
                        'id_nhan_vien' => $idNhanVien,
                        'thang' => $postThang,
                        'nam' => $postNam,
                        'so_ngay_lam' => (float)($item['so_ngay_lam'] ?? 0),
                        'so_ngay_nghi' => (int)($item['so_ngay_nghi'] ?? 0),
                        'so_ngay_phep' => (int)($item['so_ngay_phep'] ?? 0),
                        'so_ngay_vang' => (int)($item['so_ngay_vang'] ?? 0),
                        'so_gio_tang_ca' => (float)($item['so_gio_tang_ca'] ?? 0),
                        'ghi_chu' => Helper::sanitize($item['ghi_chu'] ?? ''),
                        'nguoi_cap_nhat' => $_SESSION['user_id'] ?? null,
                    ]);
                    $updated++;
                }

                $_SESSION['flash'] = [
                    'type' => 'success',
                    'message' => 'Đã cập nhật chấm công toàn bộ ' . $updated . ' nhân viên'
                ];
                $this->redirect('attendance/daily?thang=' . $postThang . '&nam=' . $postNam);
            }

            // Chấm công từng nhân viên bằng nút Sửa.
            $idNhanVien = (int)($_POST['id_nhan_vien'] ?? 0);
            if ($idNhanVien <= 0 || !$model->employeeExists($idNhanVien)) {
                $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Không tìm thấy nhân viên hợp lệ để chấm công'];
                $this->redirect('attendance/daily?thang=' . $thang . '&nam=' . $nam);
            }

            $data = [
                'id_nhan_vien' => $idNhanVien,
                'thang' => $postThang,
                'nam' => $postNam,
                'so_ngay_lam' => (float)($_POST['so_ngay_lam'] ?? 0),
                'so_ngay_nghi' => (int)($_POST['so_ngay_nghi'] ?? 0),
                'so_ngay_phep' => (int)($_POST['so_ngay_phep'] ?? 0),
                'so_ngay_vang' => (int)($_POST['so_ngay_vang'] ?? 0),
                'so_gio_tang_ca' => (float)($_POST['so_gio_tang_ca'] ?? 0),
                'ghi_chu' => Helper::sanitize($_POST['ghi_chu'] ?? ''),
                'nguoi_cap_nhat' => $_SESSION['user_id'] ?? null,
            ];
            $model->saveAttendance($data);
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Đã cập nhật chấm công'];
            $this->redirect('attendance/daily?thang=' . $data['thang'] . '&nam=' . $data['nam']);
        }

        $rows = $model->attendance($thang, $nam);
        if ($onlyMine) {
            $mine = (int)($_SESSION['id_nhan_vien'] ?? 0);
            $rows = array_values(array_filter($rows, function ($row) use ($mine) {
                return (int)$row['id_nhan_vien'] === $mine;
            }));
        }

        $this->view('attendance/index', [
            'pageTitle' => $onlyMine ? 'Chấm công cá nhân' : 'Chấm công hằng ngày',
            'current_page' => $currentPage,
            'rows' => $rows,
            'thang' => $thang,
            'nam' => $nam,
            'months' => range(1, 12),
            'years' => range(date('Y') - 2, date('Y') + 1),
            'onlyMine' => $onlyMine,
        ]);
    }
}

