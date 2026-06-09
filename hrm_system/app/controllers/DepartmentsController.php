<?php

require_once __DIR__ . '/../models/AdminCrudModel.php';

class DepartmentsController extends BaseController
{
    public function index()
    {
        Helper::requireHR();
        $model = new AdminCrudModel();
        $this->handleSave($model);
        $this->view('admin/simple_crud', [
            'pageTitle' => 'Phong ban',
            'current_page' => 'departments',
            'routeName' => 'departments',
            'icon' => 'fa-sitemap',
            'tableTitle' => 'Danh sach phong ban',
            'rows' => $model->all('phong_ban', 'id DESC'),
            'columns' => [
                'ma_pb' => 'Ma PB',
                'ten_pb' => 'Ten phong ban',
                'mo_ta' => 'Mo ta',
                'trang_thai' => 'Trang thai',
            ],
            'fields' => [
                ['name' => 'ma_pb', 'label' => 'Ma phong ban', 'required' => true],
                ['name' => 'ten_pb', 'label' => 'Ten phong ban', 'required' => true],
                ['name' => 'mo_ta', 'label' => 'Mo ta', 'type' => 'textarea'],
                ['name' => 'trang_thai', 'label' => 'Trang thai', 'type' => 'select', 'options' => ['1' => 'Hoat dong', '0' => 'Tam dung']],
            ],
        ]);
    }

    private function handleSave($model)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        $id = (int)($_POST['id'] ?? 0);
        if (($_POST['type'] ?? '') === 'delete') {
            $model->delete('phong_ban', $id);
        } else {
            $data = [
                'ma_pb' => Helper::sanitize($_POST['ma_pb'] ?? ''),
                'ten_pb' => Helper::sanitize($_POST['ten_pb'] ?? ''),
                'mo_ta' => Helper::sanitize($_POST['mo_ta'] ?? ''),
                'trang_thai' => (int)($_POST['trang_thai'] ?? 1),
            ];
            $id ? $model->update('phong_ban', $data, $id) : $model->insert('phong_ban', $data);
        }
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Da luu du lieu'];
        $this->redirect('departments');
    }
}
