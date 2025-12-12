<?php
require_once __DIR__ . '/app/config/database.php';

try {
    $db = Database::connect();
    
    echo "Simple fix for attendance table...\n";
    
    // Disable foreign key checks temporarily
    $db->exec("SET FOREIGN_KEY_CHECKS = 0");
    
    // Update empty strings to NULL
    $db->exec("UPDATE attendance SET check_out = NULL WHERE check_out = ''");
    echo "✓ Fixed empty check_out values\n";
    
    $db->exec("UPDATE attendance SET check_out = NULL WHERE check_out = '0000-00-00 00:00:00'");
    echo "✓ Fixed zero datetime check_out values\n";
    
    // Re-enable foreign key checks
    $db->exec("SET FOREIGN_KEY_CHECKS = 1");
    
    echo "Attendance table fixed successfully!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    // Re-enable foreign key checks even on error
    try {
        $db->exec("SET FOREIGN_KEY_CHECKS = 1");
    } catch (Exception $e2) {}
}
?>