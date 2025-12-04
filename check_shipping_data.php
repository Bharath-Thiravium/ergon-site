<?php
require_once __DIR__ . '/app/config/database.php';

$db = Database::connect();

echo "Environment: " . (Environment::isDevelopment() ? 'DEV' : 'PROD') . "\n\n";

// Check shipping addresses count
$count = $db->query("SELECT COUNT(*) FROM finance_customershippingaddress")->fetchColumn();
echo "Shipping addresses: $count\n";

// Check customer_id match
$sql = "SELECT 
    (SELECT COUNT(DISTINCT customer_id) FROM finance_invoices WHERE invoice_number LIKE 'BKGE%') as invoice_customers,
    (SELECT COUNT(DISTINCT customer_id) FROM finance_customershippingaddress) as shipping_customers,
    (SELECT COUNT(*) FROM finance_invoices i WHERE EXISTS(SELECT 1 FROM finance_customershippingaddress s WHERE s.customer_id = i.customer_id) AND invoice_number LIKE 'BKGE%') as matched_invoices";

$result = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
echo "Invoice customers: {$result['invoice_customers']}\n";
echo "Shipping customers: {$result['shipping_customers']}\n"; 
echo "Matched invoices: {$result['matched_invoices']}\n";
?>