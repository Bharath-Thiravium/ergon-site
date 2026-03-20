<?php
echo "<h3>Dependency Check</h3>";

// Check if Database class exists
if (!class_exists('Database')) {
    echo "❌ Database class not found, trying to load...<br>";
    try {
        require_once __DIR__ . '/app/config/database.php';
        echo "✅ Database class loaded<br>";
    } catch (Exception $e) {
        echo "❌ Failed to load Database class: " . $e->getMessage() . "<br>";
        exit;
    }
} else {
    echo "✅ Database class already available<br>";
}

// Test Database::connect()
try {
    $mysql = Database::connect();
    echo "✅ MySQL connection works<br>";
} catch (Exception $e) {
    echo "❌ MySQL connection failed: " . $e->getMessage() . "<br>";
}

// Test Database::getPostgreSQLConfig()
try {
    $pgConfig = Database::getPostgreSQLConfig();
    echo "✅ PostgreSQL config loaded<br>";
    echo "Config: " . json_encode($pgConfig) . "<br>";
} catch (Exception $e) {
    echo "❌ PostgreSQL config failed: " . $e->getMessage() . "<br>";
}

// Now try to create DataSyncService with error reporting
echo "<br><h3>Creating DataSyncService Instance</h3>";
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    require_once __DIR__ . '/app/services/DataSyncService.php';
    echo "Attempting to create DataSyncService...<br>";
    
    $syncService = new DataSyncService();
    echo "✅ DataSyncService created successfully<br>";
    
    // Test the isPostgreSQLAvailable method
    $available = $syncService->isPostgreSQLAvailable();
    echo "PostgreSQL available: " . ($available ? 'YES' : 'NO') . "<br>";
    
} catch (Error $e) {
    echo "❌ Fatal Error: " . $e->getMessage() . "<br>";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "<br>";
} catch (Exception $e) {
    echo "❌ Exception: " . $e->getMessage() . "<br>";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "<br>";
}
?>