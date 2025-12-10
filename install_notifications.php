<?php
// Notification system installation script
require_once __DIR__ . '/app/config/database.php';

echo "<h2>Installing Notification System</h2>";

try {
    $db = Database::connect();
    echo "✅ Database connected<br>";
    
    // Create notifications table
    $sql = "CREATE TABLE IF NOT EXISTS notifications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        sender_id INT NOT NULL,
        receiver_id INT NOT NULL,
        type ENUM('info', 'success', 'warning', 'error', 'urgent') DEFAULT 'info',
        category ENUM('task', 'approval', 'system', 'reminder', 'announcement') DEFAULT 'system',
        title VARCHAR(255) NOT NULL,
        message TEXT NOT NULL,
        action_url VARCHAR(500) DEFAULT NULL,
        action_text VARCHAR(100) DEFAULT NULL,
        reference_type VARCHAR(50) DEFAULT NULL,
        reference_id INT DEFAULT NULL,
        metadata JSON DEFAULT NULL,
        priority TINYINT(1) DEFAULT 1,
        is_read BOOLEAN DEFAULT FALSE,
        read_at TIMESTAMP NULL DEFAULT NULL,
        expires_at TIMESTAMP NULL DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        
        INDEX idx_receiver_unread (receiver_id, is_read, created_at),
        INDEX idx_receiver_priority (receiver_id, priority, created_at),
        INDEX idx_category_type (category, type),
        INDEX idx_reference (reference_type, reference_id),
        INDEX idx_expires (expires_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $db->exec($sql);
    echo "✅ Notifications table created/verified<br>";
    
    // Check if table exists and has data
    $stmt = $db->query("SELECT COUNT(*) as count FROM notifications");
    $result = $stmt->fetch();
    echo "✅ Current notifications in database: " . $result['count'] . "<br>";
    
    // Create a test notification for all users (using basic columns)
    $stmt = $db->query("SELECT id FROM users WHERE status = 'active' LIMIT 5");
    $users = $stmt->fetchAll();
    
    foreach ($users as $user) {
        try {
            $stmt = $db->prepare("INSERT INTO notifications (sender_id, receiver_id, title, message) VALUES (?, ?, ?, ?)");
            $stmt->execute([
                1, // sender_id
                $user['id'], // receiver_id
                'System Notification Test',
                'This is a test notification to verify the system is working properly. Created at ' . date('Y-m-d H:i:s')
            ]);
        } catch (Exception $e) {
            echo "⚠️ Could not create test notification for user " . $user['id'] . ": " . $e->getMessage() . "<br>";
        }
    }
    
    echo "✅ Attempted to create test notifications for " . count($users) . " users<br>";
    
    // Test the notification model
    require_once __DIR__ . '/app/models/Notification.php';
    $notification = new Notification();
    echo "✅ Notification model loaded successfully<br>";
    
    echo "<br><strong>Installation Complete!</strong><br>";
    echo "<a href='/ergon-site/test_notifications.php'>Test Notifications</a><br>";
    echo "<a href='/ergon-site/dashboard'>Back to Dashboard</a>";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
    echo "Stack trace: " . $e->getTraceAsString() . "<br>";
}
?>