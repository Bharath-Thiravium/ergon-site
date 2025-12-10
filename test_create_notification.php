<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    echo "Please login first: <a href='/ergon-site/login'>Login</a>";
    exit;
}

require_once __DIR__ . '/app/config/database.php';
require_once __DIR__ . '/app/models/Notification.php';
require_once __DIR__ . '/app/helpers/NotificationHelper.php';

echo "<h2>Test Notification Creation</h2>";

try {
    $userId = $_SESSION['user_id'];
    $db = Database::connect();
    
    // Get admin/owner users
    $stmt = $db->prepare("SELECT id, name, role FROM users WHERE role IN ('admin', 'owner') AND status = 'active'");
    $stmt->execute();
    $approvers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>Found approvers:</h3>";
    foreach ($approvers as $approver) {
        echo "- " . $approver['name'] . " (" . $approver['role'] . ")<br>";
    }
    
    // Test direct notification creation
    echo "<h3>Testing direct notification creation:</h3>";
    $notification = new Notification();
    
    foreach ($approvers as $approver) {
        $result = $notification->create([
            'sender_id' => $userId,
            'receiver_id' => $approver['id'],
            'type' => 'info',
            'category' => 'approval',
            'title' => 'Test Leave Request',
            'message' => 'Test leave request from ' . $_SESSION['user_name'] . ' for testing notification system',
            'reference_type' => 'leave',
            'reference_id' => 999,
            'action_url' => '/ergon-site/leaves'
        ]);
        
        if ($result) {
            echo "✅ Notification created for " . $approver['name'] . "<br>";
        } else {
            echo "❌ Failed to create notification for " . $approver['name'] . "<br>";
        }
    }
    
    // Test helper function
    echo "<h3>Testing NotificationHelper:</h3>";
    $helperResult = NotificationHelper::notifyExpenseClaim($userId, $_SESSION['user_name'], 500.00, 999);
    echo $helperResult ? "✅ Helper function worked" : "❌ Helper function failed";
    echo "<br>";
    
    // Check recent notifications
    echo "<h3>Recent notifications in database:</h3>";
    $stmt = $db->query("SELECT * FROM notifications ORDER BY created_at DESC LIMIT 5");
    $recent = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($recent as $notif) {
        echo "- ID: " . $notif['id'] . " | To: " . $notif['receiver_id'] . " | Message: " . substr($notif['message'], 0, 50) . "...<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}

echo "<br><a href='/ergon-site/dashboard'>Back to Dashboard</a>";
?>