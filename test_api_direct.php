<?php
require_once __DIR__ . '/app/config/database.php';

try {
    $db = Database::connect();
    
    echo "=== TESTING OUTSTANDING API QUERY DIRECTLY ===\n";
    
    $prefix = 'BKGE';
    $len = strlen($prefix);
    
    $sql = "SELECT 
                i.invoice_number,
                i.customer_id,
                COALESCE(c.customer_name, i.customer_id) AS customer_name,
                COALESCE((SELECT label FROM finance_customershippingaddress WHERE customer_id = i.customer_id LIMIT 1), 'N/A') AS shipping_address
            FROM finance_invoices i
            LEFT JOIN finance_customers c ON i.customer_id = c.customer_id
            WHERE LEFT(i.invoice_number, $len) = ?
              AND (i.total_amount - i.paid_amount) > 0
            LIMIT 3";
    
    $stmt = $db->prepare($sql);
    $stmt->execute([$prefix]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($results as $row) {
        echo "Invoice: {$row['invoice_number']}\n";
        echo "Customer ID: {$row['customer_id']}\n";
        echo "Customer Name: {$row['customer_name']}\n";
        echo "Shipping: {$row['shipping_address']}\n";
        
        // Check if this customer has any shipping addresses
        $checkStmt = $db->prepare("SELECT label FROM finance_customershippingaddress WHERE customer_id = ?");
        $checkStmt->execute([$row['customer_id']]);
        $addresses = $checkStmt->fetchAll(PDO::FETCH_ASSOC);
        echo "Available addresses for customer {$row['customer_id']}: " . count($addresses) . "\n";
        foreach ($addresses as $addr) {
            echo "  - {$addr['label']}\n";
        }
        echo "---\n";
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
?>
