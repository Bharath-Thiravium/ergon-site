<?php
echo "<h3>Detailed Sync Diagnostic</h3>";

// Step 1: Check if DataSyncService file exists
if (!file_exists(__DIR__ . '/app/services/DataSyncService.php')) {
    echo "❌ DataSyncService.php not found<br>";
    exit;
}
echo "✅ DataSyncService.php found<br>";

// Step 2: Try to include the service
try {
    require_once __DIR__ . '/app/services/DataSyncService.php';
    echo "✅ DataSyncService loaded<br>";
} catch (Exception $e) {
    echo "❌ Failed to load DataSyncService: " . $e->getMessage() . "<br>";
    exit;
}

// Step 3: Try to create instance
try {
    $syncService = new DataSyncService();
    echo "✅ DataSyncService instance created<br>";
} catch (Exception $e) {
    echo "❌ Failed to create DataSyncService: " . $e->getMessage() . "<br>";
    exit;
}

// Step 4: Check PostgreSQL availability
try {
    $available = $syncService->isPostgreSQLAvailable();
    echo "PostgreSQL available: " . ($available ? 'YES' : 'NO') . "<br>";
    
    if (!$available) {
        echo "❌ PostgreSQL not available, stopping test<br>";
        exit;
    }
} catch (Exception $e) {
    echo "❌ Error checking PostgreSQL availability: " . $e->getMessage() . "<br>";
    exit;
}

// Step 5: Test individual table sync
echo "<br><h4>Testing Individual Table Syncs:</h4>";

$tables = ['customers', 'quotations', 'purchase_orders', 'invoices', 'payments'];

foreach ($tables as $table) {
    echo "Testing $table sync...<br>";
    try {
        $method = 'sync' . ucfirst($table === 'purchase_orders' ? 'PurchaseOrders' : ucfirst($table));
        if (method_exists($syncService, $method)) {
            $result = $syncService->$method();
            echo "✅ $table: " . json_encode($result) . "<br>";
        } else {
            echo "❌ Method $method not found<br>";
        }
    } catch (Exception $e) {
        echo "❌ $table sync failed: " . $e->getMessage() . "<br>";
    }
    echo "<br>";
}

// Step 6: Test full sync
echo "<h4>Testing Full Sync:</h4>";
try {
    $results = $syncService->syncAllTables();
    echo "Full sync results: " . json_encode($results, JSON_PRETTY_PRINT) . "<br>";
} catch (Exception $e) {
    echo "❌ Full sync failed: " . $e->getMessage() . "<br>";
}
?>