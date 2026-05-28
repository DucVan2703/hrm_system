<?php
// Script giải nén tự động trên server
$zipFile = 'hrm_system.zip';

if (!file_exists($zipFile)) {
    die("Lỗi: Không tìm thấy file $zipFile trên server. Vui lòng upload file zip này lên trước.");
}

$zip = new ZipArchive;
if ($zip->open($zipFile) === TRUE) {
    // Giải nén vào thư mục hiện tại
    $zip->extractTo(__DIR__);
    $zip->close();
    echo "<h2>Giải nén thành công!</h2>";
    echo "<p>Tất cả các thư mục và file đã được giải nén vào htdocs.</p>";
    // Tự động xóa file zip và chính nó sau khi giải nén để bảo mật
    @unlink($zipFile);
    echo "<p>Đã xóa file zip để bảo mật.</p>";
} else {
    echo "<h2>Giải nén thất bại!</h2>";
}
?>
