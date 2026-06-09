<?php
// Bật hiển thị lỗi tối đa để chẩn đoán
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Load cấu hình database và các hằng số hệ thống (BASE_URL,...)
require_once __DIR__ . '/config/database.php';

// Cấu hình tương thích ngược cho mbstring extension nếu server thiếu
if (!function_exists('mb_substr')) {
    function mb_substr($str, $start, $length = null, $encoding = 'UTF-8') {
        if ($length === null) {
            return substr($str, $start);
        }
        return substr($str, $start, $length);
    }
}
if (!function_exists('mb_strtolower')) {
    function mb_strtolower($str, $encoding = 'UTF-8') {
        return strtolower($str);
    }
}

// Autoload class tự động
spl_autoload_register(function ($class) {
    $paths = [
        __DIR__ . '/core/',
        __DIR__ . '/app/controllers/',
        __DIR__ . '/app/models/'
    ];
    foreach ($paths as $path) {
        $file = $path . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// Điều hướng ứng dụng
Router::dispatch();
