<?php
session_start();
require_once __DIR__ . '/app/config/database.php';

echo "<h2>Attendance Debug & Fix Script</h2>";

try {
    $db = Database::connect();
    $today = date('Y-m-d');
    
    // 1. Check current user session
    echo "<h3>1. User Session Check</h3>";
    if (isset($_SESSION['user_id'])) {
        echo "✅ User ID: " . $_SESSION['user_id'] . "<br>";
        echo "✅ User Name: " . ($_SESSION['user_name'] ?? 'Not set') . "<br>";
        echo "✅ User Role: " . ($_SESSION['role'] ?? 'Not set') . "<br>";
    } else {
        echo "❌ No user session found<br>";
        exit;
    }
    
    // 2. Check attendance table structure
    echo "<h3>2. Attendance Table Structure</h3>";
    $stmt = $db->query("DESCRIBE attendance");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<table border='1'><tr><th>Field</th><th>Type</th><th>Null</th><th>Default</th></tr>";
    foreach ($columns as $col) {
        echo "<tr><td>{$col['Field']}</td><td>{$col['Type']}</td><td>{$col['Null']}</td><td>" . ($col['Default'] ?: 'NULL') . "</td></tr>";
    }
    echo "</table>";
    
    // 3. Check projects table
    echo "<h3>3. Projects Table Data</h3>";
    $stmt = $db->query("SELECT id, name, place, status FROM projects ORDER BY place ASC");
    $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (empty($projects)) {
        echo "❌ No projects found<br>";
    } else {
        echo "<table border='1'><tr><th>ID</th><th>Name</th><th>Place</th><th>Status</th></tr>";
        foreach ($projects as $project) {
            echo "<tr><td>{$project['id']}</td><td>{$project['name']}</td><td>{$project['place']}</td><td>{$project['status']}</td></tr>";
        }
        echo "</table>";
    }
    
    // 4. Check today's attendance records
    echo "<h3>4. Today's Attendance Records ($today)</h3>";
    $stmt = $db->prepare("
        SELECT 
            a.id, a.user_id, u.name as user_name, a.check_in, a.check_out, 
            a.project_id, a.location_name, a.location_display, a.project_name,
            p.name as project_table_name, p.place as project_table_place
        FROM attendance a 
        LEFT JOIN users u ON a.user_id = u.id 
        LEFT JOIN projects p ON a.project_id = p.id
        WHERE DATE(a.check_in) = ?
    ");
    $stmt->execute([$today]);
    $todayRecords = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($todayRecords)) {
        echo "❌ No attendance records for today<br>";
        
        // Create test record
        echo "<h4>Creating Test Record...</h4>";
        $userId = $_SESSION['user_id'];
        $checkIn = $today . ' 08:30:00';
        
        // Get first project if available
        $projectId = null;
        if (!empty($projects)) {
            $projectId = $projects[0]['id'];
            echo "Using project ID: $projectId ({$projects[0]['name']})<br>";
        }
        
        $stmt = $db->prepare("INSERT INTO attendance (user_id, project_id, check_in, location_name, location_display, project_name, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
        $result = $stmt->execute([
            $userId,
            $projectId,
            $checkIn,
            'Test Location',
            'Test Location Display',
            'Test Project'
        ]);
        
        if ($result) {
            echo "✅ Test record created<br>";
        } else {
            echo "❌ Failed to create test record<br>";
        }
        
        // Re-fetch records
        $stmt->execute([$today]);
        $todayRecords = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    if (!empty($todayRecords)) {
        echo "<table border='1'>";
        echo "<tr><th>ID</th><th>User</th><th>Check In</th><th>Project ID</th><th>Location Name</th><th>Location Display</th><th>Project Name</th><th>DB Project Name</th><th>DB Project Place</th></tr>";
        foreach ($todayRecords as $record) {
            echo "<tr>";
            echo "<td>{$record['id']}</td>";
            echo "<td>{$record['user_name']}</td>";
            echo "<td>{$record['check_in']}</td>";
            echo "<td>" . ($record['project_id'] ?: 'NULL') . "</td>";
            echo "<td>" . ($record['location_name'] ?: 'NULL') . "</td>";
            echo "<td>" . ($record['location_display'] ?: 'NULL') . "</td>";
            echo "<td>" . ($record['project_name'] ?: 'NULL') . "</td>";
            echo "<td>" . ($record['project_table_name'] ?: 'NULL') . "</td>";
            echo "<td>" . ($record['project_table_place'] ?: 'NULL') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // 5. Test the actual query used in controller
    echo "<h3>5. Testing Controller Query</h3>";
    $filterDate = $today;
    $userId = $_SESSION['user_id'];
    
    $stmt = $db->prepare("
        SELECT 
            u.id,
            u.name,
            u.role,
            a.id as attendance_id,
            a.check_in,
            a.check_out,
            CASE 
                WHEN p.place IS NOT NULL AND p.place != '' THEN p.place
                WHEN a.check_in IS NOT NULL THEN 'ERGON Company'
                ELSE '---'
            END as location_display,
            CASE 
                WHEN p.name IS NOT NULL AND p.name != '' THEN p.name
                WHEN a.check_in IS NOT NULL THEN '----'
                ELSE '----'
            END as project_name
        FROM users u
        LEFT JOIN attendance a ON u.id = a.user_id AND DATE(a.check_in) = ?
        LEFT JOIN projects p ON a.project_id = p.id
        WHERE u.id = ?
    ");
    $stmt->execute([$filterDate, $userId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result) {
        echo "<table border='1'>";
        echo "<tr><th>Field</th><th>Value</th></tr>";
        foreach ($result as $key => $value) {
            echo "<tr><td>$key</td><td>" . ($value ?: 'NULL') . "</td></tr>";
        }
        echo "</table>";
        
        echo "<h4>Expected Display:</h4>";
        echo "Location: <strong>" . $result['location_display'] . "</strong><br>";
        echo "Project: <strong>" . $result['project_name'] . "</strong><br>";
    } else {
        echo "❌ No result from controller query<br>";
    }
    
    // 6. Fix missing project_id
    echo "<h3>6. Fix Missing Project ID</h3>";
    if (!empty($todayRecords)) {
        foreach ($todayRecords as $record) {
            if (!$record['project_id'] && !empty($projects)) {
                echo "Updating attendance ID {$record['id']} with project ID {$projects[0]['id']}<br>";
                $stmt = $db->prepare("UPDATE attendance SET project_id = ? WHERE id = ?");
                $stmt->execute([$projects[0]['id'], $record['id']]);
                echo "✅ Updated<br>";
            }
        }
    }
    
    echo "<h3>✅ Debug Complete - Refresh attendance page to see results</h3>";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
table { border-collapse: collapse; margin: 10px 0; }
th, td { padding: 8px; text-align: left; border: 1px solid #ddd; }
th { background-color: #f2f2f2; }
h3 { color: #333; border-bottom: 2px solid #007cba; padding-bottom: 5px; }
</style>