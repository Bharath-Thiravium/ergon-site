<?php
session_start();
require_once __DIR__ . '/app/config/database.php';

// Set test session for admin user
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'admin';
$_SESSION['user_name'] = 'Test Admin';

try {
    $db = Database::connect();
    
    echo "<h2>Testing Attendance Data Structure</h2>";
    
    // Test the exact query from AttendanceController
    $filterDate = date('Y-m-d');
    $roleFilter = "u.role = 'user'";
    
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
            COALESCE(a.location_display, '---') as location_display,
            COALESCE(a.project_name, '----') as project_name,
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
    $employeeAttendance = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>Query Results for Date: $filterDate</h3>";
    echo "<pre>";
    print_r($employeeAttendance);
    echo "</pre>";
    
    // Test attendance table structure
    echo "<h3>Attendance Table Structure</h3>";
    $stmt = $db->query("DESCRIBE attendance");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<pre>";
    print_r($columns);
    echo "</pre>";
    
    // Test sample attendance records
    echo "<h3>Sample Attendance Records</h3>";
    $stmt = $db->query("SELECT * FROM attendance ORDER BY created_at DESC LIMIT 5");
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<pre>";
    print_r($records);
    echo "</pre>";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>