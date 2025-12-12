<?php
require_once __DIR__ . '/app/config/database.php';

try {
    $db = Database::connect();
    
    echo "<h2>Attendance Data Debug</h2>";
    
    // Check today's attendance records
    $today = date('Y-m-d');
    echo "<h3>Today's Attendance Records ($today):</h3>";
    
    $stmt = $db->prepare("
        SELECT 
            a.id,
            a.user_id,
            u.name,
            a.check_in,
            a.check_out,
            a.location_name,
            a.location_display,
            a.project_name,
            a.project_id,
            DATE(a.check_in) as attendance_date
        FROM attendance a 
        LEFT JOIN users u ON a.user_id = u.id 
        WHERE DATE(a.check_in) = ?
        ORDER BY a.check_in DESC
    ");
    $stmt->execute([$today]);
    $todayRecords = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($todayRecords)) {
        echo "<p>No attendance records found for today ($today)</p>";
        
        // Check recent records
        echo "<h3>Recent Attendance Records (last 7 days):</h3>";
        $stmt = $db->prepare("
            SELECT 
                a.id,
                a.user_id,
                u.name,
                a.check_in,
                a.check_out,
                a.location_name,
                a.location_display,
                a.project_name,
                a.project_id,
                DATE(a.check_in) as attendance_date
            FROM attendance a 
            LEFT JOIN users u ON a.user_id = u.id 
            WHERE DATE(a.check_in) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
            ORDER BY a.check_in DESC
            LIMIT 10
        ");
        $stmt->execute();
        $recentRecords = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($recentRecords)) {
            echo "<p>No recent attendance records found</p>";
        } else {
            echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
            echo "<tr><th>ID</th><th>User</th><th>Date</th><th>Check In</th><th>Check Out</th><th>Location Name</th><th>Location Display</th><th>Project Name</th></tr>";
            foreach ($recentRecords as $record) {
                echo "<tr>";
                echo "<td>" . $record['id'] . "</td>";
                echo "<td>" . htmlspecialchars($record['name']) . "</td>";
                echo "<td>" . $record['attendance_date'] . "</td>";
                echo "<td>" . $record['check_in'] . "</td>";
                echo "<td>" . ($record['check_out'] ?: 'NULL') . "</td>";
                echo "<td>" . ($record['location_name'] ?: 'NULL') . "</td>";
                echo "<td>" . ($record['location_display'] ?: 'NULL') . "</td>";
                echo "<td>" . ($record['project_name'] ?: 'NULL') . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
    } else {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>User</th><th>Check In</th><th>Check Out</th><th>Location Name</th><th>Location Display</th><th>Project Name</th></tr>";
        foreach ($todayRecords as $record) {
            echo "<tr>";
            echo "<td>" . $record['id'] . "</td>";
            echo "<td>" . htmlspecialchars($record['name']) . "</td>";
            echo "<td>" . $record['check_in'] . "</td>";
            echo "<td>" . ($record['check_out'] ?: 'NULL') . "</td>";
            echo "<td>" . ($record['location_name'] ?: 'NULL') . "</td>";
            echo "<td>" . ($record['location_display'] ?: 'NULL') . "</td>";
            echo "<td>" . ($record['project_name'] ?: 'NULL') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Check user info
    echo "<h3>Current User Info:</h3>";
    session_start();
    if (isset($_SESSION['user_id'])) {
        $stmt = $db->prepare("SELECT id, name, role FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user) {
            echo "<p>User ID: " . $user['id'] . "</p>";
            echo "<p>Name: " . htmlspecialchars($user['name']) . "</p>";
            echo "<p>Role: " . $user['role'] . "</p>";
        }
    } else {
        echo "<p>No user session found</p>";
    }
    
    // Check attendance table structure
    echo "<h3>Attendance Table Structure:</h3>";
    $stmt = $db->query("DESCRIBE attendance");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    foreach ($columns as $column) {
        echo "<tr>";
        echo "<td>" . $column['Field'] . "</td>";
        echo "<td>" . $column['Type'] . "</td>";
        echo "<td>" . $column['Null'] . "</td>";
        echo "<td>" . $column['Key'] . "</td>";
        echo "<td>" . ($column['Default'] ?: 'NULL') . "</td>";
        echo "<td>" . $column['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>