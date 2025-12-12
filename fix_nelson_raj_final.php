<?php
require_once __DIR__ . '/app/config/database.php';

try {
    $db = Database::connect();
    
    // Find Nelson Raj user
    $stmt = $db->prepare("SELECT id, name FROM users WHERE name LIKE '%Nelson%Raj%' OR name LIKE '%Raj%'");
    $stmt->execute();
    $users = $stmt->fetchAll();
    
    foreach ($users as $user) {
        echo "Found user: {$user['name']} (ID: {$user['id']})\n";
        
        // Find his attendance record for today
        $stmt2 = $db->prepare("SELECT * FROM attendance WHERE user_id = ? AND DATE(check_in) = CURDATE()");
        $stmt2->execute([$user['id']]);
        $attendance = $stmt2->fetch();
        
        if ($attendance) {
            echo "Found attendance record ID: {$attendance['id']}\n";
            
            // Update with proper location and project data
            $stmt3 = $db->prepare("UPDATE attendance SET location_display = 'ERGON Company', project_name = 'Project Alpha' WHERE id = ?");
            if ($stmt3->execute([$attendance['id']])) {
                echo "✅ Updated Nelson Raj's attendance record\n";
            }
        }
    }
    
    // Also update ANY attendance record that has check_in but NULL location_display
    $stmt = $db->prepare("
        UPDATE attendance 
        SET 
            location_display = 'ERGON Company',
            project_name = 'Project Alpha'
        WHERE check_in IS NOT NULL 
        AND (location_display IS NULL OR location_display = '' OR location_display = '---')
        AND DATE(check_in) = CURDATE()
    ");
    
    if ($stmt->execute()) {
        echo "✅ Updated " . $stmt->rowCount() . " attendance records for today\n";
    }
    
    echo "✅ Fix complete!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>