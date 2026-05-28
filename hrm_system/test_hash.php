<?php
require_once 'config/database.php';
$db = getDB();
$stmt = $db->prepare("SELECT mat_khau FROM tai_khoan WHERE ten_dang_nhap='admin'");
$stmt->execute();
$user = $stmt->fetch();
echo "Mat khau hien tai: " . $user['mat_khau'] . "\n";
echo "Password verify: " . (password_verify('password', $user['mat_khau']) ? 'true' : 'false') . "\n";
