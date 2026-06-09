<?php

require_once __DIR__ . '/../models/AdminCrudModel.php';

class ContractsController extends BaseController
{
    public function index()
    {
        Helper::requireHR();
        $model = new AdminCrudModel();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int)($_POST['id'] ?? 0);
            if (($_POST['type'] ?? '') === 'delete') {
                $model->delete('hop_dong', $id);
            } else {
                $data = [
                    'ma_hd' => Helper::sanitize($_POST['ma_hd'] ?? ''),
                    'id_nhan_vien' => (int)($_POST['id_nhan_vien'] ?? 0),
                    'loai_hop_dong' => Helper::sanitize($_POST['loai_hop_dong'] ?? ''),
                    'ngay_bat_dau' => $_POST['ngay_bat_dau'] ?? null,
                    'ngay_ket_thuc' => $_POST['ngay_ket_thuc'] ?: null,
                    'luong_hop_dong' => (float)($_POST['luong_hop_dong'] ?? 0),
                    'trang_thai' => Helper::sanitize($_POST['trang_thai'] ?? ''),
                    'ghi_chu' => Helper::sanitize($_POST['ghi_chu'] ?? ''),
                ];
                $id ? $model->update('hop_dong', $data, $id) : $model->insert('hop_dong', $data);
            }
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Da luu hop dong'];
            $this->redirect('contracts');
        }
        $employees = [];
        foreach ($model->employees() as $e) $employees[$e['id']] = $e['ma_nv'] . ' - ' . $e['ho_ten'];
        $this->view('admin/simple_crud', [
            'pageTitle' => 'Hop dong',
            'current_page' => 'contracts',
            'routeName' => 'contracts',
            'icon' => 'fa-file-contract',
            'tableTitle' => 'Danh sach hop dong',
            'rows' => $model->contracts(),
            'columns' => ['ma_hd' => 'Ma HD', 'ho_ten' => 'Nhan vien', 'loai_hop_dong' => 'Loai', 'ngay_bat_dau' => 'Bat dau', 'ngay_ket_thuc' => 'Ket thuc', 'luong_hop_dong' => 'Luong', 'trang_thai' => 'Trang thai'],
            'moneyColumns' => ['luong_hop_dong'],
            'fields' => [
                ['name' => 'ma_hd', 'label' => 'Ma hop dong', 'required' => true],
                ['name' => 'id_nhan_vien', 'label' => 'Nhan vien', 'type' => 'select', 'options' => $employees],
                ['name' => 'loai_hop_dong', 'label' => 'Loai hop dong', 'type' => 'select', 'options' => ['Thá»­ viá»‡c' => 'Thu viec', 'XÃ¡c Ä‘á»‹nh thá»i háº¡n' => 'Xac dinh thoi han', 'KhÃ´ng xÃ¡c Ä‘á»‹nh thá»i háº¡n' => 'Khong xac dinh thoi han']],
                ['name' => 'ngay_bat_dau', 'label' => 'Ngay bat dau', 'type' => 'date'],
                ['name' => 'ngay_ket_thuc', 'label' => 'Ngay ket thuc', 'type' => 'date'],
                ['name' => 'luong_hop_dong', 'label' => 'Luong hop dong', 'type' => 'number'],
                ['name' => 'trang_thai', 'label' => 'Trang thai', 'type' => 'select', 'options' => ['Äang hiá»‡u lá»±c' => 'Dang hieu luc', 'Háº¿t háº¡n' => 'Het han', 'ÄÃ£ káº¿t thÃºc' => 'Da ket thuc', 'Gia háº¡n' => 'Gia han']],
                ['name' => 'ghi_chu', 'label' => 'Ghi chu', 'type' => 'textarea'],
            ],
        ]);
    }
}
