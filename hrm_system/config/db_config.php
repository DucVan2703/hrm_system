<?php
// Phát hiện giao thức HTTPS động (hỗ trợ cả Cloudflare/Reverse Proxy)
$isHttps = false;
if (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] === 'on' || $_SERVER['HTTPS'] == 1)) {
    $isHttps = true;
} elseif (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
    $isHttps = true;
} elseif (isset($_SERVER['HTTP_FRONT_END_HTTPS']) && $_SERVER['HTTP_FRONT_END_HTTPS'] === 'on') {
    $isHttps = true;
}
$protocol = $isHttps ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'ducvan.id.vn';
$dynamic_base_url = $protocol . '://' . $host;

return [
    'host' => 'fdb1032.awardspace.net',
    'username' => '4762137_quanlyluong',
    'password' => 'Nguyenvan272005@',
    'dbname' => '4762137_quanlyluong',
    'charset' => 'utf8mb4',
    
    // Cấu hình ứng dụng
    'app_name' => 'Đại Học Thành Đông',
    'app_version' => '1.0.0',
    'base_url' => $dynamic_base_url,
    'ngay_chuan' => 26,
    'bhxh_rate' => 0.08,
    'bhyt_rate' => 0.015,
    'tncn_rate' => 0.05
];
