<?php
require_once __DIR__ . '/app/config/database.php';

echo "=== SHIPPING ADDRESS DIAGNOSTIC ===\n";
echo "Environment: " . (Environment::isDevelopment() ? 'DEVELOPMENT' : 'PRODUCTION') . "\n";
echo "Host: " . ($_SERVER['HTTP_HOST'] ?? 'unknown') . "\n";
echo "Document Root: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'unknown') . "\n\n";

try {
    $db = Database::connect();
    echo "✅ MySQL Connection: SUCCESS\n";
    
    // Check table existence
    $stmt = $db->query("SHOW TABLES LIKE 'finance_customershippingaddress'");
    if ($stmt->rowCount() > 0) {
        echo "✅ Table exists: finance_customershippingaddress\n";
        
        // Check record count
        $count = $db->query("SELECT COUNT(*) FROM finance_customershippingaddress")->fetchColumn();
        echo "📊 Total shipping addresses: $count\n";
        
        if ($count > 0) {
            // Sample data
            $stmt = $db->query("SELECT customer_id, label, city FROM finance_customershippingaddress LIMIT 3");
            echo "📋 Sample data:\n";
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                echo "   - Customer: {$row['customer_id']}, Label: {$row['label']}, City: {$row['city']}\n";
            }
        } else {
            echo "⚠️  No shipping address data found\n";
        }
    } else {
        echo "❌ Table missing: finance_customershippingaddress\n";
    }
    
    // Test PostgreSQL connectivity
    echo "\n=== POSTGRESQL CONNECTIVITY TEST ===\n";
    try {
        $pg_dsn = 'pgsql:host=72.60.218.167;port=5432;dbname=modernsap';
        $pg = new PDO($pg_dsn, 'postgres', 'mango', [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 5
        ]);
        echo "✅ PostgreSQL Connection: SUCCESS\n";
        
        $pgCount = $pg->query("SELECT COUNT(*) FROM finance_customershippingaddress")->fetchColumn();
        echo "📊 PostgreSQL shipping addresses: $pgCount\n";
        
    } catch (Exception $e) {
        echo "❌ PostgreSQL Connection: FAILED\n";
        echo "Error: " . $e->getMessage() . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ MySQL Connection: FAILED\n";
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n=== RECOMMENDATION ===\n";
if (Environment::isProduction()) {
    echo "Run: php sync_shipping_addresses.php\n";
} else {
    echo "Development environment detected - check production server\n";
}
?>