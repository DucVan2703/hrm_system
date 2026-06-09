<?php

class SettingsController extends BaseController
{
    public function index()
    {
        Helper::requireOnlyAdmin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Cau hinh hien duoc doc tu file config/db_config.php'];
            $this->redirect('settings');
        }
        $config = require __DIR__ . '/../../config/db_config.php';
        $this->view('settings/index', [
            'pageTitle' => 'Cau hinh he thong',
            'current_page' => 'settings',
            'config' => $config,
        ]);
    }
}
