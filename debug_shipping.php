<?php
require_once __DIR__ . '/app/config/database.php';

try {
    $db = Database::connect();
    
    // Check shipping addresses count
    $stmt = $db->query("SELECT COUNT(*) as count FROM finance_customershippingaddress");
    $count = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Total shipping addresses: " . $count['count'] . "\n";
    
    // Check sample shipping addresses
    $stmt = $db->query("SELECT customer_id, label FROM finance_customershippingaddress LIMIT 5");
    $addresses = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Sample addresses:\n";
    foreach ($addresses as $addr) {
        echo "Customer ID: {$addr['customer_id']}, Label: {$addr['label']}\n";
    }
    
    // Check customer IDs from invoices
    $stmt = $db->query("SELECT DISTINCT customer_id FROM finance_invoices WHERE invoice_number LIKE 'BKGE%' LIMIT 5");
    $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "\nCustomer IDs from invoices:\n";
    foreach ($customers as $customer) {
        echo "Customer ID: {$customer['customer_id']}\n";
    }
    
    // Check if customer IDs match
    $stmt = $db->query("
        SELECT i.customer_id, i.invoice_number,
               (SELECT label FROM finance_customershippingaddress WHERE customer_id = i.customer_id LIMIT 1) as shipping
        FROM finance_invoices i 
        WHERE invoice_number LIKE 'BKGE%' 
        LIMIT 3
    ");
    $matches = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "\nInvoice to shipping match test:\n";
    foreach ($matches as $match) {
        echo "Invoice: {$match['invoice_number']}, Customer: {$match['customer_id']}, Shipping: " . ($match['shipping'] ?: 'NULL') . "\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>