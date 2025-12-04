<?php
require_once __DIR__ . '/app/config/database.php';

try {
    $mysql_db = Database::connect();
    
    // Check table structure
    $result = $mysql_db->query("DESCRIBE finance_customershippingaddress");
    echo "Table structure:\n";
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        echo "- {$row['Field']} ({$row['Type']})\n";
    }
    
    // Check if table has data
    $count = $mysql_db->query("SELECT COUNT(*) FROM finance_customershippingaddress")->fetchColumn();
    echo "\nTotal records: $count\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
?>