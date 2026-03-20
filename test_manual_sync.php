<?php
require_once __DIR__ . '/app/services/DataSyncService.php';

echo "<h3>Manual PostgreSQL Sync Test</h3>";

try {
    $syncService = new DataSyncService();
    
    if (!$syncService->isPostgreSQLAvailable()) {
        echo "❌ PostgreSQL not available<br>";
        exit;
    }
    
    echo "✅ PostgreSQL connection available<br>";
    echo "Starting sync...<br><br>";
    
    $results = $syncService->syncAllTables();
    
    foreach ($results as $table => $result) {
        if (isset($result['error'])) {
            echo "❌ $table: " . $result['error'] . "<br>";
        } else {
            $status = $result['status'] ?? 'unknown';
            $records = $result['records'] ?? 0;
            
            if ($status === 'success') {
                echo "✅ $table: $records records synced successfully<br>";
            } elseif ($status === 'no_data') {
                echo "⚠️ $table: No data to sync<br>";
            } else {
                echo "❌ $table: Status $status, $records records<br>";
                if (isset($result['error'])) {
                    echo "   Error: " . $result['error'] . "<br>";
                }
            }
        }
    }
    
    echo "<br>✅ Sync completed!<br>";
    
} catch (Exception $e) {
    echo "❌ Sync failed: " . $e->getMessage() . "<br>";
}
?>