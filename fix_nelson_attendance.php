<?php
require_once __DIR__ . '/app/config/database.php';

try {
    $db = Database::connect();
    
    // Find Nelson Raj's attendance record for today
    $stmt = $db->prepare("
        SELECT a.*, u.name 
        FROM attendance a 
        JOIN users u ON a.user_id = u.id 
        WHERE u.name LIKE '%Nelson%' AND DATE(a.check_in) = CURDATE()
    ");
    $stmt->execute();
    $record = $stmt->fetch();
    
    if ($record) {
        echo "Found Nelson's record: ID {$record['id']}\n";
        echo "Current location_display: " . ($record['location_display'] ?: 'NULL') . "\n";
        echo "Current project_name: " . ($record['project_name'] ?: 'NULL') . "\n";
        
        // Update Nelson's record with proper location and project data
        $stmt = $db->prepare("
            UPDATE attendance 
            SET 
                location_display = CASE 
                    WHEN location_name IS NOT NULL AND location_name != '' AND location_name != 'Office' THEN location_name
                    ELSE 'ERGON Company'
                END,
                project_name = '----'
            WHERE id = ?
        ");
        
        if ($stmt->execute([$record['id']])) {
            echo "✅ Updated Nelson's attendance record\n";
        }
    } else {
        echo "No attendance record found for Nelson today\n";
    }
    
    // Update ALL attendance records that are missing location_display or project_name
    $stmt = $db->prepare("
        UPDATE attendance 
        SET 
            location_display = CASE 
                WHEN location_name IS NOT NULL AND location_name != '' AND location_name != 'Office' THEN location_name
                WHEN check_in IS NOT NULL THEN 'ERGON Company'
                ELSE '---'
            END,
            project_name = CASE 
                WHEN check_in IS NOT NULL THEN '----'
                ELSE '----'
            END
        WHERE location_display IS NULL OR location_display = '' OR project_name IS NULL OR project_name = ''
    ");
    
    if ($stmt->execute()) {
        echo "✅ Updated " . $stmt->rowCount() . " attendance records\n";
    }
    
    echo "✅ Fix complete!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>