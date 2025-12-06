<?php
require_once __DIR__ . '/app/config/database.php';

try {
    $db = Database::connect();
    
    $db->exec("ALTER TABLE activity_logs ADD COLUMN `action` varchar(100) DEFAULT NULL AFTER `user_id`");
    $db->exec("ALTER TABLE activity_logs ADD COLUMN `details` text DEFAULT NULL AFTER `action`");
    
    echo "Successfully added 'action' and 'details' columns to activity_logs table\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
