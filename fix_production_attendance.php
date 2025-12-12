<?php
require_once __DIR__ . '/app/config/database.php';

try {
    $db = Database::connect();
    
    // Add missing columns if they don't exist
    try {
        $db->exec("ALTER TABLE attendance ADD COLUMN location_display VARCHAR(255) NULL AFTER location_name");
        echo "✅ Added location_display column\n";
    } catch (Exception $e) {
        echo "⚠️ location_display column exists or error: " . $e->getMessage() . "\n";
    }
    
    try {
        $db->exec("ALTER TABLE attendance ADD COLUMN project_name VARCHAR(255) NULL AFTER project_id");
        echo "✅ Added project_name column\n";
    } catch (Exception $e) {
        echo "⚠️ project_name column exists or error: " . $e->getMessage() . "\n";
    }
    
    // Update all existing records with proper location and project data
    $stmt = $db->prepare("
        UPDATE attendance a 
        LEFT JOIN projects p ON a.project_id = p.id 
        SET 
            a.location_display = CASE 
                WHEN p.location_title IS NOT NULL AND p.location_title != '' THEN p.location_title
                WHEN p.name IS NOT NULL AND p.name != '' THEN CONCAT(p.name, ' Site')
                WHEN a.location_name IS NOT NULL AND a.location_name != '' AND a.location_name != 'Office' THEN a.location_name
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
        echo "✅ Updated " . $stmt->rowCount() . " attendance records\n";
    }
    
    echo "✅ Production attendance table fixed!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>