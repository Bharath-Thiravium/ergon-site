<?php
echo "=== Database Connection Test ===\n\n";

// Set subdomain environment
$_SERVER['HTTP_HOST'] = 'aes.athenas.co.in';
$_SERVER['HTTPS'] = 'on';

try {
    require_once 'app/config/environment.php';
    echo "Environment: " . Environment::detect() . "\n";
    echo "Is Production: " . (Environment::isProduction() ? 'Yes' : 'No') . "\n\n";
    
    require_once 'app/config/database.php';
    echo "Database config loaded\n";
    
    // Test database connection
    $db = Database::connect();
    echo "✅ Database connection successful!\n\n";
    
    // Test a simple query
    $stmt = $db->query("SELECT COUNT(*) as user_count FROM users");
    $result = $stmt->fetch();
    echo "✅ Query test successful - User count: " . $result['user_count'] . "\n\n";
    
    // Test environment variables loading
    echo "Environment Variables:\n";
    echo "DB_HOST: " . ($_ENV['DB_HOST'] ?? 'not set') . "\n";
    echo "DB_NAME: " . ($_ENV['DB_NAME'] ?? 'not set') . "\n";
    echo "DB_USER: " . ($_ENV['DB_USER'] ?? 'not set') . "\n";
    echo "DB_PASS: " . (isset($_ENV['DB_PASS']) ? '[SET]' : 'not set') . "\n";
    
    echo "\n✅ All database tests passed!\n";
    
} catch (Exception $e) {
    echo "❌ Database Error:\n";
    echo "Message: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
?>