<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}
define('DB_OPTIONAL', false);
require_once '../includes/db.php';
?>
