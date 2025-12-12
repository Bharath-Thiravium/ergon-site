<?php
require_once __DIR__ . '/app/config/database.php';

try {
    $db = Database::connect();
    
    // Check attendance table structure
    $stmt = $db->prepare("DESCRIBE attendance");
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Attendance table structure:\n";
    foreach ($columns as $column) {
        echo "- {$column['Field']} ({$column['Type']}) {$column['Null']} {$column['Key']}\n";
    }
    
    // Check for existing records
    echo "\nSample attendance records:\n";
    $stmt = $db->prepare("SELECT id, user_id, check_in, check_out, DATE(check_in) as date_only FROM attendance ORDER BY id DESC LIMIT 5");
    $stmt->execute();
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($records as $record) {
        echo "ID: {$record['id']}, User: {$record['user_id']}, Date: {$record['date_only']}, Check-in: {$record['check_in']}\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>