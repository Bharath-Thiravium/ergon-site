<?php
require_once __DIR__ . '/app/config/database.php';

try {
    $db = Database::connect();
    
    $sql = file_get_contents(__DIR__ . '/create_attendance_logs_table.sql');
    
    $db->exec($sql);
    
    echo "Migration completed successfully!\n";
    echo "Tables created:\n";
    echo "- attendance_logs\n";
    echo "- attendance_conflicts\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
