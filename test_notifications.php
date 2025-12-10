<?php
// Simple notification test script
require_once __DIR__ . '/app/core/Session.php';
require_once __DIR__ . '/app/config/database.php';
require_once __DIR__ . '/app/models/Notification.php';

Session::init();

echo "<h2>Notification System Test</h2>";

try {
    // Test database connection
    $db = Database::connect();
    echo "✅ Database connection: OK<br>";
    
    // Test notification model
    $notification = new Notification();
    echo "✅ Notification model: OK<br>";
    
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        echo "❌ User not logged in. Please login first.<br>";
        echo "<a href='/ergon-site/login'>Login</a><br>";
        exit;
    }
    
    $userId = $_SESSION['user_id'];
    echo "✅ User ID: $userId<br>";
    
    // Test creating a notification
    $result = $notification->create([
        'sender_id' => 1,
        'receiver_id' => $userId,
        'type' => 'info',
        'category' => 'system',
        'title' => 'Test Notification',
        'message' => 'This is a test notification created at ' . date('Y-m-d H:i:s'),
        'reference_type' => 'test',
        'reference_id' => 1
    ]);
    
    if ($result) {
        echo "✅ Test notification created successfully<br>";
    } else {
        echo "❌ Failed to create test notification<br>";
    }
    
    // Test getting notifications
    $notifications = $notification->getForUser($userId, 5);
    echo "✅ Found " . count($notifications) . " notifications<br>";
    
    // Test getting unread count
    $unreadCount = $notification->getUnreadCount($userId);
    echo "✅ Unread count: $unreadCount<br>";
    
    // Display recent notifications
    if (!empty($notifications)) {
        echo "<h3>Recent Notifications:</h3>";
        foreach ($notifications as $notif) {
            echo "<div style='border: 1px solid #ccc; padding: 10px; margin: 5px;'>";
            echo "<strong>" . htmlspecialchars($notif['title']) . "</strong><br>";
            echo htmlspecialchars($notif['message']) . "<br>";
            echo "<small>Created: " . $notif['created_at'] . " | Read: " . ($notif['is_read'] ? 'Yes' : 'No') . "</small>";
            echo "</div>";
        }
    }
    
    // Test API endpoint
    echo "<h3>Testing API Endpoint:</h3>";
    $apiUrl = '/ergon-site/api/notifications.php';
    echo "<a href='$apiUrl' target='_blank'>Test API directly</a><br>";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
    echo "Stack trace: " . $e->getTraceAsString() . "<br>";
}

echo "<br><a href='/ergon-site/dashboard'>Back to Dashboard</a>";
?>