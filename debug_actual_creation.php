<?php
/**
 * Debug Actual User Creation Issue
 */

// Start session and set up authentication
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'owner';

// Simulate POST data that would come from the form
$_POST = [
    'name' => 'Test User Debug',
    'email' => 'debug_' . time() . '@example.com',
    'phone' => '1234567890',
    'role' => 'company_owner',
    'status' => 'active',
    'department_id' => '',
    'designation' => 'Test Designation',
    'joining_date' => '2024-01-01',
    'salary' => '50000',
    'date_of_birth' => '1990-01-01',
    'gender' => 'male',
    'address' => 'Test Address',
    'emergency_contact' => '9876543210'
];

$_SERVER['REQUEST_METHOD'] = 'POST';

echo "<h2>Debug Actual User Creation</h2>";
echo "<h3>Simulated POST Data:</h3>";
echo "<pre>" . print_r($_POST, true) . "</pre>";

// Capture all output and errors
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    require_once __DIR__ . '/app/controllers/UsersController.php';
    
    echo "<h3>Loading UsersController...</h3>";
    $controller = new UsersController();
    
    echo "<h3>Calling create() method...</h3>";
    
    // Capture any redirects
    $headers_sent = false;
    
    // Override header function to capture redirects
    function custom_header($string) {
        global $headers_sent;
        echo "<p><strong>REDIRECT ATTEMPTED:</strong> " . htmlspecialchars($string) . "</p>";
        $headers_sent = true;
    }
    
    // Call the create method
    $controller->create();
    
    echo "<h3>Method completed successfully</h3>";
    
} catch (Exception $e) {
    echo "<h3 style='color: red;'>Exception Caught:</h3>";
    echo "<p><strong>Message:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>File:</strong> " . $e->getFile() . "</p>";
    echo "<p><strong>Line:</strong> " . $e->getLine() . "</p>";
    echo "<h4>Stack Trace:</h4>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}

$output = ob_get_clean();
echo $output;

// Check for any PHP errors
$error = error_get_last();
if ($error) {
    echo "<h3 style='color: red;'>PHP Error:</h3>";
    echo "<pre>" . print_r($error, true) . "</pre>";
}

// Check session for any stored data
echo "<h3>Session Data After:</h3>";
echo "<pre>" . print_r($_SESSION, true) . "</pre>";
?>
