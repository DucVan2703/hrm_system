<?php

require_once __DIR__ . '/../models/AdminCrudModel.php';

class LeaveRequestsController extends BaseController
{
    public function index()
    {
        Helper::requireHR();
        $model = new AdminCrudModel();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int)($_POST['id'] ?? 0);
            if (($_POST['type'] ?? '') === 'delete') {
                $model->delete('don_nghi_phep', $id);
            } else {
                $data = [
                    'id_nhan_vien' => (int)($_POST['id_nhan_vien'] ?? ($_SESSION['id_nhan_vien'] ?? 0)),
                    'ngay_bat_dau' => $_POST['ngay_bat_dau'] ?? null,
                    'ngay_ket_thuc' => $_POST['ngay_ket_thuc'] ?? null,
                    'so_ngay' => (int)($_POST['so_ngay'] ?? 1),
                    'loai_phep' => Helper::sanitize($_POST['loai_phep'] ?? ''),
                    'ly_do' => Helper::sanitize($_POST['ly_do'] ?? ''),
                    'trang_thai' => Helper::sanitize($_POST['trang_thai'] ?? 'Chờ duyệt'),
                    'ghi_chu_duyet' => Helper::sanitize($_POST['ghi_chu_duyet'] ?? ''),
                ];
                if ($data['trang_thai'] !== 'Chờ duyệt') {
                    $data['nguoi_duyet'] = $_SESSION['user_id'] ?? null;
                    $data['ngay_duyet'] = date('Y-m-d H:i:s');
                }
                $id ? $model->update('don_nghi_phep', $data, $id) : $model->insert('don_nghi_phep', $data);
            }
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Đã lưu đơn nghỉ phép'];
            $this->redirect('leave-requests');
        }
        $employees = [];
        foreach ($model->employees() as $e) $employees[$e['id']] = $e['ma_nv'] . ' - ' . $e['ho_ten'];
        $this->view('admin/simple_crud', [
            'pageTitle' => 'Đơn nghỉ phép',
            'current_page' => 'leave-requests',
            'routeName' => 'leave-requests',
            'icon' => 'fa-calendar-alt',
            'tableTitle' => 'Danh sách đơn nghỉ phép',
            'rows' => $model->leaves(),
            'columns' => ['ho_ten' => 'Nhân viên', 'loai_phep' => 'Loại phép', 'ngay_bat_dau' => 'Từ ngày', 'ngay_ket_thuc' => 'Đến ngày', 'so_ngay' => 'Số ngày', 'trang_thai' => 'Trạng thái'],
            'fields' => [
                ['name' => 'id_nhan_vien', 'label' => 'Nhân viên', 'type' => 'select', 'options' => $employees],
                ['name' => 'loai_phep', 'label' => 'Loại phép', 'type' => 'select', 'options' => ['Phép năm' => 'Phép năm', 'Phép không lương' => 'Phép không lương', 'Phép ốm' => 'Phép ốm', 'Phép cưới' => 'Phép cưới', 'Phép tang' => 'Phép tang']],
                ['name' => 'ngay_bat_dau', 'label' => 'Ngày bắt đầu', 'type' => 'date'],
                ['name' => 'ngay_ket_thuc', 'label' => 'Ngày kết thúc', 'type' => 'date'],
                ['name' => 'so_ngay', 'label' => 'Số ngày', 'type' => 'number'],
                ['name' => 'trang_thai', 'label' => 'Trạng thái', 'type' => 'select', 'options' => ['Chờ duyệt' => 'Chờ duyệt', 'Đã duyệt' => 'Đã duyệt', 'Từ chối' => 'Từ chối']],
                ['name' => 'ly_do', 'label' => 'Lý do', 'type' => 'textarea'],
                ['name' => 'ghi_chu_duyet', 'label' => 'Ghi chú duyệt', 'type' => 'textarea'],
            ],
        ]);
    }
}
