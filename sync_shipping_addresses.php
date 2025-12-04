<?php
require_once __DIR__ . '/app/config/database.php';
require_once __DIR__ . '/src/services/PostgreSQLSyncService.php';

try {
    $mysql_db = Database::connect();
    
    // Create table if not exists
    $mysql_db->exec("CREATE TABLE IF NOT EXISTS finance_customershippingaddress (
        id INT AUTO_INCREMENT PRIMARY KEY,
        customer_id VARCHAR(50) NOT NULL,
        shipping_address TEXT,
        shipping_city VARCHAR(100),
        shipping_state VARCHAR(100),
        shipping_pincode VARCHAR(20),
        shipping_country VARCHAR(100) DEFAULT 'India',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY unique_customer (customer_id),
        INDEX idx_customer_id (customer_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    
    $syncService = new PostgreSQLSyncService($mysql_db);
    $result = $syncService->syncAll();
    
    if ($result['success']) {
        echo "SUCCESS: " . $result['message'] . "\n";
    } else {
        echo "ERROR: " . $result['message'] . "\n";
    }
    
} catch (Exception $e) {
    echo "FATAL ERROR: " . $e->getMessage() . "\n";
}
?>