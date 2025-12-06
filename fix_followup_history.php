<?php
require_once __DIR__ . '/app/config/database.php';

try {
    $db = Database::connect();
    
    $db->exec("ALTER TABLE followup_history ADD COLUMN `action_type` varchar(50) DEFAULT NULL AFTER `followup_id`");
    $db->exec("ALTER TABLE followup_history ADD COLUMN `old_date` date DEFAULT NULL AFTER `action_type`");
    $db->exec("ALTER TABLE followup_history ADD COLUMN `new_date` date DEFAULT NULL AFTER `old_date`");
    
    echo "Successfully added columns to followup_history table\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
