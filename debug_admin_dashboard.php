<?php
echo "=== Admin Dashboard 500 Error Debug ===\n\n";

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Check if AdminController exists and is accessible
    $adminControllerPath = 'app/controllers/AdminController.php';
    echo "1. Checking AdminController file...\n";
    
    if (file_exists($adminControllerPath)) {
        echo "   ✅ AdminController.php exists\n";
        
        // Try to include it
        require_once $adminControllerPath;
        
        if (class_exists('AdminController')) {
            echo "   ✅ AdminController class loads successfully\n";
            
            // Check if dashboard method exists
            if (method_exists('AdminController', 'dashboard')) {
                echo "   ✅ dashboard method exists\n";
            } else {
                echo "   ❌ dashboard method NOT found\n";
                
                // List available methods
                $methods = get_class_methods('AdminController');
                echo "   Available methods: " . implode(', ', $methods) . "\n";
            }
        } else {
            echo "   ❌ AdminController class NOT found after include\n";
        }
    } else {
        echo "   ❌ AdminController.php file NOT found\n";
    }
    
    echo "\n2. Checking route configuration...\n";
    
    // Check if the route is properly defined
    $routesPath = 'app/config/routes.php';
    if (file_exists($routesPath)) {
        $routesContent = file_get_contents($routesPath);
        if (strpos($routesContent, '/admin/dashboard') !== false) {
            echo "   ✅ /admin/dashboard route is defined\n";
        } else {
            echo "   ❌ /admin/dashboard route NOT found\n";
        }
    }
    
    echo "\n3. Checking dependencies...\n";
    
    // Check core files
    $coreFiles = [
        'app/config/environment.php',
        'app/config/url_helper.php',
        'app/config/database.php',
        'app/core/Controller.php'
    ];
    
    foreach ($coreFiles as $file) {
        if (file_exists($file)) {
            echo "   ✅ $file exists\n";
        } else {
            echo "   ❌ $file MISSING\n";
        }
    }
    
    echo "\n4. Testing basic includes...\n";
    
    // Test basic includes
    require_once 'app/config/environment.php';
    echo "   ✅ Environment config loaded\n";
    
    require_once 'app/config/url_helper.php';
    echo "   ✅ URL helper loaded\n";
    
    require_once 'app/core/Controller.php';
    echo "   ✅ Controller base class loaded\n";
    
    echo "\n5. Testing AdminController instantiation...\n";
    
    if (class_exists('AdminController')) {
        $adminController = new AdminController();
        echo "   ✅ AdminController instantiated successfully\n";
        
        // Test if we can call dashboard method
        if (method_exists($adminController, 'dashboard')) {
            echo "   ✅ dashboard method is callable\n";
        }
    }
    
} catch (Exception $e) {
    echo "\n❌ ERROR FOUND:\n";
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "\nStack trace:\n" . $e->getTraceAsString() . "\n";
} catch (Error $e) {
    echo "\n❌ FATAL ERROR FOUND:\n";
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "\nStack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== Debug Complete ===\n";
?>