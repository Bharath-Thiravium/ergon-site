<?php
require_once __DIR__ . '/app/config/database.php';

try {
    $db = Database::connect();
    
    // Find all users with Raj in name
    echo "=== Users with 'Raj' in name ===\n";
    $stmt = $db->prepare("SELECT id, name, role, status FROM users WHERE name LIKE '%Raj%'");
    $stmt->execute();
    $users = $stmt->fetchAll();
    
    foreach ($users as $user) {
        echo "ID: {$user['id']}, Name: {$user['name']}, Role: {$user['role']}, Status: {$user['status']}\n";
    }
    
    // Find all users with Nelson in name (case insensitive)
    echo "\n=== All users with 'Nelson' (case insensitive) ===\n";
    $stmt = $db->prepare("SELECT id, name, role, status FROM users WHERE LOWER(name) LIKE LOWER('%Nelson%')");
    $stmt->execute();
    $users = $stmt->fetchAll();
    
    foreach ($users as $user) {
        echo "ID: {$user['id']}, Name: {$user['name']}, Role: {$user['role']}, Status: {$user['status']}\n";
        
        // Check if this user has attendance today
        $stmt2 = $db->prepare("SELECT * FROM attendance WHERE user_id = ? AND DATE(check_in) = CURDATE()");
        $stmt2->execute([$user['id']]);
        $attendance = $stmt2->fetch();
        
        if ($attendance) {
            echo "  -> HAS ATTENDANCE: Check-in: {$attendance['check_in']}, Location: " . ($attendance['location_display'] ?: 'NULL') . "\n";
            
            // Update this record with proper location data
            $stmt3 = $db->prepare("UPDATE attendance SET location_display = 'ERGON Company', project_name = '----' WHERE id = ?");
            if ($stmt3->execute([$attendance['id']])) {
                echo "  -> ✅ UPDATED with location data\n";
            }
        } else {
            echo "  -> No attendance today\n";
        }
    }
    
    // Also check recent attendance records (last 7 days)
    echo "\n=== Recent attendance records (last 7 days) ===\n";
    $stmt = $db->prepare("
        SELECT a.id, a.user_id, u.name, a.check_in, a.location_display, a.project_name
        FROM attendance a 
        JOIN users u ON a.user_id = u.id 
        WHERE a.check_in >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
        ORDER BY a.check_in DESC
    ");
    $stmt->execute();
    $records = $stmt->fetchAll();
    
    foreach ($records as $record) {
        echo "User: {$record['name']}, Check-in: {$record['check_in']}, Location: " . ($record['location_display'] ?: 'NULL') . ", Project: " . ($record['project_name'] ?: 'NULL') . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>