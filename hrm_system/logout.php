<?php
require_once __DIR__ . '/includes/functions.php';
session_destroy();
header('Location: ' . Helper::route('auth/login'));
exit();
