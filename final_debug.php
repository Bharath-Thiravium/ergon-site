<?php
session_start();
require_once __DIR__ . '/app/config/database.php';

try {
    $db = Database::connect();
    $today = date('Y-m-d');
    $userId = $_SESSION['user_id'] ?? null;
    
    if (!$userId) {
        echo "❌ Please login first";
        exit;
    }
    
    echo "<h2>Final Debug - Attendance Query Analysis</h2>";
    
    // 1. Check current attendance record
    echo "<h3>1. Current Attendance Record</h3>";
    $stmt = $db->prepare("SELECT * FROM attendance WHERE user_id = ? AND DATE(check_in) = ?");
    $stmt->execute([$userId, $today]);
    $attendance = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($attendance) {
        echo "✅ Attendance record found (ID: {$attendance['id']})<br>";
        echo "project_id: {$attendance['project_id']}<br>";
        echo "location_display: " . ($attendance['location_display'] ?: 'NULL') . "<br>";
        echo "project_name: " . ($attendance['project_name'] ?: 'NULL') . "<br><br>";
    } else {
        echo "❌ No attendance record found<br>";
    }
    
    // 2. Check project data
    echo "<h3>2. Project ID 22 Data</h3>";
    $stmt = $db->prepare("SELECT * FROM projects WHERE id = 22");
    $stmt->execute();
    $project = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($project) {
        echo "✅ Project found:<br>";
        echo "ID: {$project['id']}<br>";
        echo "Name: {$project['name']}<br>";
        echo "Place: {$project['place']}<br>";
        echo "Status: {$project['status']}<br><br>";
    } else {
        echo "❌ Project ID 22 not found<br>";
    }
    
    // 3. Test the exact controller query for admin view
    echo "<h3>3. Testing Admin Controller Query</h3>";
    $filterDate = $today;
    
    $stmt = $db->prepare("
        SELECT 
            u.id,
            u.name,
            u.role,
            a.id as attendance_id,
            a.check_in,
            a.check_out,
            a.project_id,
            p.place as project_place,
            p.name as project_name_from_table,
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
        
        echo "<h4>Expected Display Values:</h4>";
        echo "Location Column: <strong>{$result['location_display']}</strong><br>";
        echo "Project Column: <strong>{$result['project_name']}</strong><br><br>";
    }
    
    // 4. Force update attendance record if needed
    if ($attendance && $attendance['project_id'] != 22) {
        echo "<h3>4. Fixing Attendance Record</h3>";
        $stmt = $db->prepare("UPDATE attendance SET project_id = 22 WHERE id = ?");
        $result = $stmt->execute([$attendance['id']]);
        
        if ($result) {
            echo "✅ Updated project_id to 22<br>";
            
            // Re-test the query
            echo "<h4>Re-testing after update:</h4>";
            $stmt = $db->prepare("
                SELECT 
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
                FROM attendance a
                LEFT JOIN projects p ON a.project_id = p.id
                WHERE a.id = ?
            ");
            $stmt->execute([$attendance['id']]);
            $updated = $stmt->fetch(PDO::FETCH_ASSOC);
            
            echo "New Location: <strong>{$updated['location_display']}</strong><br>";
            echo "New Project: <strong>{$updated['project_name']}</strong><br>";
        }
    }
    
    // 5. Check which view file is being used
    echo "<h3>5. View File Check</h3>";
    $role = $_SESSION['role'] ?? 'user';
    echo "Current user role: $role<br>";
    
    if ($role === 'owner') {
        echo "Using view: attendance/owner_index.php<br>";
    } elseif ($role === 'admin') {
        echo "Using view: attendance/admin_index.php<br>";
    } else {
        echo "Using view: attendance/index.php<br>";
    }
    
    echo "<br><h3>✅ Debug Complete</h3>";
    echo "<p><strong>If Location still shows '---' and Project shows '----', the issue is in the view file rendering, not the database query.</strong></p>";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
table { border-collapse: collapse; margin: 10px 0; }
th, td { padding: 8px; text-align: left; border: 1px solid #ddd; }
th { background-color: #f2f2f2; }
h3 { color: #333; border-bottom: 1px solid #ccc; padding-bottom: 5px; }
</style>