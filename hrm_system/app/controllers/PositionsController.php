<?php

require_once __DIR__ . '/../models/AdminCrudModel.php';

class PositionsController extends BaseController
{
    public function index()
    {
        Helper::requireHR();
        $model = new AdminCrudModel();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int)($_POST['id'] ?? 0);
            if (($_POST['type'] ?? '') === 'delete') {
                $model->delete('chuc_vu', $id);
            } else {
                $data = [
                    'ma_cv' => Helper::sanitize($_POST['ma_cv'] ?? ''),
                    'ten_cv' => Helper::sanitize($_POST['ten_cv'] ?? ''),
                    'mo_ta' => Helper::sanitize($_POST['mo_ta'] ?? ''),
                    'he_so_luong' => (float)($_POST['he_so_luong'] ?? 1),
                    'trang_thai' => (int)($_POST['trang_thai'] ?? 1),
                ];
                $id ? $model->update('chuc_vu', $data, $id) : $model->insert('chuc_vu', $data);
            }
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Da luu du lieu'];
            $this->redirect('positions');
        }
        $this->view('admin/simple_crud', [
            'pageTitle' => 'Chuc vu',
            'current_page' => 'positions',
            'routeName' => 'positions',
            'icon' => 'fa-user-tie',
            'tableTitle' => 'Danh sach chuc vu',
            'rows' => $model->all('chuc_vu', 'id DESC'),
            'columns' => ['ma_cv' => 'Ma CV', 'ten_cv' => 'Ten chuc vu', 'he_so_luong' => 'He so', 'trang_thai' => 'Trang thai'],
            'fields' => [
                ['name' => 'ma_cv', 'label' => 'Ma chuc vu', 'required' => true],
                ['name' => 'ten_cv', 'label' => 'Ten chuc vu', 'required' => true],
                ['name' => 'he_so_luong', 'label' => 'He so luong', 'type' => 'number', 'step' => '0.01'],
                ['name' => 'mo_ta', 'label' => 'Mo ta', 'type' => 'textarea'],
                ['name' => 'trang_thai', 'label' => 'Trang thai', 'type' => 'select', 'options' => ['1' => 'Hoat dong', '0' => 'Tam dung']],
            ],
        ]);
    }
}
