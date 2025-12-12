<?php
session_start();
require_once __DIR__ . '/app/config/database.php';

// Set test session for admin user
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'admin';
$_SESSION['user_name'] = 'Test Admin';

try {
    $db = Database::connect();
    
    echo "<h2>Attendance Debug Information</h2>";
    
    // Check if attendance table has the required columns
    echo "<h3>1. Attendance Table Structure</h3>";
    $stmt = $db->query("SHOW COLUMNS FROM attendance");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<table border='1'><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    foreach ($columns as $col) {
        echo "<tr><td>{$col['Field']}</td><td>{$col['Type']}</td><td>{$col['Null']}</td><td>{$col['Key']}</td><td>{$col['Default']}</td></tr>";
    }
    echo "</table>";
    
    // Check current attendance records
    echo "<h3>2. Current Attendance Records</h3>";
    $stmt = $db->query("SELECT id, user_id, check_in, check_out, location_name, location_display, project_name FROM attendance ORDER BY created_at DESC LIMIT 10");
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($records)) {
        echo "<p>No attendance records found.</p>";
    } else {
        echo "<table border='1'><tr><th>ID</th><th>User ID</th><th>Check In</th><th>Check Out</th><th>Location Name</th><th>Location Display</th><th>Project Name</th></tr>";
        foreach ($records as $record) {
            echo "<tr>";
            echo "<td>{$record['id']}</td>";
            echo "<td>{$record['user_id']}</td>";
            echo "<td>{$record['check_in']}</td>";
            echo "<td>{$record['check_out']}</td>";
            echo "<td>{$record['location_name']}</td>";
            echo "<td>{$record['location_display']}</td>";
            echo "<td>{$record['project_name']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Check users table
    echo "<h3>3. Active Users</h3>";
    $stmt = $db->query("SELECT id, name, role, status FROM users WHERE status = 'active'");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($users)) {
        echo "<p>No active users found.</p>";
    } else {
        echo "<table border='1'><tr><th>ID</th><th>Name</th><th>Role</th><th>Status</th></tr>";
        foreach ($users as $user) {
            echo "<tr><td>{$user['id']}</td><td>{$user['name']}</td><td>{$user['role']}</td><td>{$user['status']}</td></tr>";
        }
        echo "</table>";
    }
    
    // Test the attendance query with location data
    echo "<h3>4. Test Attendance Query with Location Data</h3>";
    $filterDate = date('Y-m-d');
    $stmt = $db->prepare("
        SELECT 
            u.id,
            u.name,
            u.role,
            a.check_in,
            a.check_out,
            COALESCE(a.location_display, '---') as location_display,
            COALESCE(a.project_name, '----') as project_name,
            CASE 
                WHEN a.check_in IS NOT NULL THEN 'Present'
                ELSE 'Absent'
            END as status
        FROM users u
        LEFT JOIN attendance a ON u.id = a.user_id AND DATE(a.check_in) = ?
        WHERE u.status = 'active'
        ORDER BY u.role DESC, u.name
    ");
    $stmt->execute([$filterDate]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($results)) {
        echo "<p>No results from attendance query.</p>";
    } else {
        echo "<table border='1'><tr><th>ID</th><th>Name</th><th>Role</th><th>Check In</th><th>Check Out</th><th>Location Display</th><th>Project Name</th><th>Status</th></tr>";
        foreach ($results as $result) {
            echo "<tr>";
            echo "<td>{$result['id']}</td>";
            echo "<td>{$result['name']}</td>";
            echo "<td>{$result['role']}</td>";
            echo "<td>{$result['check_in']}</td>";
            echo "<td>{$result['check_out']}</td>";
            echo "<td>{$result['location_display']}</td>";
            echo "<td>{$result['project_name']}</td>";
            echo "<td>{$result['status']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>

<style>
table { border-collapse: collapse; margin: 10px 0; }
th, td { padding: 8px; text-align: left; }
th { background-color: #f2f2f2; }
</style>