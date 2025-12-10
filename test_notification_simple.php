<?php
// Use same session handling as main app
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/app/config/database.php';
require_once __DIR__ . '/app/models/Notification.php';

echo "<h2>Session Debug Info:</h2>";
echo "Session ID: " . session_id() . "<br>";
echo "User ID in session: " . ($_SESSION['user_id'] ?? 'NOT SET') . "<br>";
echo "User name: " . ($_SESSION['user_name'] ?? 'NOT SET') . "<br><br>";

if (!isset($_SESSION['user_id'])) {
    echo "Please login first: <a href='/ergon-site/login'>Login</a><br>";
    echo "<a href='/ergon-site/debug_session.php'>Debug Session</a>";
    exit;
}

$userId = $_SESSION['user_id'];
echo "<h2>Simple Notification Test</h2>";

try {
    $notification = new Notification();
    
    // Get existing notifications
    $notifications = $notification->getForDropdown($userId, 5);
    $unreadCount = $notification->getUnreadCount($userId);
    
    echo "✅ User ID: $userId<br>";
    echo "✅ Total notifications: " . count($notifications) . "<br>";
    echo "✅ Unread count: $unreadCount<br>";
    
    if (!empty($notifications)) {
        echo "<h3>Recent Notifications:</h3>";
        foreach ($notifications as $notif) {
            echo "<div style='border: 1px solid #ccc; padding: 10px; margin: 5px; background: " . ($notif['is_read'] ? '#f9f9f9' : '#fff3cd') . ";'>";
            echo "<strong>" . htmlspecialchars($notif['message']) . "</strong><br>";
            echo "<small>From: " . htmlspecialchars($notif['sender_name']) . " | ";
            echo "Created: " . $notif['created_at'] . " | ";
            echo "Status: " . ($notif['is_read'] ? 'Read' : 'Unread') . "</small>";
            echo "</div>";
        }
    }
    
    // Test API response
    echo "<h3>API Test:</h3>";
    echo "<button onclick='testAPI()'>Test Notification API</button>";
    echo "<div id='apiResult'></div>";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>

<script>
function testAPI() {
    fetch('/ergon-site/api/notifications.php')
        .then(response => response.json())
        .then(data => {
            document.getElementById('apiResult').innerHTML = 
                '<pre>' + JSON.stringify(data, null, 2) + '</pre>';
        })
        .catch(error => {
            document.getElementById('apiResult').innerHTML = 
                '<div style="color: red;">Error: ' + error + '</div>';
        });
}
</script>

<br><a href='/ergon-site/dashboard'>Back to Dashboard</a>