<?php
// Check ergon_db database for all tables
try {
    $pdo = new PDO("mysql:host=localhost;dbname=ergon_db", "root", "");
    $stmt = $pdo->query("SHOW TABLES LIKE 'finance_%'");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "ergon_db database finance tables:\n";
    foreach ($tables as $table) {
        echo "- $table\n";
    }
    
    // Check if shipping addresses exist
    if (in_array('finance_customershippingaddress', $tables)) {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM finance_customershippingaddress");
        $count = $stmt->fetch()['count'];
        echo "\nShipping addresses in ergon_db: $count\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
