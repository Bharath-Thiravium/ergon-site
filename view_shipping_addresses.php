<?php
require_once __DIR__ . '/app/config/database.php';

try {
    $mysql_db = Database::connect();
    
    $result = $mysql_db->query("SELECT customer_id, label, address_line1, address_line2, city, state, pincode, country FROM finance_customershippingaddress LIMIT 10");
    
    echo "Site Addresses (Label column):\n";
    echo str_repeat("-", 80) . "\n";
    
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        echo "Customer: {$row['customer_id']}\n";
        echo "Site: {$row['label']}\n";
        echo "Address: {$row['address_line1']}\n";
        if ($row['address_line2']) echo "         {$row['address_line2']}\n";
        echo "Location: {$row['city']}, {$row['state']} - {$row['pincode']}\n";
        echo "Country: {$row['country']}\n";
        echo str_repeat("-", 80) . "\n";
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
?>