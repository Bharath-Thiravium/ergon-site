<?php

class PostgreSQLSyncService {
    private $pgPdo;
    private $mysqlPdo;
    
    public function __construct($mysqlPdo) {
        $this->mysqlPdo = $mysqlPdo;
        
        // PostgreSQL connection - production SAP database
        $pgDsn = "pgsql:host=72.60.218.167;port=5432;dbname=modernsap";
        $this->pgPdo = new PDO($pgDsn, 'postgres', 'mango');
    }
    
    public function syncAll() {
        try {
            $invoiceCount = $this->syncInvoices();
            $poCount = $this->syncPurchaseOrders();
            $customerCount = $this->syncCustomers();
            $shippingResult = $this->syncCustomerShippingAddress();
            $shippingCount = $shippingResult['synced'];
            
            return [
                'success' => true, 
                'message' => "Synced {$invoiceCount} invoices, {$poCount} POs, {$customerCount} customers, {$shippingCount} shipping addresses from PostgreSQL"
            ];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    private function syncInvoices() {
        $pgSql = "SELECT invoice_number, customer_id, total_amount, taxable_amount, amount_paid, igst_amount, cgst_amount, sgst_amount, due_date, invoice_date, status FROM invoices";
        $pgStmt = $this->pgPdo->query($pgSql);
        
        $mysqlSql = "INSERT INTO finance_invoices (invoice_number, customer_id, total_amount, taxable_amount, amount_paid, igst_amount, cgst_amount, sgst_amount, due_date, invoice_date, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE total_amount=VALUES(total_amount), amount_paid=VALUES(amount_paid)";
        $mysqlStmt = $this->mysqlPdo->prepare($mysqlSql);
        
        $count = 0;
        while ($row = $pgStmt->fetch(PDO::FETCH_ASSOC)) {
            $mysqlStmt->execute(array_values($row));
            $count++;
        }
        return $count;
    }
    
    private function syncPurchaseOrders() {
        $pgSql = "SELECT po_number, customer_id, po_total_value, po_date, po_status FROM purchase_orders";
        $pgStmt = $this->pgPdo->query($pgSql);
        
        $mysqlSql = "INSERT INTO finance_purchase_orders (po_number, customer_id, po_total_value, po_date, po_status) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE po_total_value=VALUES(po_total_value), po_status=VALUES(po_status)";
        $mysqlStmt = $this->mysqlPdo->prepare($mysqlSql);
        
        $count = 0;
        while ($row = $pgStmt->fetch(PDO::FETCH_ASSOC)) {
            $mysqlStmt->execute(array_values($row));
            $count++;
        }
        return $count;
    }
    
    private function syncCustomers() {
        $pgSql = "SELECT customer_id, customer_name, customer_gstin FROM customers";
        $pgStmt = $this->pgPdo->query($pgSql);
        
        $mysqlSql = "INSERT INTO finance_customers (customer_id, customer_name, customer_gstin) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE customer_name=VALUES(customer_name)";
        $mysqlStmt = $this->mysqlPdo->prepare($mysqlSql);
        
        $count = 0;
        while ($row = $pgStmt->fetch(PDO::FETCH_ASSOC)) {
            $mysqlStmt->execute(array_values($row));
            $count++;
        }
        return $count;
    }
    
    public function syncCustomerShippingAddress() {
        $pgSql = "SELECT id, label, address_line1, address_line2, city, state, pincode, country, is_default, created_at, updated_at, customer_id FROM finance_customershippingaddress";
        $pgStmt = $this->pgPdo->query($pgSql);
        
        $mysqlSql = "INSERT INTO finance_customershippingaddress (id, label, address_line1, address_line2, city, state, pincode, country, is_default, created_at, updated_at, customer_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE label=VALUES(label), address_line1=VALUES(address_line1), address_line2=VALUES(address_line2), city=VALUES(city), state=VALUES(state), pincode=VALUES(pincode), country=VALUES(country), is_default=VALUES(is_default), updated_at=VALUES(updated_at)";
        $mysqlStmt = $this->mysqlPdo->prepare($mysqlSql);
        
        $count = 0;
        while ($row = $pgStmt->fetch(PDO::FETCH_ASSOC)) {
            $row['is_default'] = $row['is_default'] ? 1 : 0;
            $row['created_at'] = substr($row['created_at'], 0, 19);
            $row['updated_at'] = substr($row['updated_at'], 0, 19);
            $mysqlStmt->execute(array_values($row));
            $count++;
        }
        return ['success' => true, 'synced' => $count, 'skipped' => 0];
    }
}
