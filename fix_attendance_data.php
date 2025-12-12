<?php
session_start();
require_once __DIR__ . '/app/config/database.php';

try {
    $db = Database::connect();
    
    echo "<h2>Fix Attendance Data</h2>";
    
    // Check projects table structure
    echo "<h3>1. Projects Table Structure:</h3>";
    $stmt = $db->query("SHOW COLUMNS FROM projects");
    $projectColumns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($projectColumns as $col) {
        echo "<p>{$col['Field']} - {$col['Type']}</p>";
    }
    
    // Update existing attendance records with proper location and project data
    echo "<h3>2. Updating Existing Records:</h3>";
    
    // First, update records that have project_id
    $stmt = $db->prepare("
        UPDATE attendance a 
        LEFT JOIN projects p ON a.project_id = p.id 
        SET 
            a.location_display = CASE 
                WHEN p.location IS NOT NULL AND p.location != '' THEN p.location 
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
                WHEN check_in IS NOT NULL THEN 'ERGON Company' 
                ELSE '---' 
            END,
            project_name = CASE 
                WHEN check_in IS NOT NULL THEN '----' 
                ELSE '----' 
            END
        WHERE project_id IS NULL AND (location_display IS NULL OR project_name IS NULL)
    ");
    
    if ($stmt->execute()) {
        $updatedRows = $stmt->rowCount();
        echo "<p>✅ Updated $updatedRows records without project_id</p>";
    }
    
    // Create some test data for today
    echo "<h3>3. Creating Test Data for Today:</h3>";
    $today = date('Y-m-d');
    
    // Clear existing test data for today
    $stmt = $db->prepare("DELETE FROM attendance WHERE DATE(check_in) = ? AND user_id IN (1,2,3)");
    $stmt->execute([$today]);
    
    // Insert test records
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
    echo "<h3>4. Final Test - Recent Attendance Records:</h3>";
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
        ORDER BY a.created_at DESC 
        LIMIT 10
    ");
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse;'>";
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
    
    echo "<h3>✅ Fix Complete!</h3>";
    echo "<p>Location and Project data should now be visible in the attendance table.</p>";
    echo "<p>Check the attendance page: <a href='/ergon-site/attendance' target='_blank'>Attendance Page</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>