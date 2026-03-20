<?php
require_once __DIR__ . '/../config/url_helper.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    redirectToLogin();
    exit;
}
?>
