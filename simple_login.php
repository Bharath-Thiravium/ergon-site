<?php
/**
 * Simple Login Redirect
 * This file redirects to the proper login route
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// If this is a POST request, forward the data to the proper login endpoint
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Include the main application
    require_once __DIR__ . '/index.php';
    exit;
}

// For GET requests, redirect to the login page
header('Location: /ergon-site/login');
exit;
?>