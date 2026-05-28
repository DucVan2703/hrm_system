<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

if (isset($_SESSION['user_id'])) {
    if (isAdmin()) { header('Location: admin/index.php'); }
    else { header('Location: employee/index.php'); }
} else {
    header('Location: login.php');
}
exit();
