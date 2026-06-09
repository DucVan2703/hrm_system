<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>KẾT QUẢ QUÉT TỆP TIN TRÊN MÁY CHỦ THỰC TẾ</h2>";

$controllers = [
    'AccountsController',
    'AllowancesController',
    'AttendanceController',
    'AuthController',
    'ChatbotController',
    'ContractsController',
    'DashboardController',
    'DepartmentsController',
    'EmployeeController',
    'LeaveRequestsController',
    'PayrollController',
    'PositionsController',
    'ReportsController',
    'SalarySheetController',
    'SettingsController',
    'TimekeepingController'
];

$models = [
    'AdminCrudModel',
    'ChatBotModel',
    'DashboardModel',
    'EmployeeModel',
    'PayrollModel',
    'SalarySheetModel',
    'UserModel'
];

$views = [
    'admin/simple_crud',
    'attendance/index',
    'attendance/my',
    'auth/login',
    'chatbot/index',
    'dashboard/index',
    'employee/dashboard',
    'employee/detail',
    'employee/leave',
    'employee/list',
    'employee/profile',
    'layouts/header',
    'layouts/sidebar',
    'payroll/index',
    'reports/index',
    'salary_sheet/index',
    'settings/index'
];

echo "<h3>1. Kiểm tra các thư mục cốt lõi (Core/App):</h3>";
$dirs = [
    'core' => __DIR__ . '/core',
    'app/controllers' => __DIR__ . '/app/controllers',
    'app/models' => __DIR__ . '/app/models',
    'app/views' => __DIR__ . '/app/views',
    'includes' => __DIR__ . '/includes'
];
foreach ($dirs as $name => $path) {
    echo "Thư mục <b>$name</b>: " . (is_dir($path) ? "<span style='color:green'>TỒN TẠI</span>" : "<span style='color:red'>KHÔNG TỒN TẠI</span>") . " ($path)<br>";
}

echo "<h3>2. Kiểm tra các tệp tin Controllers trong app/controllers:</h3>";
foreach ($controllers as $ctrl) {
    $file = __DIR__ . "/app/controllers/{$ctrl}.php";
    echo "Controller <b>{$ctrl}.php</b>: " . (file_exists($file) ? "<span style='color:green'>FOUND</span>" : "<span style='color:red; font-weight:bold;'>MISSING (THIẾU)</span>") . "<br>";
}

echo "<h3>3. Kiểm tra các tệp tin Models trong app/models:</h3>";
foreach ($models as $mdl) {
    $file = __DIR__ . "/app/models/{$mdl}.php";
    echo "Model <b>{$mdl}.php</b>: " . (file_exists($file) ? "<span style='color:green'>FOUND</span>" : "<span style='color:red; font-weight:bold;'>MISSING (THIẾU)</span>") . "<br>";
}

echo "<h3>4. Kiểm tra các tệp tin Views trong app/views:</h3>";
foreach ($views as $vw) {
    $file = __DIR__ . "/app/views/{$vw}.php";
    echo "View <b>{$vw}.php</b>: " . (file_exists($file) ? "<span style='color:green'>FOUND</span>" : "<span style='color:red; font-weight:bold;'>MISSING (THIẾU)</span>") . "<br>";
}
