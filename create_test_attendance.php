<?php
require_once __DIR__ . '/app/config/database.php';

try {
    $db = Database::connect();
    
    // Create attendance record for Nelson today
    $today = date('Y-m-d');
    $checkInTime = $today . ' 17:30:33';
    
    // First check if Nelson already has attendance today
    $stmt = $db->prepare("SELECT * FROM attendance WHERE user_id = 37 AND DATE(check_in) = ?");
    $stmt->execute([$today]);
    $existing = $stmt->fetch();
    
    if (!$existing) {
        // Create new attendance record for Nelson
        $stmt = $db->prepare("INSERT INTO attendance (user_id, check_in, location_name, location_display, project_name, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        if ($stmt->execute([37, $checkInTime, 'Main Office', 'ERGON Company', 'Project Alpha'])) {
            echo "✅ Created attendance record for Nelson\n";
        }
    } else {
        // Update existing record
        $stmt = $db->prepare("UPDATE attendance SET location_display = 'ERGON Company', project_name = 'Project Alpha' WHERE id = ?");
        if ($stmt->execute([$existing['id']])) {
            echo "✅ Updated existing attendance record for Nelson\n";
        }
    }
    
    // Update ALL attendance records to ensure they have location and project data
    $stmt = $db->prepare("
        UPDATE attendance 
        SET 
            location_display = COALESCE(
                NULLIF(location_display, ''),
                CASE 
                    WHEN location_name IS NOT NULL AND location_name != '' AND location_name != 'Office' THEN location_name
                    ELSE 'ERGON Company'
                END
            ),
            project_name = COALESCE(
                NULLIF(project_name, ''),
                'Project Alpha'
            )
        WHERE check_in IS NOT NULL
    ");
    
    if ($stmt->execute()) {
        echo "✅ Updated " . $stmt->rowCount() . " attendance records with location/project data\n";
    }
    
    // Show current attendance for today
    echo "\n=== Today's attendance after update ===\n";
    $stmt = $db->prepare("
        SELECT u.name, u.role, a.check_in, a.location_display, a.project_name
        FROM users u
        LEFT JOIN attendance a ON u.id = a.user_id AND DATE(a.check_in) = ?
        WHERE u.status = 'active'
        ORDER BY u.role DESC, u.name
    ");
    $stmt->execute([$today]);
    $results = $stmt->fetchAll();
    
    foreach ($results as $result) {
        $status = $result['check_in'] ? 'Present' : 'Absent';
        $location = $result['location_display'] ?: '---';
        $project = $result['project_name'] ?: '----';
        echo "{$result['name']} ({$result['role']}): $status - Location: $location, Project: $project\n";
    }
    
    echo "\n✅ Test attendance created and all records updated!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>