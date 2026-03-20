<?php
// Enable all error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

echo "=== Comprehensive 500 Error Debug ===\n\n";

// Simulate the exact request environment
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/ergon-site/admin/dashboard';
$_SERVER['HTTP_HOST'] = 'aes.athenas.co.in';
$_SERVER['HTTPS'] = 'on';

// Start session like the real application
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'admin';
$_SESSION['user_name'] = 'Test Admin';
$_SESSION['user_email'] = 'admin@test.com';

echo "1. Environment Setup:\n";
echo "   Host: " . $_SERVER['HTTP_HOST'] . "\n";
echo "   URI: " . $_SERVER['REQUEST_URI'] . "\n";
echo "   Method: " . $_SERVER['REQUEST_METHOD'] . "\n";
echo "   Session User ID: " . $_SESSION['user_id'] . "\n\n";

try {
    echo "2. Loading Core Files:\n";
    
    // Load files in the same order as index.php
    require_once __DIR__ . '/app/config/session.php';
    echo "   ✅ Session config loaded\n";
    
    require_once __DIR__ . '/app/config/environment.php';
    echo "   ✅ Environment config loaded\n";
    
    require_once __DIR__ . '/app/config/url_helper.php';
    echo "   ✅ URL helper loaded\n";
    
    require_once __DIR__ . '/app/config/database.php';
    echo "   ✅ Database config loaded\n";
    
    require_once __DIR__ . '/app/core/Router.php';
    echo "   ✅ Router loaded\n";
    
    require_once __DIR__ . '/app/core/Controller.php';
    echo "   ✅ Controller base class loaded\n";
    
    echo "\n3. Testing Database Connection:\n";
    $db = Database::connect();
    echo "   ✅ Database connection successful\n";
    
    echo "\n4. Loading AdminController:\n";
    require_once __DIR__ . '/app/controllers/AdminController.php';
    echo "   ✅ AdminController file loaded\n";
    
    echo "\n5. Testing AdminController Instantiation:\n";
    $adminController = new AdminController();
    echo "   ✅ AdminController instantiated\n";
    
    echo "\n6. Testing AuthMiddleware:\n";
    require_once __DIR__ . '/app/middlewares/AuthMiddleware.php';
    echo "   ✅ AuthMiddleware loaded\n";
    
    echo "\n7. Testing Dashboard Method Dependencies:\n";
    
    // Check if all required model files exist
    $requiredModels = [
        'app/models/User.php',
        'app/models/Task.php', 
        'app/models/Leave.php',
        'app/models/Expense.php',
        'app/models/Advance.php',
        'app/models/Attendance.php',
        'app/models/Department.php'
    ];
    
    foreach ($requiredModels as $model) {
        if (file_exists($model)) {
            echo "   ✅ $model exists\n";
        } else {
            echo "   ❌ $model MISSING\n";
        }
    }
    
    echo "\n8. Testing Helper Files:\n";
    $helpers = [
        'app/helpers/RoleManager.php',
        'app/helpers/DatabaseHelper.php'
    ];
    
    foreach ($helpers as $helper) {
        if (file_exists($helper)) {
            echo "   ✅ $helper exists\n";
        } else {
            echo "   ❌ $helper MISSING\n";
        }
    }
    
    echo "\n9. Attempting to Call Dashboard Method:\n";
    
    // Capture any output from the dashboard method
    ob_start();
    
    try {
        $adminController->dashboard();
        $output = ob_get_contents();
        echo "   ✅ Dashboard method executed successfully\n";
        echo "   Output length: " . strlen($output) . " characters\n";
    } catch (Exception $e) {
        ob_end_clean();
        throw $e;
    }
    
    ob_end_clean();
    
    echo "\n✅ ALL TESTS PASSED - The issue might be elsewhere\n";
    
} catch (Exception $e) {
    echo "\n❌ EXCEPTION CAUGHT:\n";
    echo "Type: " . get_class($e) . "\n";
    echo "Message: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "\nStack Trace:\n" . $e->getTraceAsString() . "\n";
} catch (Error $e) {
    echo "\n❌ FATAL ERROR CAUGHT:\n";
    echo "Type: " . get_class($e) . "\n";
    echo "Message: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "\nStack Trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== Debug Complete ===\n";
?>