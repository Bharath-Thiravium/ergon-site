<?php
require_once __DIR__ . '/app/config/database.php';

try {
    $db = Database::connect();
    
    // Update all attendance records to have proper location_display and project_name
    $stmt = $db->prepare("
        UPDATE attendance a 
        LEFT JOIN projects p ON a.project_id = p.id 
        SET 
            a.location_display = CASE 
                WHEN p.location_title IS NOT NULL AND p.location_title != '' THEN p.location_title
                WHEN p.name IS NOT NULL AND p.name != '' THEN CONCAT(p.name, ' Site')
                WHEN a.location_name IS NOT NULL AND a.location_name != '' AND a.location_name != 'Office' THEN a.location_name
                WHEN a.check_in IS NOT NULL THEN 'ERGON Company' 
                ELSE '---' 
            END,
            a.project_name = CASE 
                WHEN p.name IS NOT NULL AND p.name != '' THEN p.name 
                WHEN a.check_in IS NOT NULL THEN '----' 
                ELSE '----' 
            END
    ");
    
    if ($stmt->execute()) {
        echo "✅ Updated " . $stmt->rowCount() . " attendance records with location and project data\n";
    }
    
    // Create some sample attendance records for today if none exist
    $today = date('Y-m-d');
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM attendance WHERE DATE(check_in) = ?");
    $stmt->execute([$today]);
    $result = $stmt->fetch();
    
    if ($result['count'] == 0) {
        // Get first few active users
        $stmt = $db->prepare("SELECT id FROM users WHERE status = 'active' LIMIT 3");
        $stmt->execute();
        $users = $stmt->fetchAll();
        
        foreach ($users as $i => $user) {
            $checkIn = $today . ' ' . sprintf('%02d:00:00', 9 + $i);
            $checkOut = $today . ' ' . sprintf('%02d:00:00', 17 + $i);
            $locations = ['ERGON Company', 'Project Alpha Site', 'Beta Construction Site'];
            $projects = ['----', 'Project Alpha', 'Project Beta'];
            
            $stmt = $db->prepare("INSERT INTO attendance (user_id, check_in, check_out, location_name, location_display, project_name, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
            $stmt->execute([
                $user['id'], 
                $checkIn, 
                $checkOut, 
                $locations[$i], 
                $locations[$i], 
                $projects[$i]
            ]);
        }
        echo "✅ Created sample attendance records for today\n";
    }
    
    echo "✅ Attendance data populated successfully!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>