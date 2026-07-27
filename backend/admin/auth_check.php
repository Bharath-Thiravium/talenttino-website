<?php
session_start();
require_once __DIR__ . '/admin-helpers.php';
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}
define('DB_OPTIONAL', false);
require_once '../includes/db.php';
?>
