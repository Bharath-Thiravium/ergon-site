<?php
// Simple error check file
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "PHP Version: " . phpversion() . "<br>";
echo "Server: " . $_SERVER['SERVER_SOFTWARE'] . "<br>";

// Test basic includes
try {
    require_once __DIR__ . '/app/config/session.php';
    echo "Session config: OK<br>";
} catch (Exception $e) {
    echo "Session config error: " . $e->getMessage() . "<br>";
}

try {
    require_once __DIR__ . '/app/config/database.php';
    echo "Database config: OK<br>";
} catch (Exception $e) {
    echo "Database config error: " . $e->getMessage() . "<br>";
}

try {
    require_once __DIR__ . '/app/core/Router.php';
    echo "Router: OK<br>";
} catch (Exception $e) {
    echo "Router error: " . $e->getMessage() . "<br>";
}

echo "Basic checks complete.";
?>