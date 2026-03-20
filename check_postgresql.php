<?php
// Check PostgreSQL driver availability
echo "<h3>PostgreSQL Driver Check</h3>";

if (extension_loaded('pdo_pgsql')) {
    echo "✅ PostgreSQL PDO driver is installed<br>";
} else {
    echo "❌ PostgreSQL PDO driver is NOT installed<br>";
    echo "Solutions:<br>";
    echo "1. Install php-pgsql extension<br>";
    echo "2. Enable pdo_pgsql in php.ini<br>";
    echo "3. Disable PostgreSQL sync if not needed<br>";
}

// Check if PostgreSQL sync can be disabled
$configFiles = [
    __DIR__ . '/app/config/database.php',
    __DIR__ . '/app/config/sync.php',
    __DIR__ . '/app/helpers/PostgreSQLSync.php'
];

echo "<br><h3>Config Files Check</h3>";
foreach ($configFiles as $file) {
    if (file_exists($file)) {
        echo "📁 Found: " . basename($file) . "<br>";
        $content = file_get_contents($file);
        if (strpos($content, 'postgresql') !== false || strpos($content, 'pgsql') !== false) {
            echo "⚠️ Contains PostgreSQL references<br>";
        }
    } else {
        echo "❌ Not found: " . basename($file) . "<br>";
    }
}

// Provide fix options
echo "<br><h3>Fix Options</h3>";
echo "Option 1: Install PostgreSQL driver<br>";
echo "- Ubuntu/Debian: sudo apt-get install php-pgsql<br>";
echo "- CentOS/RHEL: sudo yum install php-pgsql<br>";
echo "- Windows: Enable extension=pdo_pgsql in php.ini<br>";

echo "<br>Option 2: Disable PostgreSQL sync<br>";
echo "- Comment out PostgreSQL sync code<br>";
echo "- Use MySQL only<br>";
?>