<?php
require_once __DIR__ . '/app/config/database.php';

try {
    $mysql_db = Database::connect();
    
    // Check the actual structure and data
    echo "=== FINANCE_INVOICES STRUCTURE ===\n";
    $result = $mysql_db->query("DESCRIBE finance_invoices");
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        echo "- {$row['Field']} ({$row['Type']})\n";
    }
    
    echo "\n=== FINANCE_CUSTOMERSHIPPINGADDRESS STRUCTURE ===\n";
    $result = $mysql_db->query("DESCRIBE finance_customershippingaddress");
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        echo "- {$row['Field']} ({$row['Type']})\n";
    }
    
    echo "\n=== SAMPLE INVOICE DATA ===\n";
    $result = $mysql_db->query("SELECT invoice_number, customer_id FROM finance_invoices LIMIT 3");
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        echo "Invoice: {$row['invoice_number']}, Customer ID: {$row['customer_id']}\n";
    }
    
    echo "\n=== SAMPLE SHIPPING ADDRESS DATA ===\n";
    $result = $mysql_db->query("SELECT customer_id, label FROM finance_customershippingaddress LIMIT 3");
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        echo "Customer ID: {$row['customer_id']}, Label: {$row['label']}\n";
    }
    
    echo "\n=== TEST JOIN QUERY ===\n";
    $result = $mysql_db->query("SELECT i.invoice_number, i.customer_id, s.label FROM finance_invoices i LEFT JOIN finance_customershippingaddress s ON i.customer_id = s.customer_id WHERE i.invoice_number LIKE 'BKGE%' LIMIT 3");
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        echo "Invoice: {$row['invoice_number']}, Customer: {$row['customer_id']}, Shipping: " . ($row['label'] ?: 'NULL') . "\n";
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
?>