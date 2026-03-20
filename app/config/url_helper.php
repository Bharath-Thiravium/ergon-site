<?php
/**
 * URL Helper Functions
 * Provides dynamic URL generation for both main domain and subdomains
 */

if (!function_exists('getBaseUrl')) {
    function getBaseUrl() {
        require_once __DIR__ . '/environment.php';
        return Environment::getBaseUrl();
    }
}

if (!function_exists('url')) {
    function url($path = '') {
        $baseUrl = getBaseUrl();
        
        // Ensure path starts with /
        if (!empty($path) && strpos($path, '/') !== 0) {
            $path = '/' . $path;
        }
        
        return $baseUrl . $path;
    }
}

if (!function_exists('redirect')) {
    function redirect($path) {
        $url = url($path);
        header("Location: $url");
        exit;
    }
}

if (!function_exists('redirectToLogin')) {
    function redirectToLogin($message = null) {
        if ($message) {
            session_start();
            $_SESSION['login_error'] = $message;
        }
        redirect('/login');
    }
}
?>