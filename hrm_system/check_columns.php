<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/config/database.php';

echo "<h3>HỆ THỐNG KIỂM TRA LỖI UPLOAD & DATABASE</h3>";

try {
    $db = getDB();
    $stmt = $db->query("DESCRIBE nhan_vien");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "✅ Kết nối Database OK. Các cột: <b>" . implode(', ', $columns) . "</b><br><br>";
} catch (Exception $e) {
    echo "❌ Lỗi Database: " . $e->getMessage() . "<br><br>";
}

echo "<h4>THÔNG TIN CẤU HÌNH DATABASE</h4>";
$db_config = require __DIR__ . '/config/db_config.php';
echo "• Host (db_config.php): <b>" . $db_config['host'] . "</b><br>";
echo "• DB User (db_config.php): <b>" . $db_config['username'] . "</b><br>";
echo "• DB Name (db_config.php): <b>" . $db_config['dbname'] . "</b><br>";
echo "• Host (database.php constant DB_HOST): <b>" . (defined('DB_HOST') ? DB_HOST : 'Not defined') . "</b><br>";
echo "• DB User (database.php constant DB_USER): <b>" . (defined('DB_USER') ? DB_USER : 'Not defined') . "</b><br>";
echo "• DB Name (database.php constant DB_NAME): <b>" . (defined('DB_NAME') ? DB_NAME : 'Not defined') . "</b><br><br>";

// Đường dẫn tương ứng trong Controller:
// Controller nằm ở app/controllers, nên: __DIR__ . '/../../public/assets/uploads/employees'
// Ở đây file check_columns.php ở root, nên đường dẫn tương đương là: __DIR__ . '/public/assets/uploads/employees'

$dir_emp = __DIR__ . '/public/assets/uploads/employees';
$dir_sig = __DIR__ . '/public/assets/uploads/signatures';

echo "Thư mục ảnh thẻ mong muốn: <code>" . $dir_emp . "</code><br>";
echo "Thư mục chữ ký mong muốn: <code>" . $dir_sig . "</code><br><br>";

// Thử tạo thư mục
if (!is_dir($dir_emp)) {
    echo "Đang thử tạo thư mục employees...<br>";
    if (@mkdir($dir_emp, 0777, true)) {
        echo "✅ Tạo thư mục employees thành công!<br>";
    } else {
        echo "❌ THẤT BẠI tạo thư mục employees. Lỗi phân quyền ghi trên server!<br>";
    }
} else {
    echo "✅ Thư mục employees đã tồn tại.<br>";
}

if (!is_dir($dir_sig)) {
    echo "Đang thử tạo thư mục signatures...<br>";
    if (@mkdir($dir_sig, 0777, true)) {
        echo "✅ Tạo thư mục signatures thành công!<br>";
    } else {
        echo "❌ THẤT BẠI tạo thư mục signatures. Lỗi phân quyền ghi trên server!<br>";
    }
} else {
    echo "✅ Thư mục signatures đã tồn tại.<br>";
}

// Thử ghi file nháp
if (is_dir($dir_emp)) {
    $test_file = $dir_emp . '/test_write.txt';
    if (@file_put_contents($test_file, 'test')) {
        echo "✅ Ghi file nháp thành công vào thư mục employees!<br>";
        @unlink($test_file);
    } else {
        echo "❌ THẤT BẠI ghi file nháp vào thư mục employees. Server chặn quyền ghi tệp!<br>";
    }
}
?>
