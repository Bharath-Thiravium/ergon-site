<?php
session_start();
require_once __DIR__ . '/app/config/database.php';

try {
    $db = Database::connect();
    
    echo "<h2>Fix Attendance Table Columns</h2>";
    
    // Check current table structure
    echo "<h3>1. Current Table Structure:</h3>";
    $stmt = $db->query("SHOW COLUMNS FROM attendance");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $existingColumns = array_column($columns, 'Field');
    
    foreach ($columns as $col) {
        echo "<p>{$col['Field']} - {$col['Type']}</p>";
    }
    
    // Add missing columns
    echo "<h3>2. Adding Missing Columns:</h3>";
    
    $columnsToAdd = [
        'location_display' => "ALTER TABLE attendance ADD COLUMN location_display VARCHAR(255) NULL AFTER location_name",
        'project_name' => "ALTER TABLE attendance ADD COLUMN project_name VARCHAR(255) NULL AFTER project_id"
    ];
    
    foreach ($columnsToAdd as $columnName => $sql) {
        if (!in_array($columnName, $existingColumns)) {
            try {
                $db->exec($sql);
                echo "<p>✅ Added column: $columnName</p>";
            } catch (Exception $e) {
                echo "<p>❌ Failed to add $columnName: " . $e->getMessage() . "</p>";
            }
        } else {
            echo "<p>⚠️ Column $columnName already exists</p>";
        }
    }
    
    // Update existing records with proper location and project data
    echo "<h3>3. Updating Existing Records:</h3>";
    
    // Update records that have project_id but missing project_name/location_display
    $stmt = $db->prepare("
        UPDATE attendance a 
        LEFT JOIN projects p ON a.project_id = p.id 
        SET 
            a.location_display = CASE 
                WHEN p.place IS NOT NULL AND p.place != '' THEN p.place 
                WHEN a.check_in IS NOT NULL THEN 'ERGON Company' 
                ELSE '---' 
            END,
            a.project_name = CASE 
                WHEN p.name IS NOT NULL AND p.name != '' THEN p.name 
                WHEN a.check_in IS NOT NULL THEN '----' 
                ELSE '----' 
            END
        WHERE a.location_display IS NULL OR a.project_name IS NULL
    ");
    
    if ($stmt->execute()) {
        $updatedRows = $stmt->rowCount();
        echo "<p>✅ Updated $updatedRows existing records with project/location data</p>";
    } else {
        echo "<p>❌ Failed to update existing records</p>";
    }
    
    // Update records without project_id to have default values
    $stmt = $db->prepare("
        UPDATE attendance 
        SET 
            location_display = CASE 
                WHEN check_in IS NOT NULL THEN 'ERGON Company' 
                ELSE '---' 
            END,
            project_name = CASE 
                WHEN check_in IS NOT NULL THEN '----' 
                ELSE '----' 
            END
        WHERE project_id IS NULL AND (location_display IS NULL OR project_name IS NULL)
    ");
    
    if ($stmt->execute()) {
        $updatedRows = $stmt->rowCount();
        echo "<p>✅ Updated $updatedRows records without project_id</p>";
    }
    
    // Test the updated structure
    echo "<h3>4. Testing Updated Structure:</h3>";
    $stmt = $db->query("SELECT id, user_id, check_in, location_name, location_display, project_name FROM attendance ORDER BY created_at DESC LIMIT 5");
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>User ID</th><th>Check In</th><th>Location Name</th><th>Location Display</th><th>Project Name</th></tr>";
    foreach ($records as $record) {
        echo "<tr>";
        echo "<td>{$record['id']}</td>";
        echo "<td>{$record['user_id']}</td>";
        echo "<td>" . ($record['check_in'] ?: 'NULL') . "</td>";
        echo "<td>" . ($record['location_name'] ?: 'NULL') . "</td>";
        echo "<td>" . ($record['location_display'] ?: 'NULL') . "</td>";
        echo "<td>" . ($record['project_name'] ?: 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h3>✅ Fix Complete!</h3>";
    echo "<p>The attendance table now has the required columns. Check the attendance page: <a href='/ergon-site/attendance' target='_blank'>Attendance Page</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>