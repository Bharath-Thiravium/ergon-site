<?php
echo "=== AdminController Dashboard Test ===\n\n";

// Simulate admin session
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'admin';
$_SESSION['department_id'] = 1;

// Set subdomain environment
$_SERVER['HTTP_HOST'] = 'aes.athenas.co.in';
$_SERVER['HTTPS'] = 'on';

try {
    // Test basic includes
    require_once 'app/config/environment.php';
    require_once 'app/config/url_helper.php';
    require_once 'app/core/Controller.php';
    
    echo "✅ Core files loaded successfully\n";
    
    // Test AdminController loading
    require_once 'app/controllers/AdminController.php';
    echo "✅ AdminController loaded successfully\n";
    
    // Test instantiation
    $adminController = new AdminController();
    echo "✅ AdminController instantiated successfully\n";
    
    // Test URL helper functions are available
    echo "Base URL: " . getBaseUrl() . "\n";
    echo "Login URL: " . url('/login') . "\n";
    echo "Dashboard URL: " . url('/admin/dashboard') . "\n";
    
    echo "\n✅ All tests passed - AdminController should work now!\n";
    
} catch (Exception $e) {
    echo "\n❌ ERROR:\n";
    echo "Message: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
} catch (Error $e) {
    echo "\n❌ FATAL ERROR:\n";
    echo "Message: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
?>