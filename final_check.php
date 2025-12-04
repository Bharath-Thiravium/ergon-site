<?php
// Final check - test the exact API call the dashboard makes
require_once __DIR__ . '/../ergon-site/app/config/database.php';

try {
    $db = Database::connect();
    
    // Test the exact query from outstanding.php
    $prefix = 'BKGE';
    $limit = 3;
    $len = strlen($prefix);

    $sql = "SELECT 
                i.invoice_number,
                i.customer_id,
                COALESCE(c.display_name, c.name, i.customer_id) AS customer_name,
                COALESCE((SELECT label FROM finance_customershippingaddress WHERE customer_id = i.customer_id ORDER BY label ASC LIMIT 1), 'N/A') AS shipping_address
            FROM finance_invoices i
            LEFT JOIN finance_customer c ON i.customer_id = c.id
            WHERE LEFT(i.invoice_number, $len) = ?
              AND (i.total_amount - i.paid_amount) > 0
            ORDER BY i.invoice_date DESC
            LIMIT ?";

    $stmt = $db->prepare($sql);
    $stmt->execute([$prefix, (int)$limit]);
    $invoices = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "Database: " . (new Database())->getEnvironment() . "\n";
    echo "Results: " . count($invoices) . "\n\n";
    
    foreach ($invoices as $invoice) {
        echo "Invoice: {$invoice['invoice_number']}\n";
        echo "Customer ID: {$invoice['customer_id']}\n";
        echo "Customer Name: {$invoice['customer_name']}\n";
        echo "Shipping: {$invoice['shipping_address']}\n";
        echo "---\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
