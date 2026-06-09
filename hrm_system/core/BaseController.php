<?php
class BaseController {
    // Gọi và truyền dữ liệu cho view
    protected function view($view, $data = []) {
        extract($data);
        
        // Thêm helper function cho view dễ sử dụng
        if (!function_exists('route')) {
            function route($path) {
                $config = require __DIR__ . '/../config/db_config.php';
                return rtrim($config['base_url'], '/') . '/' . ltrim($path, '/');
            }
        }
        
        if (!function_exists('asset')) {
            function asset($path) {
                $config = require __DIR__ . '/../config/db_config.php';
                return rtrim($config['base_url'], '/') . '/public/assets/' . ltrim($path, '/');
            }
        }

        $viewPath = __DIR__ . '/../app/views/' . $view . '.php';
        if (file_exists($viewPath)) {
            require $viewPath;
        } else {
            die("Không tìm thấy view: " . $view);
        }
    }

    protected function redirect($path) {
        $config = require __DIR__ . '/../config/db_config.php';
        $url = rtrim($config['base_url'], '/') . '/' . ltrim($path, '/');
        header("Location: " . $url);
        exit;
    }
}
