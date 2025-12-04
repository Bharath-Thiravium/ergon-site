<?php
/**
 * Test Login System Fix
 */

echo "<h2>Login System Test</h2>";

// Test 1: Check if login route exists
echo "<h3>1. Testing Login Route</h3>";
$loginUrl = "http://localhost/ergon-site/login";
echo "Login URL: <a href='$loginUrl' target='_blank'>$loginUrl</a><br>";

// Test 2: Check if simple_login.php exists
echo "<h3>2. Testing simple_login.php</h3>";
$simpleLoginPath = __DIR__ . '/simple_login.php';
if (file_exists($simpleLoginPath)) {
    echo "✅ simple_login.php exists<br>";
} else {
    echo "❌ simple_login.php does not exist<br>";
}

// Test 3: Check AuthController
echo "<h3>3. Testing AuthController</h3>";
$authControllerPath = __DIR__ . '/app/controllers/AuthController.php';
if (file_exists($authControllerPath)) {
    echo "✅ AuthController.php exists<br>";
    
    // Check if the file is readable
    if (is_readable($authControllerPath)) {
        echo "✅ AuthController.php is readable<br>";
    } else {
        echo "❌ AuthController.php is not readable<br>";
    }
} else {
    echo "❌ AuthController.php does not exist<br>";
}

// Test 4: Check .htaccess
echo "<h3>4. Testing .htaccess</h3>";
$htaccessPath = __DIR__ . '/.htaccess';
if (file_exists($htaccessPath)) {
    echo "✅ .htaccess exists<br>";
    echo "Content preview:<br>";
    echo "<pre>" . htmlspecialchars(substr(file_get_contents($htaccessPath), 0, 200)) . "...</pre>";
} else {
    echo "❌ .htaccess does not exist<br>";
}

// Test 5: Check if mod_rewrite is enabled (basic check)
echo "<h3>5. Testing URL Rewriting</h3>";
if (function_exists('apache_get_modules')) {
    $modules = apache_get_modules();
    if (in_array('mod_rewrite', $modules)) {
        echo "✅ mod_rewrite is loaded<br>";
    } else {
        echo "❌ mod_rewrite is not loaded<br>";
    }
} else {
    echo "⚠️ Cannot check mod_rewrite status (apache_get_modules not available)<br>";
}

echo "<h3>6. Manual Test</h3>";
echo "Try accessing: <a href='/ergon-site/login' target='_blank'>/ergon-site/login</a><br>";
echo "If it shows the login page, the routing is working correctly.<br>";

?>
