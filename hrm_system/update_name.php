<?php
require 'config/database.php';
$db = getDB();
$db->query("UPDATE cau_hinh SET gia_tri = 'Đại Học Thành Đông' WHERE ma_cau_hinh = 'APP_NAME'");
echo "Updated APP_NAME in DB.\n";
