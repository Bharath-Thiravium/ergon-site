<?php
require_once __DIR__ . '/app/config/database.php';

echo "<h3>PostgreSQL Connection Test</h3>";

try {
    $config = Database::getPostgreSQLConfig();
    $pg = $config['postgresql'];
    
    echo "Attempting connection to:<br>";
    echo "Host: {$pg['host']}<br>";
    echo "Port: {$pg['port']}<br>";
    echo "Database: {$pg['database']}<br>";
    echo "Username: {$pg['username']}<br>";
    echo "<br>";
    
    $pdo = new PDO(
        "pgsql:host={$pg['host']};port={$pg['port']};dbname={$pg['database']}",
        $pg['username'],
        $pg['password'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_TIMEOUT => 30
        ]
    );
    
    echo "✅ PostgreSQL connection successful!<br>";
    
    // Test a simple query
    $stmt = $pdo->query("SELECT version()");
    $version = $stmt->fetchColumn();
    echo "PostgreSQL version: " . $version . "<br>";
    
    // Test table access
    $stmt = $pdo->query("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public' LIMIT 5");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Available tables: " . implode(', ', $tables) . "<br>";
    
} catch (PDOException $e) {
    echo "❌ PostgreSQL connection failed: " . $e->getMessage() . "<br>";
    echo "Please check your credentials and network connectivity.<br>";
}
?>