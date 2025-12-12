<?php
require_once __DIR__ . '/app/config/database.php';

try {
    $db = Database::connect();
    
    echo "<h2>Fixing Attendance Location Data</h2>";
    
    // Get today's date
    $today = date('Y-m-d');
    
    // Check current attendance records for today
    $stmt = $db->prepare("
        SELECT 
            a.id,
            a.user_id,
            u.name,
            a.check_in,
            a.check_out,
            a.location_name,
            a.location_display,
            a.project_name
        FROM attendance a 
        LEFT JOIN users u ON a.user_id = u.id 
        WHERE DATE(a.check_in) = ?
    ");
    $stmt->execute([$today]);
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($records)) {
        echo "<p>No attendance records found for today. Creating a test record...</p>";
        
        // Get current user from session
        session_start();
        if (isset($_SESSION['user_id'])) {
            $userId = $_SESSION['user_id'];
            $checkInTime = $today . ' 08:30:00';
            
            // Insert a test attendance record with proper location data
            $stmt = $db->prepare("
                INSERT INTO attendance 
                (user_id, check_in, location_name, location_display, project_name, created_at) 
                VALUES (?, ?, ?, ?, ?, NOW())
            ");
            $result = $stmt->execute([
                $userId,
                $checkInTime,
                'ERGON Company',
                'ERGON Company',
                '----'
            ]);
            
            if ($result) {
                echo "<p>✅ Test attendance record created successfully!</p>";
                echo "<p>User ID: $userId</p>";
                echo "<p>Check In: $checkInTime</p>";
                echo "<p>Location: ERGON Company</p>";
            } else {
                echo "<p>❌ Failed to create test record</p>";
            }
        } else {
            echo "<p>❌ No user session found. Please login first.</p>";
        }
    } else {
        echo "<p>Found " . count($records) . " attendance record(s) for today. Updating location data...</p>";
        
        foreach ($records as $record) {
            // Update records that have null or empty location data
            if (empty($record['location_display']) || $record['location_display'] === '---') {
                $stmt = $db->prepare("
                    UPDATE attendance 
                    SET 
                        location_name = 'ERGON Company',
                        location_display = 'ERGON Company',
                        project_name = COALESCE(project_name, '----')
                    WHERE id = ?
                ");
                $result = $stmt->execute([$record['id']]);
                
                if ($result) {
                    echo "<p>✅ Updated record for " . htmlspecialchars($record['name']) . " (ID: {$record['id']})</p>";
                } else {
                    echo "<p>❌ Failed to update record for " . htmlspecialchars($record['name']) . "</p>";
                }
            } else {
                echo "<p>ℹ️ Record for " . htmlspecialchars($record['name']) . " already has location data: " . htmlspecialchars($record['location_display']) . "</p>";
            }
        }
    }
    
    // Verify the fix
    echo "<h3>Verification - Current Records:</h3>";
    $stmt = $db->prepare("
        SELECT 
            a.id,
            a.user_id,
            u.name,
            a.check_in,
            a.check_out,
            a.location_name,
            a.location_display,
            a.project_name
        FROM attendance a 
        LEFT JOIN users u ON a.user_id = u.id 
        WHERE DATE(a.check_in) = ?
    ");
    $stmt->execute([$today]);
    $updatedRecords = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($updatedRecords)) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>User</th><th>Check In</th><th>Check Out</th><th>Location Display</th><th>Project Name</th></tr>";
        foreach ($updatedRecords as $record) {
            echo "<tr>";
            echo "<td>" . $record['id'] . "</td>";
            echo "<td>" . htmlspecialchars($record['name']) . "</td>";
            echo "<td>" . $record['check_in'] . "</td>";
            echo "<td>" . ($record['check_out'] ?: 'Not set') . "</td>";
            echo "<td>" . ($record['location_display'] ?: 'NULL') . "</td>";
            echo "<td>" . ($record['project_name'] ?: 'NULL') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    echo "<p><strong>✅ Fix completed! Please refresh the attendance page to see the updated data.</strong></p>";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
table { margin: 10px 0; }
th, td { padding: 8px; text-align: left; }
th { background-color: #f2f2f2; }
</style>