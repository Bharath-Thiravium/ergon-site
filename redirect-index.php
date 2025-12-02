<?php
// Place this file as index.php in your server's root directory (public_html)
// It will redirect all requests to the ergon-site subdirectory

$requestUri = $_SERVER['REQUEST_URI'] ?? '/';

// If already in ergon-site path, include the main application
if (strpos($requestUri, '/ergon-site/') === 0) {
    require_once __DIR__ . '/ergon-site/index.php';
    exit;
}

// Redirect to ergon-site
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
header("Location: {$protocol}://{$host}/ergon-site/");
exit;
?>