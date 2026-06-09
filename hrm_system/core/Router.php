<?php
class Router {
    public static function dispatch() {
        // Lấy URL path hiện tại
        $requestUri = $_SERVER['REQUEST_URI'];
        $scriptName = $_SERVER['SCRIPT_NAME'];
        
        // Loại bỏ phần base path của thư mục (nếu có ví dụ /hrm_system/)
        $basePath = str_replace('/index.php', '', $scriptName);
        $url = substr($requestUri, strlen($basePath));
        
        // Loại bỏ query string (ví dụ ?id=1)
        if (($pos = strpos($url, '?')) !== false) {
            $url = substr($url, 0, $pos);
        }
        
        $url = trim($url, '/');
        $parts = explode('/', $url);

        // Xác định controller mặc định
        $controllerName = 'DashboardController';
        $action = 'index';

        if (!empty($parts[0])) {
            // Chuyển đổi từ 'employee' thành 'EmployeeController'
            $controllerName = str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $parts[0]))) . 'Controller';
        }

        if (isset($parts[1]) && !empty($parts[1])) {
            $action = $parts[1];
        }

        if (class_exists($controllerName)) {
            $controllerInstance = new $controllerName();
            if (method_exists($controllerInstance, $action)) {
                // Lấy các tham số còn lại truyền vào action
                $params = array_slice($parts, 2);
                call_user_func_array([$controllerInstance, $action], $params);
            } else {
                http_response_code(404);
                echo "<h1>404 Không tìm thấy action [{$action}] trong [{$controllerName}]</h1>";
            }
        } else {
            // Nếu không tìm thấy controller, chuyển hướng về Dashboard nếu đã login, ngược lại login
            $controllerName = 'AuthController';
            $action = 'login';
            if (class_exists($controllerName)) {
                $controllerInstance = new $controllerName();
                $controllerInstance->$action();
            } else {
                die("Lỗi hệ thống: Không thể khởi chạy AuthController.");
            }
        }
    }
}
