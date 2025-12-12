<?php
require_once __DIR__ . '/app/config/database.php';

try {
    $db = Database::connect();
    
    // Find all users with Nelson in name
    echo "=== Users with Nelson in name ===\n";
    $stmt = $db->prepare("SELECT id, name, role, status FROM users WHERE name LIKE '%Nelson%'");
    $stmt->execute();
    $users = $stmt->fetchAll();
    
    foreach ($users as $user) {
        echo "ID: {$user['id']}, Name: {$user['name']}, Role: {$user['role']}, Status: {$user['status']}\n";
    }
    
    // Find all attendance records for today
    echo "\n=== Today's attendance records ===\n";
    $stmt = $db->prepare("
        SELECT a.id, a.user_id, u.name, a.check_in, a.check_out, a.location_name, a.location_display, a.project_name
        FROM attendance a 
        JOIN users u ON a.user_id = u.id 
        WHERE DATE(a.check_in) = CURDATE()
        ORDER BY a.check_in DESC
    ");
    $stmt->execute();
    $records = $stmt->fetchAll();
    
    foreach ($records as $record) {
        echo "ID: {$record['id']}, User: {$record['name']}, Check-in: {$record['check_in']}, Location: " . ($record['location_display'] ?: 'NULL') . ", Project: " . ($record['project_name'] ?: 'NULL') . "\n";
    }
    
    // Test the exact controller query
    echo "\n=== Testing controller query ===\n";
    $filterDate = date('Y-m-d');
    $roleFilter = "u.role IN ('admin', 'user', 'owner')";
    
    $stmt = $db->prepare("
        SELECT 
            u.name,
            u.role,
            a.check_in,
            a.location_display,
            a.project_name,
            CASE 
                WHEN a.location_display IS NOT NULL AND a.location_display != '' THEN a.location_display
                WHEN a.location_name IS NOT NULL AND a.location_name != '' AND a.location_name != 'Office' THEN a.location_name
                WHEN a.check_in IS NOT NULL THEN 'ERGON Company'
                ELSE '---'
            END as final_location,
            CASE 
                WHEN a.project_name IS NOT NULL AND a.project_name != '' THEN a.project_name
                WHEN a.check_in IS NOT NULL THEN '----'
                ELSE '----'
            END as final_project
        FROM users u
        LEFT JOIN attendance a ON u.id = a.user_id AND DATE(a.check_in) = ?
        WHERE $roleFilter AND u.status = 'active' AND u.name LIKE '%Nelson%'
    ");
    $stmt->execute([$filterDate]);
    $results = $stmt->fetchAll();
    
    foreach ($results as $result) {
        echo "Name: {$result['name']}, Role: {$result['role']}, Check-in: " . ($result['check_in'] ?: 'NULL') . "\n";
        echo "Raw location_display: " . ($result['location_display'] ?: 'NULL') . "\n";
        echo "Raw project_name: " . ($result['project_name'] ?: 'NULL') . "\n";
        echo "Final location: {$result['final_location']}\n";
        echo "Final project: {$result['final_project']}\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>