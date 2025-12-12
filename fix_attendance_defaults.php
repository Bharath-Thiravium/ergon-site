<?php
require_once __DIR__ . '/app/config/database.php';

try {
    $db = Database::connect();
    
    echo "<h2>Fixing Attendance Table Default Values</h2>";
    
    // Update existing records with null or empty location_display to have '---'
    $stmt = $db->prepare("UPDATE attendance SET location_display = '---' WHERE location_display IS NULL OR location_display = ''");
    $result1 = $stmt->execute();
    $affected1 = $stmt->rowCount();
    
    // Update existing records with null or empty project_name to have '----'
    $stmt = $db->prepare("UPDATE attendance SET project_name = '----' WHERE project_name IS NULL OR project_name = ''");
    $result2 = $stmt->execute();
    $affected2 = $stmt->rowCount();
    
    echo "<p>✅ Updated {$affected1} records with default location_display value ('---')</p>";
    echo "<p>✅ Updated {$affected2} records with default project_name value ('----')</p>";
    
    // Check current attendance records
    echo "<h3>Current Attendance Records:</h3>";
    $stmt = $db->query("SELECT id, user_id, check_in, location_display, project_name FROM attendance ORDER BY created_at DESC LIMIT 10");
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($records)) {
        echo "<p>No attendance records found.</p>";
    } else {
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr style='background-color: #f2f2f2;'><th style='padding: 8px;'>ID</th><th style='padding: 8px;'>User ID</th><th style='padding: 8px;'>Check In</th><th style='padding: 8px;'>Location Display</th><th style='padding: 8px;'>Project Name</th></tr>";
        
        foreach ($records as $record) {
            echo "<tr>";
            echo "<td style='padding: 8px;'>{$record['id']}</td>";
            echo "<td style='padding: 8px;'>{$record['user_id']}</td>";
            echo "<td style='padding: 8px;'>{$record['check_in']}</td>";
            echo "<td style='padding: 8px;'>{$record['location_display']}</td>";
            echo "<td style='padding: 8px;'>{$record['project_name']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    echo "<p><strong>✅ Default values fixed successfully!</strong></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>