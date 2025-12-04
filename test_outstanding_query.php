<?php
require_once __DIR__ . '/app/config/database.php';

try {
    $mysql_db = Database::connect();
    
    $prefix = 'BKGE';
    $len = strlen($prefix);
    
    echo "=== TESTING OUTSTANDING QUERY ===\n";
    $sql = "SELECT 
                i.invoice_number,
                i.customer_id,
                COALESCE(c.customer_name, i.customer_id) AS customer_name,
                i.invoice_date,
                i.total_amount,
                (i.total_amount - i.paid_amount) AS outstanding_amount,
                DATEDIFF(CURDATE(), i.due_date) AS days_overdue,
                CASE 
                    WHEN (i.total_amount - i.paid_amount) > 0 
                         AND i.due_date < CURDATE() 
                    THEN 'Overdue'
                    ELSE i.status
                END AS status,
                COALESCE(s.label, 'N/A') AS shipping_address
            FROM finance_invoices i
            LEFT JOIN finance_customers c ON i.customer_id = c.customer_id
            LEFT JOIN finance_customershippingaddress s ON i.customer_id = s.customer_id
            WHERE LEFT(i.invoice_number, $len) = ?
              AND (i.total_amount - i.paid_amount) > 0
            ORDER BY days_overdue DESC, outstanding_amount DESC
            LIMIT 3";
    
    $stmt = $mysql_db->prepare($sql);
    $stmt->execute([$prefix]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($results as $row) {
        echo "Invoice: {$row['invoice_number']}\n";
        echo "Customer ID: {$row['customer_id']}\n";
        echo "Customer Name: {$row['customer_name']}\n";
        echo "Shipping: {$row['shipping_address']}\n";
        echo "---\n";
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
?>