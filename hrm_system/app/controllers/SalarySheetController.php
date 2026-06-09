<?php
class SalarySheetController extends BaseController {
    public function index() {
        Helper::requireAdmin();

        $model = new SalarySheetModel();
        $thang = (int)($_GET['thang'] ?? date('n'));
        $nam = (int)($_GET['nam'] ?? date('Y'));

        $list = $model->getSalarySheet($thang, $nam);
        $tongQuyLuong = array_sum(array_column($list, 'thuc_linh'));

        $months = range(1, 12);
        $years = range(date('Y') - 2, date('Y') + 1);

        $this->view('salary_sheet/index', [
            'pageTitle' => 'Bảng lương',
            'list' => $list,
            'thang' => $thang,
            'nam' => $nam,
            'tongQuyLuong' => $tongQuyLuong,
            'months' => $months,
            'years' => $years
        ]);
    }
}
