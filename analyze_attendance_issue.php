<?php
session_start();
require_once __DIR__ . '/app/config/database.php';

// Set session for testing
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'admin';
$_SESSION['user_name'] = 'Admin User';

try {
    $db = Database::connect();
    
    echo "<h2>🔍 Complete Attendance Analysis</h2>";
    
    // 1. Check database structure
    echo "<h3>1. Database Structure Analysis:</h3>";
    
    $stmt = $db->query("SHOW COLUMNS FROM attendance");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $hasLocationDisplay = false;
    $hasProjectName = false;
    
    echo "<table border='1'><tr><th>Column</th><th>Type</th><th>Null</th><th>Default</th></tr>";
    foreach ($columns as $col) {
        echo "<tr><td>{$col['Field']}</td><td>{$col['Type']}</td><td>{$col['Null']}</td><td>{$col['Default']}</td></tr>";
        if ($col['Field'] === 'location_display') $hasLocationDisplay = true;
        if ($col['Field'] === 'project_name') $hasProjectName = true;
    }
    echo "</table>";
    
    echo "<p>✅ location_display column exists: " . ($hasLocationDisplay ? 'YES' : 'NO') . "</p>";
    echo "<p>✅ project_name column exists: " . ($hasProjectName ? 'YES' : 'NO') . "</p>";
    
    // 2. Check current attendance data
    echo "<h3>2. Current Attendance Data:</h3>";
    
    $stmt = $db->query("SELECT * FROM attendance ORDER BY created_at DESC LIMIT 10");
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($records)) {
        echo "<p>❌ NO ATTENDANCE RECORDS FOUND!</p>";
        echo "<p>Creating test records...</p>";
        
        // Create test records
        $today = date('Y-m-d');
        $testRecords = [
            [1, $today . ' 09:34:00', $today . ' 20:00:00', 'Athena Solutions Office', 'Athena Solutions', null],
            [2, $today . ' 08:30:00', null, 'Alpha Construction Site', 'Alpha Construction Site', 'Project Alpha']
        ];
        
        $stmt = $db->prepare("INSERT INTO attendance (user_id, check_in, check_out, location_name, location_display, project_name, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
        
        foreach ($testRecords as $record) {
            $stmt->execute($record);
            echo "<p>➕ Created test record for user {$record[0]}</p>";
        }
        
        // Fetch again
        $stmt = $db->query("SELECT * FROM attendance ORDER BY created_at DESC LIMIT 10");
        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    echo "<table border='1'><tr><th>ID</th><th>User ID</th><th>Check In</th><th>Location Name</th><th>Location Display</th><th>Project Name</th></tr>";
    foreach ($records as $record) {
        echo "<tr>";
        echo "<td>{$record['id']}</td>";
        echo "<td>{$record['user_id']}</td>";
        echo "<td>{$record['check_in']}</td>";
        echo "<td>" . ($record['location_name'] ?: 'NULL') . "</td>";
        echo "<td style='background: " . ($record['location_display'] ? '#d4edda' : '#f8d7da') . ";'>" . ($record['location_display'] ?: 'NULL') . "</td>";
        echo "<td style='background: " . ($record['project_name'] ? '#d4edda' : '#f8d7da') . ";'>" . ($record['project_name'] ?: 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // 3. Test the exact AttendanceController query
    echo "<h3>3. Testing AttendanceController Query:</h3>";
    
    $filterDate = date('Y-m-d');
    $roleFilter = "u.role IN ('admin', 'user', 'owner')";
    
    $query = "
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
                WHEN a.location_display IS NOT NULL AND a.location_display != '' THEN a.location_display
                ELSE '---'
            END as display_location,
            CASE 
                WHEN a.project_name IS NOT NULL AND a.project_name != '' THEN a.project_name
                ELSE '----'
            END as display_project,
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
    ";
    
    echo "<p><strong>Query:</strong></p>";
    echo "<pre style='background: #f8f9fa; padding: 10px; border-radius: 5px;'>" . htmlspecialchars($query) . "</pre>";
    
    $stmt = $db->prepare($query);
    $stmt->execute([$filterDate]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p><strong>Query Results:</strong></p>";
    echo "<table border='1' style='width: 100%;'>";
    echo "<tr style='background: #f8f9fa;'>";
    echo "<th>Name</th><th>Role</th><th>Status</th><th>Raw Location</th><th>Raw Project</th><th>Display Location</th><th>Display Project</th><th>Check In</th>";
    echo "</tr>";
    
    foreach ($results as $result) {
        echo "<tr>";
        echo "<td>{$result['name']}</td>";
        echo "<td>{$result['role']}</td>";
        echo "<td>{$result['status']}</td>";
        echo "<td style='background: " . ($result['location_display'] ? '#d4edda' : '#f8d7da') . ";'>" . ($result['location_display'] ?: 'NULL') . "</td>";
        echo "<td style='background: " . ($result['project_name'] ? '#d4edda' : '#f8d7da') . ";'>" . ($result['project_name'] ?: 'NULL') . "</td>";
        echo "<td style='font-weight: bold; color: #0066cc;'>{$result['display_location']}</td>";
        echo "<td style='font-weight: bold; color: #cc6600;'>{$result['display_project']}</td>";
        echo "<td>" . ($result['check_in'] ?: 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // 4. Check which view is being used
    echo "<h3>4. View Analysis:</h3>";
    
    $role = $_SESSION['role'] ?? 'user';
    if ($role === 'user') {
        echo "<p>🎯 Using: <strong>handleUserView()</strong> → views/attendance/index.php</p>";
    } else {
        $viewName = ($role === 'owner') ? 'attendance/owner_index' : 'attendance/admin_index';
        echo "<p>🎯 Using: <strong>handleAdminView()</strong> → views/{$viewName}.php</p>";
    }
    
    // 5. Simulate the exact data structure passed to view
    echo "<h3>5. Data Structure Passed to View:</h3>";
    
    if ($role !== 'user') {
        // Group attendance by role
        $groupedAttendance = ['admin' => [], 'user' => []];
        
        foreach ($results as $emp) {
            if ($emp['role'] === 'admin' || $emp['role'] === 'owner') {
                $groupedAttendance['admin'][] = $emp;
            } else {
                $groupedAttendance['user'][] = $emp;
            }
        }
        
        echo "<p><strong>Grouped Attendance Structure:</strong></p>";
        echo "<pre style='background: #f8f9fa; padding: 10px; border-radius: 5px;'>";
        echo "Admin Users: " . count($groupedAttendance['admin']) . "\n";
        echo "Regular Users: " . count($groupedAttendance['user']) . "\n";
        echo "\nSample Admin Record:\n";
        if (!empty($groupedAttendance['admin'])) {
            print_r($groupedAttendance['admin'][0]);
        }
        echo "\nSample User Record:\n";
        if (!empty($groupedAttendance['user'])) {
            print_r($groupedAttendance['user'][0]);
        }
        echo "</pre>";
    }
    
    // 6. Check if projects and settings exist
    echo "<h3>6. Projects and Settings Check:</h3>";
    
    $stmt = $db->query("SELECT COUNT(*) as count FROM projects WHERE status = 'active'");
    $projectCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    echo "<p>Active Projects: {$projectCount}</p>";
    
    $stmt = $db->query("SELECT COUNT(*) as count FROM settings");
    $settingsCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    echo "<p>Settings Records: {$settingsCount}</p>";
    
    if ($projectCount == 0 || $settingsCount == 0) {
        echo "<p>⚠️ Missing projects or settings - location validation may fail</p>";
    }
    
    echo "<h3>✅ Analysis Complete!</h3>";
    
    if (empty($records)) {
        echo "<div style='background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px;'>";
        echo "<h4>🔧 Issue Found: No Attendance Records</h4>";
        echo "<p>The attendance table is empty. Users need to clock in to generate location data.</p>";
        echo "</div>";
    } else {
        $hasLocationData = false;
        foreach ($records as $record) {
            if ($record['location_display'] || $record['project_name']) {
                $hasLocationData = true;
                break;
            }
        }
        
        if (!$hasLocationData) {
            echo "<div style='background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px;'>";
            echo "<h4>🔧 Issue Found: No Location Data in Records</h4>";
            echo "<p>Attendance records exist but location_display and project_name are NULL.</p>";
            echo "<p>This means the clock-in process is not saving location data properly.</p>";
            echo "</div>";
        } else {
            echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px;'>";
            echo "<h4>✅ Location Data Found!</h4>";
            echo "<p>The database has proper location data. Check the frontend display logic.</p>";
            echo "</div>";
        }
    }
    
    echo "<p><strong>Next Steps:</strong></p>";
    echo "<ul>";
    echo "<li><a href='/ergon-site/attendance' target='_blank'>Check Attendance Page</a></li>";
    echo "<li><a href='/ergon-site/attendance/clock' target='_blank'>Test Clock In/Out</a></li>";
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>