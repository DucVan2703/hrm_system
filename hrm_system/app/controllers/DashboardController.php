<?php
class DashboardController extends BaseController {
    public function index() {
        Helper::requireAdmin();

        $model = new DashboardModel();
        $tq = $model->getTongQuan();
        $chartData = $model->getQuyLuongSauThang();
        $nvMoi = $model->getNhanVienMoi();
        $pbStats = $model->getPhanBoPhongBan();

        $this->view('dashboard/index', [
            'pageTitle' => 'Dashboard',
            'tq' => $tq,
            'chartData' => $chartData,
            'nvMoi' => $nvMoi,
            'pbStats' => $pbStats
        ]);
    }
}
