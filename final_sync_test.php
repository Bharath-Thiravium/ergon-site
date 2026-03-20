<?php
require_once 'app/config/database.php';
require_once 'app/services/DataSyncService.php';

echo "=== Final Sync Test ===\n";

try {
    $syncService = new DataSyncService();
    echo "✅ DataSyncService created\n";
    
    if ($syncService->isPostgreSQLAvailable()) {
        echo "✅ PostgreSQL available\n";
        
        // Test sync operation
        echo "\nTesting sync operation...\n";
        $result = $syncService->syncAllTables();
        
        if ($result) {
            echo "✅ Sync completed successfully\n";
        } else {
            echo "❌ Sync failed\n";
        }
        
    } else {
        echo "❌ PostgreSQL not available\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== Test Complete ===\n";
?>