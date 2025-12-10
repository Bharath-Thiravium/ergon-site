<?php
// Fix notifications table structure
require_once __DIR__ . '/app/config/database.php';

echo "<h2>Fixing Notifications Table</h2>";

try {
    $db = Database::connect();
    echo "✅ Database connected<br>";
    
    // Check current table structure
    $stmt = $db->query("DESCRIBE notifications");
    $columns = $stmt->fetchAll();
    
    echo "<h3>Current table structure:</h3>";
    foreach ($columns as $col) {
        echo "- " . $col['Field'] . " (" . $col['Type'] . ")<br>";
    }
    
    // Get existing column names
    $existingColumns = array_column($columns, 'Field');
    
    // Add missing columns if they don't exist
    $columnsToAdd = [
        'type' => "ALTER TABLE notifications ADD COLUMN type ENUM('info', 'success', 'warning', 'error', 'urgent') DEFAULT 'info' AFTER receiver_id",
        'category' => "ALTER TABLE notifications ADD COLUMN category ENUM('task', 'approval', 'system', 'reminder', 'announcement') DEFAULT 'system' AFTER type",
        'action_url' => "ALTER TABLE notifications ADD COLUMN action_url VARCHAR(500) DEFAULT NULL AFTER message",
        'action_text' => "ALTER TABLE notifications ADD COLUMN action_text VARCHAR(100) DEFAULT NULL AFTER action_url",
        'reference_type' => "ALTER TABLE notifications ADD COLUMN reference_type VARCHAR(50) DEFAULT NULL AFTER action_text",
        'reference_id' => "ALTER TABLE notifications ADD COLUMN reference_id INT DEFAULT NULL AFTER reference_type",
        'metadata' => "ALTER TABLE notifications ADD COLUMN metadata JSON DEFAULT NULL AFTER reference_id",
        'priority' => "ALTER TABLE notifications ADD COLUMN priority TINYINT(1) DEFAULT 1 AFTER metadata",
        'expires_at' => "ALTER TABLE notifications ADD COLUMN expires_at TIMESTAMP NULL DEFAULT NULL AFTER read_at",
        'updated_at' => "ALTER TABLE notifications ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at"
    ];
    
    echo "<h3>Adding missing columns:</h3>";
    foreach ($columnsToAdd as $column => $sql) {
        if (!in_array($column, $existingColumns)) {
            try {
                $db->exec($sql);
                echo "✅ Added column: $column<br>";
            } catch (Exception $e) {
                echo "⚠️ Column $column: " . $e->getMessage() . "<br>";
            }
        } else {
            echo "✅ Column $column: Already exists<br>";
        }
    }
    
    // Add indexes if they don't exist
    $indexesToAdd = [
        'idx_receiver_unread' => "CREATE INDEX idx_receiver_unread ON notifications (receiver_id, is_read, created_at)",
        'idx_receiver_priority' => "CREATE INDEX idx_receiver_priority ON notifications (receiver_id, priority, created_at)",
        'idx_category_type' => "CREATE INDEX idx_category_type ON notifications (category, type)",
        'idx_reference' => "CREATE INDEX idx_reference ON notifications (reference_type, reference_id)",
        'idx_expires' => "CREATE INDEX idx_expires ON notifications (expires_at)"
    ];
    
    echo "<h3>Adding indexes:</h3>";
    foreach ($indexesToAdd as $indexName => $sql) {
        try {
            $db->exec($sql);
            echo "✅ Added index: $indexName<br>";
        } catch (Exception $e) {
            echo "⚠️ Index $indexName: Already exists or error<br>";
        }
    }
    
    echo "<br><strong>Table structure fixed!</strong><br>";
    echo "<a href='/ergon-site/test_notifications.php'>Test Notifications</a><br>";
    echo "<a href='/ergon-site/dashboard'>Back to Dashboard</a>";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}
?>