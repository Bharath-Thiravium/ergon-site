<?php
session_start();
require_once __DIR__ . '/app/config/database.php';

try {
    $db = Database::connect();
    
    echo "<h2>Fix Attendance Data - Final</h2>";
    
    // Update existing attendance records with proper location and project data
    echo "<h3>1. Updating Existing Records:</h3>";
    
    // First, update records that have project_id using available project columns
    $stmt = $db->prepare("
        UPDATE attendance a 
        LEFT JOIN projects p ON a.project_id = p.id 
        SET 
            a.location_display = CASE 
                WHEN p.location_title IS NOT NULL AND p.location_title != '' THEN p.location_title
                WHEN p.name IS NOT NULL AND p.name != '' THEN CONCAT(p.name, ' Site')
                WHEN a.check_in IS NOT NULL THEN 'ERGON Company' 
                ELSE '---' 
            END,
            a.project_name = CASE 
                WHEN p.name IS NOT NULL AND p.name != '' THEN p.name 
                WHEN a.check_in IS NOT NULL THEN '----' 
                ELSE '----' 
            END
        WHERE a.project_id IS NOT NULL
    ");
    
    if ($stmt->execute()) {
        $updatedRows = $stmt->rowCount();
        echo "<p>✅ Updated $updatedRows records with project data</p>";
    } else {
        echo "<p>❌ Failed to update records with project data</p>";
    }
    
    // Update records without project_id to have default values
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
        WHERE (location_display IS NULL OR location_display = '') OR (project_name IS NULL OR project_name = '')
    ");
    
    if ($stmt->execute()) {
        $updatedRows = $stmt->rowCount();
        echo "<p>✅ Updated $updatedRows records without project_id</p>";
    }
    
    // Create some test data for today
    echo "<h3>2. Creating Test Data for Today:</h3>";
    $today = date('Y-m-d');
    
    // Clear existing test data for today
    $stmt = $db->prepare("DELETE FROM attendance WHERE DATE(check_in) = ? AND location_display IN ('ERGON Company', 'Alpha Construction Site', 'Home Office')");
    $stmt->execute([$today]);
    
    // Insert test records with proper location and project data
    $testData = [
        [1, $today . ' 09:00:00', $today . ' 18:00:00', 'Main Office', 'ERGON Company', '----'],
        [2, $today . ' 08:30:00', null, 'Project Site Alpha', 'Alpha Construction Site', 'Project Alpha'],
        [3, $today . ' 09:15:00', $today . ' 17:30:00', 'Remote Work', 'Home Office', 'Remote Work']
    ];
    
    $stmt = $db->prepare("INSERT INTO attendance (user_id, check_in, check_out, location_name, location_display, project_name, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
    
    foreach ($testData as $data) {
        try {
            $stmt->execute($data);
            echo "<p>✅ Inserted test record for user {$data[0]} - Location: {$data[4]}, Project: {$data[5]}</p>";
        } catch (Exception $e) {
            echo "<p>❌ Failed to insert test record: " . $e->getMessage() . "</p>";
        }
    }
    
    // Test the final result
    echo "<h3>3. Final Test - Recent Attendance Records:</h3>";
    $stmt = $db->query("
        SELECT 
            a.id, 
            a.user_id, 
            u.name as user_name,
            a.check_in, 
            a.check_out,
            a.location_name, 
            a.location_display, 
            a.project_name 
        FROM attendance a
        LEFT JOIN users u ON a.user_id = u.id
        WHERE DATE(a.check_in) = CURDATE() OR a.check_in IS NULL
        ORDER BY a.created_at DESC 
        LIMIT 10
    ");
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #f0f0f0;'><th>ID</th><th>User</th><th>Check In</th><th>Check Out</th><th>Location Name</th><th>Location Display</th><th>Project Name</th></tr>";
    foreach ($records as $record) {
        echo "<tr>";
        echo "<td>{$record['id']}</td>";
        echo "<td>" . ($record['user_name'] ?: 'Unknown') . "</td>";
        echo "<td>" . ($record['check_in'] ?: 'NULL') . "</td>";
        echo "<td>" . ($record['check_out'] ?: 'NULL') . "</td>";
        echo "<td>" . ($record['location_name'] ?: 'NULL') . "</td>";
        echo "<td style='font-weight: bold; color: blue;'>" . ($record['location_display'] ?: 'NULL') . "</td>";
        echo "<td style='font-weight: bold; color: green;'>" . ($record['project_name'] ?: 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Test the controller query
    echo "<h3>4. Testing Controller Query:</h3>";
    $filterDate = date('Y-m-d');
    $roleFilter = "u.role IN ('admin', 'user', 'owner')";
    
    $stmt = $db->prepare("
        SELECT 
            u.id,
            u.id as user_id,
            u.name,
            u.email,
            u.role,
            COALESCE(d.name, 'Not Assigned') as department,
            a.id as attendance_id,
            a.check_in,
            a.check_out,
            a.location_display,
            a.project_name,
            CASE 
                WHEN a.location_name = 'On Approved Leave' THEN 'On Leave'
                WHEN a.check_in IS NOT NULL THEN 'Present'
                ELSE 'Absent'
            END as status,
            CASE 
                WHEN a.check_in IS NOT NULL AND a.check_out IS NOT NULL THEN 
                    CONCAT(FLOOR(TIMESTAMPDIFF(MINUTE, a.check_in, a.check_out) / 60), 'h ', 
                           MOD(TIMESTAMPDIFF(MINUTE, a.check_in, a.check_out), 60), 'm')
                ELSE '0h 0m'
            END as working_hours
        FROM users u
        LEFT JOIN departments d ON u.department_id = d.id
        LEFT JOIN attendance a ON u.id = a.user_id AND DATE(a.check_in) = ?
        WHERE $roleFilter AND u.status = 'active'
        ORDER BY u.role DESC, u.name
    ");
    $stmt->execute([$filterDate]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #e0e0e0;'><th>Name</th><th>Role</th><th>Status</th><th>Location Display</th><th>Project Name</th><th>Working Hours</th></tr>";
    foreach ($results as $result) {
        echo "<tr>";
        echo "<td>{$result['name']}</td>";
        echo "<td>{$result['role']}</td>";
        echo "<td>{$result['status']}</td>";
        echo "<td style='font-weight: bold; color: blue;'>" . ($result['location_display'] ?: '---') . "</td>";
        echo "<td style='font-weight: bold; color: green;'>" . ($result['project_name'] ?: '----') . "</td>";
        echo "<td>{$result['working_hours']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h3>✅ Fix Complete!</h3>";
    echo "<p><strong>Location and Project data should now be visible in the attendance table.</strong></p>";
    echo "<p>Check the attendance page: <a href='/ergon-site/attendance' target='_blank' style='color: blue; font-weight: bold;'>🔗 Attendance Page</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>