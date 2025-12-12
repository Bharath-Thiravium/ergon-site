<?php
require_once __DIR__ . '/app/config/database.php';

try {
    $db = Database::connect();
    
    echo "Fixing attendance table...\n";
    
    // Clean up invalid datetime values
    $db->exec("UPDATE attendance SET check_out = NULL WHERE check_out = '' OR check_out = '0000-00-00 00:00:00'");
    echo "✓ Cleaned invalid check_out values\n";
    
    // Check current structure
    $stmt = $db->query("DESCRIBE attendance");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "\nCurrent table structure:\n";
    foreach ($columns as $column) {
        echo "- {$column['Field']}: {$column['Type']}\n";
    }
    
    echo "\nTable fixed successfully!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>