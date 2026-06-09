<?php

require_once __DIR__ . '/../models/AdminCrudModel.php';

class AllowancesController extends BaseController
{
    public function index()
    {
        Helper::requireKetoan();
        $model = new AdminCrudModel();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int)($_POST['id'] ?? 0);
            if (($_POST['type'] ?? '') === 'delete') {
                $model->delete('phu_cap', $id);
            } else {
                $data = [
                    'ma_pc' => Helper::sanitize($_POST['ma_pc'] ?? ''),
                    'ten_phu_cap' => Helper::sanitize($_POST['ten_phu_cap'] ?? ''),
                    'so_tien' => (float)($_POST['so_tien'] ?? 0),
                    'mo_ta' => Helper::sanitize($_POST['mo_ta'] ?? ''),
                    'trang_thai' => (int)($_POST['trang_thai'] ?? 1),
                ];
                $id ? $model->update('phu_cap', $data, $id) : $model->insert('phu_cap', $data);
            }
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Da luu du lieu'];
            $this->redirect('allowances');
        }
        $this->view('admin/simple_crud', [
            'pageTitle' => 'Phu cap',
            'current_page' => 'allowances',
            'routeName' => 'allowances',
            'icon' => 'fa-hand-holding-usd',
            'tableTitle' => 'Danh muc phu cap',
            'rows' => $model->all('phu_cap', 'id DESC'),
            'columns' => ['ma_pc' => 'Ma PC', 'ten_phu_cap' => 'Ten phu cap', 'so_tien' => 'So tien', 'trang_thai' => 'Trang thai'],
            'moneyColumns' => ['so_tien'],
            'fields' => [
                ['name' => 'ma_pc', 'label' => 'Ma phu cap', 'required' => true],
                ['name' => 'ten_phu_cap', 'label' => 'Ten phu cap', 'required' => true],
                ['name' => 'so_tien', 'label' => 'So tien', 'type' => 'number'],
                ['name' => 'mo_ta', 'label' => 'Mo ta', 'type' => 'textarea'],
                ['name' => 'trang_thai', 'label' => 'Trang thai', 'type' => 'select', 'options' => ['1' => 'Hoat dong', '0' => 'Tam dung']],
            ],
        ]);
    }
}
