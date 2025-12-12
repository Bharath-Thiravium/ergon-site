<?php
session_start();
require_once __DIR__ . '/app/config/database.php';

// Set admin session for testing
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'admin';
$_SESSION['user_name'] = 'Admin User';

try {
    $db = Database::connect();
    
    echo "<h2>Debug Attendance Display Issue</h2>";
    
    // Check current attendance table structure
    echo "<h3>1. Attendance Table Structure:</h3>";
    $stmt = $db->query("SHOW COLUMNS FROM attendance");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($columns as $col) {
        echo "<p>{$col['Field']} - {$col['Type']} - Default: {$col['Default']}</p>";
    }
    
    // Check current attendance data
    echo "<h3>2. Current Attendance Data:</h3>";
    $stmt = $db->query("SELECT id, user_id, check_in, location_name, location_display, project_name FROM attendance ORDER BY created_at DESC LIMIT 5");
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1'><tr><th>ID</th><th>User ID</th><th>Check In</th><th>Location Name</th><th>Location Display</th><th>Project Name</th></tr>";
    foreach ($records as $record) {
        echo "<tr>";
        echo "<td>{$record['id']}</td>";
        echo "<td>{$record['user_id']}</td>";
        echo "<td>{$record['check_in']}</td>";
        echo "<td>" . ($record['location_name'] ?: 'NULL') . "</td>";
        echo "<td>" . ($record['location_display'] ?: 'NULL') . "</td>";
        echo "<td>" . ($record['project_name'] ?: 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Test the exact query from AttendanceController
    echo "<h3>3. Testing AttendanceController Query:</h3>";
    $filterDate = date('Y-m-d');
    $roleFilter = "u.role = 'user'";
    
    $stmt = $db->prepare("
        SELECT 
            u.id,
            u.name,
            u.role,
            a.check_in,
            a.check_out,
            a.location_display,
            a.project_name,
            CASE 
                WHEN a.location_display IS NOT NULL AND a.location_display != '' THEN a.location_display
                ELSE '---'
            END as display_location,
            CASE 
                WHEN a.project_name IS NOT NULL AND a.project_name != '' THEN a.project_name
                ELSE '----'
            END as display_project,
            CASE 
                WHEN a.check_in IS NOT NULL THEN 'Present'
                ELSE 'Absent'
            END as status
        FROM users u
        LEFT JOIN attendance a ON u.id = a.user_id AND DATE(a.check_in) = ?
        WHERE $roleFilter AND u.status = 'active'
        ORDER BY u.role DESC, u.name
    ");
    $stmt->execute([$filterDate]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1'>";
    echo "<tr><th>Name</th><th>Role</th><th>Status</th><th>Raw Location</th><th>Raw Project</th><th>Display Location</th><th>Display Project</th></tr>";
    foreach ($results as $result) {
        echo "<tr>";
        echo "<td>{$result['name']}</td>";
        echo "<td>{$result['role']}</td>";
        echo "<td>{$result['status']}</td>";
        echo "<td>" . ($result['location_display'] ?: 'NULL') . "</td>";
        echo "<td>" . ($result['project_name'] ?: 'NULL') . "</td>";
        echo "<td>{$result['display_location']}</td>";
        echo "<td>{$result['display_project']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Create test data with proper values
    echo "<h3>4. Creating Test Data with Proper Values:</h3>";
    
    // Clear existing attendance for today
    $stmt = $db->prepare("DELETE FROM attendance WHERE DATE(check_in) = ?");
    $stmt->execute([$filterDate]);
    
    // Insert test records with proper location data
    $testData = [
        [1, $filterDate . ' 09:34:00', $filterDate . ' 20:00:00', 'Athena Solutions Office', 'Athena Solutions', null],
        [2, null, null, null, null, null],
        [3, $filterDate . ' 08:30:00', null, 'Alpha Construction Site', 'Alpha Construction Site', 'Project Alpha']
    ];
    
    $stmt = $db->prepare("INSERT INTO attendance (user_id, check_in, check_out, location_name, location_display, project_name, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
    
    foreach ($testData as $data) {
        if ($data[1]) { // Only insert if check_in exists
            $stmt->execute($data);
            echo "<p>✅ Inserted test record for user {$data[0]}</p>";
        }
    }
    
    // Test the query again
    echo "<h3>5. Testing Query After Data Insert:</h3>";
    $stmt->execute([$filterDate]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1'>";
    echo "<tr><th>Name</th><th>Status</th><th>Display Location</th><th>Display Project</th><th>Check In</th><th>Check Out</th></tr>";
    foreach ($results as $result) {
        echo "<tr>";
        echo "<td>{$result['name']}</td>";
        echo "<td>{$result['status']}</td>";
        echo "<td>{$result['display_location']}</td>";
        echo "<td>{$result['display_project']}</td>";
        echo "<td>" . ($result['check_in'] ?: 'Not set') . "</td>";
        echo "<td>" . ($result['check_out'] ?: 'Not set') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h3>✅ Debug Complete!</h3>";
    echo "<p>Now check: <a href='/ergon-site/attendance' target='_blank'>Attendance Page</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>