<?php
echo "<h3>PostgreSQL Connection Diagnostic</h3>";

// 1. Check if PostgreSQL extension is loaded
echo "<h4>1. Extension Check</h4>";
if (extension_loaded('pdo_pgsql')) {
    echo "✅ pdo_pgsql extension is loaded<br>";
} else {
    echo "❌ pdo_pgsql extension is NOT loaded<br>";
}

if (extension_loaded('pgsql')) {
    echo "✅ pgsql extension is loaded<br>";
} else {
    echo "❌ pgsql extension is NOT loaded<br>";
}

// 2. List all loaded extensions
echo "<h4>2. All PDO Drivers</h4>";
$drivers = PDO::getAvailableDrivers();
echo "Available PDO drivers: " . implode(', ', $drivers) . "<br>";

// 3. Check PostgreSQL config files
echo "<h4>3. Configuration Files</h4>";
$configFiles = [
    __DIR__ . '/app/config/database.php',
    __DIR__ . '/app/config/postgresql.php',
    __DIR__ . '/config/database.php'
];

foreach ($configFiles as $file) {
    if (file_exists($file)) {
        echo "📁 Found: " . basename($file) . "<br>";
        $content = file_get_contents($file);
        
        // Look for PostgreSQL config
        if (preg_match('/pgsql|postgresql/i', $content)) {
            echo "⚠️ Contains PostgreSQL configuration<br>";
            
            // Extract connection details (safely)
            if (preg_match('/host[\'"]?\s*=>\s*[\'"]([^\'"]+)/', $content, $matches)) {
                echo "Host: " . $matches[1] . "<br>";
            }
            if (preg_match('/port[\'"]?\s*=>\s*[\'"]?(\d+)/', $content, $matches)) {
                echo "Port: " . $matches[1] . "<br>";
            }
            if (preg_match('/dbname[\'"]?\s*=>\s*[\'"]([^\'"]+)/', $content, $matches)) {
                echo "Database: " . $matches[1] . "<br>";
            }
        }
    }
}

// 4. Test basic PostgreSQL connection
echo "<h4>4. Connection Test</h4>";
try {
    // Try to create a basic PostgreSQL connection
    $testDsn = "pgsql:host=localhost;port=5432;dbname=test";
    $testPdo = new PDO($testDsn, 'test', 'test');
    echo "✅ PDO PostgreSQL connection class works<br>";
} catch (Exception $e) {
    echo "⚠️ PDO connection test failed (expected): " . $e->getMessage() . "<br>";
}

// 5. Check for sync files
echo "<h4>5. Sync Files</h4>";
$syncFiles = [
    __DIR__ . '/app/helpers/PostgreSQLSync.php',
    __DIR__ . '/app/cron/postgresql_sync.php',
    __DIR__ . '/sync/postgresql.php'
];

foreach ($syncFiles as $file) {
    if (file_exists($file)) {
        echo "📁 Found sync file: " . basename($file) . "<br>";
        $content = file_get_contents($file);
        
        // Check for connection string
        if (preg_match('/new PDO\([\'"]([^\'"]+)/', $content, $matches)) {
            echo "Connection string: " . $matches[1] . "<br>";
        }
    }
}

echo "<br><strong>Next: Check the actual PostgreSQL connection details in your config files</strong>";
?>