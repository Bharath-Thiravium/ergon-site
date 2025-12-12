<?php
require_once __DIR__ . '/app/config/database.php';

try {
    $db = Database::connect();
    
    echo "Cleaning attendance table...\n";
    
    // First, let's see what we have
    $stmt = $db->query("SELECT id, check_in, check_out FROM attendance WHERE check_out = '' OR check_out = '0000-00-00 00:00:00' LIMIT 5");
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Found " . count($records) . " records with invalid check_out values\n";
    
    // Update them one by one
    foreach ($records as $record) {
        $stmt = $db->prepare("UPDATE attendance SET check_out = NULL WHERE id = ?");
        $stmt->execute([$record['id']]);
        echo "✓ Fixed record ID: {$record['id']}\n";
    }
    
    // Clean all remaining
    $stmt = $db->prepare("UPDATE attendance SET check_out = NULL WHERE check_out = '' OR check_out = '0000-00-00 00:00:00'");
    $result = $stmt->execute();
    $affected = $stmt->rowCount();
    
    echo "✓ Cleaned {$affected} records\n";
    echo "Attendance table cleaned successfully!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>