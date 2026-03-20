<?php
// Fallback for Hostinger plans without PostgreSQL support

// Check if PostgreSQL is available
if (!extension_loaded('pdo_pgsql')) {
    echo "PostgreSQL extensions not available on this hosting plan.<br>";
    echo "Disabling PostgreSQL sync...<br>";
    
    // Create a simple sync service that only logs the attempt
    $fallbackService = '<?php
class DataSyncService {
    public function syncAllTables() {
        error_log("PostgreSQL sync attempted but extensions not available");
        return [
            "error" => "PostgreSQL extensions not available on this hosting plan",
            "customers" => ["table" => "customers", "records" => 0, "status" => "skipped"],
            "quotations" => ["table" => "quotations", "records" => 0, "status" => "skipped"],
            "purchase_orders" => ["table" => "purchase_orders", "records" => 0, "status" => "skipped"],
            "invoices" => ["table" => "invoices", "records" => 0, "status" => "skipped"],
            "payments" => ["table" => "payments", "records" => 0, "status" => "skipped"]
        ];
    }
    
    public function isPostgreSQLAvailable(): bool {
        return false;
    }
}';
    
    // Backup original and create fallback
    if (file_exists(__DIR__ . '/app/services/DataSyncService.php')) {
        copy(__DIR__ . '/app/services/DataSyncService.php', __DIR__ . '/app/services/DataSyncService.php.backup');
        file_put_contents(__DIR__ . '/app/services/DataSyncService.php', $fallbackService);
        echo "✅ Created fallback DataSyncService<br>";
    }
    
    echo "✅ System will now work without PostgreSQL sync<br>";
} else {
    echo "✅ PostgreSQL extensions are available!<br>";
}
?>