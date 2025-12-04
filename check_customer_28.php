<?php
require_once __DIR__ . '/app/config/database.php';

try {
    $mysql_db = Database::connect();
    
    echo "=== CUSTOMER 28 SHIPPING ADDRESSES ===\n";
    $result = $mysql_db->query("SELECT * FROM finance_customershippingaddress WHERE customer_id = 28");
    $addresses = $result->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($addresses)) {
        echo "No shipping addresses found for customer 28\n";
        
        echo "\n=== ALL CUSTOMERS WITH SHIPPING ADDRESSES ===\n";
        $result = $mysql_db->query("SELECT DISTINCT customer_id, label FROM finance_customershippingaddress ORDER BY customer_id");
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            echo "Customer {$row['customer_id']}: {$row['label']}\n";
        }
        
        echo "\n=== ADDING SAMPLE SHIPPING ADDRESS FOR CUSTOMER 28 ===\n";
        $stmt = $mysql_db->prepare("INSERT INTO finance_customershippingaddress (customer_id, label, address_line1, city, state, pincode, country) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([28, 'Pro-Zeal Green Power Five Pvt. Ltd.', 'Hangalahobali, Mylanahalli Village', 'Chamarajanagar', 'Karnataka', '571111', 'India']);
        
        echo "Added shipping address for customer 28\n";
    } else {
        foreach ($addresses as $addr) {
            echo "Found: {$addr['label']} - {$addr['city']}, {$addr['state']}\n";
        }
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
?>
