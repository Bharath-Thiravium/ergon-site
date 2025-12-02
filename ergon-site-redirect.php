<?php
/**
 * Redirect handler for /ergon-site/ path
 * Place this file in your web root if you want to maintain the /ergon-site/ URL structure
 */

// Get the request URI
$requestUri = $_SERVER['REQUEST_URI'] ?? '';

// Remove /ergon-site/ from the beginning if present
if (strpos($requestUri, '/ergon-site/') === 0) {
    $newUri = substr($requestUri, 11); // Remove '/ergon-site'
    if (empty($newUri)) {
        $newUri = '/';
    }
} else {
    $newUri = $requestUri;
}

// Update the REQUEST_URI for the application
$_SERVER['REQUEST_URI'] = $newUri;

// Include the main application
require_once __DIR__ . '/index.php';
?>