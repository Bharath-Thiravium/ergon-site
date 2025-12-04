<?php
require_once __DIR__ . '/app/config/database.php';

try {
    $mysql_db = Database::connect();
    
    // Create table
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
        UNIQUE KEY unique_customer (customer_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    
    echo "Table finance_customershippingaddress created successfully.\n";
    
    // Insert sample data for testing
    $sampleData = [
        ['CUST001', '123 Main Street, Block A', 'Chennai', 'Tamil Nadu', '600001', 'India'],
        ['CUST002', '456 Park Avenue, Suite 200', 'Mumbai', 'Maharashtra', '400001', 'India'],
        ['CUST003', '789 Business District', 'Bangalore', 'Karnataka', '560001', 'India']
    ];
    
    $stmt = $mysql_db->prepare("INSERT INTO finance_customershippingaddress (customer_id, shipping_address, shipping_city, shipping_state, shipping_pincode, shipping_country) VALUES (?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE shipping_address=VALUES(shipping_address)");
    
    $count = 0;
    foreach ($sampleData as $row) {
        $stmt->execute($row);
        $count++;
    }
    
    echo "Inserted $count sample shipping addresses.\n";
    echo "SUCCESS: finance_customershippingaddress table ready for use.\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
?>