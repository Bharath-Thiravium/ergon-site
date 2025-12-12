<?php
require_once __DIR__ . '/app/config/database.php';

try {
    $db = Database::connect();
    
    // Check if attendance_logs table exists
    $stmt = $db->prepare("SHOW TABLES LIKE 'attendance_logs'");
    $stmt->execute();
    $tableExists = $stmt->fetch();
    
    if (!$tableExists) {
        echo "❌ attendance_logs table does not exist!\n";
        echo "Creating attendance_logs table...\n";
        
        // Create the table
        $createTableSQL = "CREATE TABLE IF NOT EXISTS `attendance_logs` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `user_id` int(11) NOT NULL,
            `action` varchar(50) NOT NULL,
            `details` text DEFAULT NULL,
            `created_by` int(11) DEFAULT NULL,
            `created_at` timestamp NULL DEFAULT current_timestamp(),
            PRIMARY KEY (`id`),
            KEY `idx_user_id` (`user_id`),
            KEY `idx_action` (`action`),
            KEY `idx_created_at` (`created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $db->exec($createTableSQL);
        echo "✅ attendance_logs table created successfully!\n";
    } else {
        echo "✅ attendance_logs table exists\n";
        
        // Check table structure
        $stmt = $db->prepare("DESCRIBE attendance_logs");
        $stmt->execute();
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "\nTable structure:\n";
        foreach ($columns as $column) {
            echo "- {$column['Field']} ({$column['Type']})\n";
        }
        
        // Check if 'action' column exists
        $hasActionColumn = false;
        foreach ($columns as $column) {
            if ($column['Field'] === 'action') {
                $hasActionColumn = true;
                break;
            }
        }
        
        if (!$hasActionColumn) {
            echo "❌ 'action' column is missing!\n";
            echo "Adding 'action' column...\n";
            $db->exec("ALTER TABLE attendance_logs ADD COLUMN action varchar(50) NOT NULL AFTER user_id");
            echo "✅ 'action' column added successfully!\n";
        } else {
            echo "✅ 'action' column exists\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>