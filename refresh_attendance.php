<?php
session_start();
require_once __DIR__ . '/app/config/database.php';

try {
    $db = Database::connect();
    $today = date('Y-m-d');
    $userId = $_SESSION['user_id'];
    
    echo "<h2>Refreshing Attendance Data</h2>";
    
    // Force refresh the user attendance query with the updated JOIN
    $stmt = $db->prepare("
        SELECT 
            a.*, 
            u.name as user_name, 
            CASE 
                WHEN p.place IS NOT NULL AND p.place != '' THEN p.place
                WHEN a.check_in IS NOT NULL THEN 'ERGON Company'
                ELSE '---'
            END as location_display, 
            CASE 
                WHEN p.name IS NOT NULL AND p.name != '' THEN p.name
                WHEN a.check_in IS NOT NULL THEN '----'
                ELSE '----'
            END as project_name, 
            COALESCE(d.name, 'Not Assigned') as department, 
            CASE 
                WHEN a.check_in IS NOT NULL AND a.check_out IS NOT NULL THEN 
                    CONCAT(FLOOR(TIMESTAMPDIFF(MINUTE, a.check_in, a.check_out) / 60), 'h ', 
                           MOD(TIMESTAMPDIFF(MINUTE, a.check_in, a.check_out), 60), 'm')
                ELSE '0h 0m'
            END as working_hours 
        FROM attendance a 
        LEFT JOIN users u ON a.user_id = u.id 
        LEFT JOIN departments d ON u.department_id = d.id 
        LEFT JOIN projects p ON a.project_id = p.id 
        WHERE a.user_id = ? AND DATE(a.check_in) = ?
        ORDER BY a.check_in DESC
    ");
    
    $stmt->execute([$userId, $today]);
    $attendance = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($attendance)) {
        echo "<h3>✅ Updated Attendance Data:</h3>";
        echo "<table border='1'>";
        echo "<tr><th>Field</th><th>Value</th></tr>";
        
        $record = $attendance[0];
        echo "<tr><td>User Name</td><td>{$record['user_name']}</td></tr>";
        echo "<tr><td>Check In</td><td>{$record['check_in']}</td></tr>";
        echo "<tr><td>Check Out</td><td>" . ($record['check_out'] ?: 'Not set') . "</td></tr>";
        echo "<tr><td><strong>Location Display</strong></td><td><strong>{$record['location_display']}</strong></td></tr>";
        echo "<tr><td><strong>Project Name</strong></td><td><strong>{$record['project_name']}</strong></td></tr>";
        echo "<tr><td>Working Hours</td><td>{$record['working_hours']}</td></tr>";
        echo "</table>";
        
        echo "<br><h3>🎉 Success!</h3>";
        echo "<p><strong>Location:</strong> {$record['location_display']}</p>";
        echo "<p><strong>Project:</strong> {$record['project_name']}</p>";
        echo "<p><a href='/ergon-site/attendance' class='btn'>Go to Attendance Page</a></p>";
        
    } else {
        echo "❌ No attendance records found for today";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
table { border-collapse: collapse; margin: 10px 0; }
th, td { padding: 8px; text-align: left; border: 1px solid #ddd; }
th { background-color: #f2f2f2; }
.btn { 
    display: inline-block; 
    padding: 10px 20px; 
    background: #007cba; 
    color: white; 
    text-decoration: none; 
    border-radius: 4px; 
    margin-top: 10px;
}
</style>