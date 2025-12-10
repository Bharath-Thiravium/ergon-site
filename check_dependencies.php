<?php
echo "<h2>PHP Dependencies Check</h2>";

// Check PHP version
echo "PHP Version: " . PHP_VERSION . "<br>";

// Check required extensions
$required_extensions = ['pdo', 'pdo_mysql', 'json', 'session', 'curl'];

foreach ($required_extensions as $ext) {
    if (extension_loaded($ext)) {
        echo "✅ $ext: Loaded<br>";
    } else {
        echo "❌ $ext: NOT LOADED<br>";
    }
}

// Check if composer dependencies are installed
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    echo "✅ Composer dependencies: Installed<br>";
} else {
    echo "❌ Composer dependencies: NOT INSTALLED<br>";
    echo "Run: composer install<br>";
}

// Check database connection
try {
    require_once __DIR__ . '/app/config/database.php';
    $db = Database::connect();
    echo "✅ Database connection: OK<br>";
} catch (Exception $e) {
    echo "❌ Database connection: FAILED - " . $e->getMessage() . "<br>";
}

// Check session functionality
session_start();
$_SESSION['test'] = 'working';
if (isset($_SESSION['test'])) {
    echo "✅ Sessions: Working<br>";
    unset($_SESSION['test']);
} else {
    echo "❌ Sessions: NOT WORKING<br>";
}

// Check file permissions
$dirs_to_check = [
    __DIR__ . '/storage',
    __DIR__ . '/storage/sessions'
];

foreach ($dirs_to_check as $dir) {
    if (is_dir($dir) && is_writable($dir)) {
        echo "✅ $dir: Writable<br>";
    } else {
        echo "❌ $dir: NOT WRITABLE<br>";
    }
}

echo "<br><a href='/ergon-site/test_notifications.php'>Test Notifications</a><br>";
echo "<a href='/ergon-site/dashboard'>Back to Dashboard</a>";
?>