<?php
require_once __DIR__ . '/app/config/database.php';
require_once __DIR__ . '/app/helpers/NotificationHelper.php';

try {
    $db = Database::connect();
    
    // Get a test user
    $stmt = $db->query("SELECT id, name FROM users LIMIT 1");
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        echo "❌ No users found in database\n";
        exit;
    }
    
    echo "✅ Testing notification system with user: {$user['name']} (ID: {$user['id']})\n\n";
    
    // Test 1: Leave Request Notification
    echo "🔄 Testing Leave Request Notification...\n";
    $result = NotificationHelper::notifyLeaveRequest(1, $user['id'], 'user');
    echo $result ? "✅ Leave request notification created\n" : "❌ Leave request notification failed\n";
    
    // Test 2: Expense Claim Notification
    echo "🔄 Testing Expense Claim Notification...\n";
    $result = NotificationHelper::notifyExpenseClaim($user['id'], $user['name'], 500.00, 1);
    echo $result ? "✅ Expense claim notification created\n" : "❌ Expense claim notification failed\n";
    
    // Test 3: Advance Request Notification
    echo "🔄 Testing Advance Request Notification...\n";
    $result = NotificationHelper::notifyAdvanceRequest(1, $user['id']);
    echo $result ? "✅ Advance request notification created\n" : "❌ Advance request notification failed\n";
    
    // Test 4: Check currency format in notifications
    echo "\n🔄 Checking currency format in recent notifications...\n";
    $stmt = $db->prepare("SELECT message FROM notifications WHERE message LIKE '%₹%' ORDER BY created_at DESC LIMIT 3");
    $stmt->execute();
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($notifications) > 0) {
        echo "✅ Found notifications with Indian Rupee (₹) format:\n";
        foreach ($notifications as $notif) {
            echo "   - " . $notif['message'] . "\n";
        }
    } else {
        echo "⚠️  No notifications found with ₹ format yet\n";
    }
    
    // Test 5: Check for duplicate notifications
    echo "\n🔄 Checking for duplicate notifications...\n";
    $stmt = $db->query("SELECT message, COUNT(*) as count FROM notifications GROUP BY message HAVING count > 1");
    $duplicates = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($duplicates) > 0) {
        echo "⚠️  Found duplicate notifications:\n";
        foreach ($duplicates as $dup) {
            echo "   - '{$dup['message']}' appears {$dup['count']} times\n";
        }
    } else {
        echo "✅ No duplicate notifications found\n";
    }
    
    echo "\n🎉 Notification system test completed!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
