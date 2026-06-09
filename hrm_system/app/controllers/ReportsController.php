<?php

require_once __DIR__ . '/../models/AdminCrudModel.php';

class ReportsController extends BaseController
{
    public function index()
    {
        Helper::requireAdmin();
        $model = new AdminCrudModel();
        $thang = (int)($_GET['thang'] ?? date('n'));
        $nam = (int)($_GET['nam'] ?? date('Y'));
        $this->view('reports/index', [
            'pageTitle' => 'Bao cao thong ke',
            'current_page' => 'reports',
            'stats' => $model->dashboardReports($thang, $nam),
            'thang' => $thang,
            'nam' => $nam,
            'months' => range(1, 12),
            'years' => range(date('Y') - 2, date('Y') + 1),
        ]);
    }
}
