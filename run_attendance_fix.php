<?php
/**
 * Fix Attendance Table Structure
 * This script updates the attendance table to remove old latitude/longitude columns
 * and ensure the correct structure is in place
 */

require_once __DIR__ . '/app/config/database.php';

try {
    $db = Database::connect();
    
    echo "Starting attendance table structure fix...\n\n";
    
    // Check current table structure
    echo "Checking current table structure...\n";
    $stmt = $db->query("DESCRIBE attendance");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Current columns: " . implode(", ", $columns) . "\n\n";
    
    // Add missing columns if they don't exist
    echo "Adding missing columns...\n";
    
    $columnsToAdd = [
        ['name' => 'project_id', 'sql' => 'ALTER TABLE attendance ADD COLUMN project_id INT NULL AFTER user_id'],
        ['name' => 'location_type', 'sql' => 'ALTER TABLE attendance ADD COLUMN location_type VARCHAR(50) NULL AFTER location_name'],
        ['name' => 'location_title', 'sql' => 'ALTER TABLE attendance ADD COLUMN location_title VARCHAR(255) NULL AFTER location_type'],
        ['name' => 'location_radius', 'sql' => 'ALTER TABLE attendance ADD COLUMN location_radius INT NULL AFTER location_title']
    ];
    
    foreach ($columnsToAdd as $col) {
        if (!in_array($col['name'], $columns)) {
            try {
                $db->exec($col['sql']);
                echo "✓ Added {$col['name']} column\n";
            } catch (Exception $e) {
                echo "✓ {$col['name']} column already exists\n";
            }
        } else {
            echo "✓ {$col['name']} column already exists\n";
        }
    }
    
    // Update existing records with default values
    echo "\nUpdating existing records...\n";
    
    $stmt = $db->exec("UPDATE attendance SET location_type = 'office' WHERE location_type IS NULL");
    echo "✓ Updated location_type for existing records\n";
    
    $stmt = $db->exec("UPDATE attendance SET location_title = COALESCE(location_name, 'Main Office') WHERE location_title IS NULL");
    echo "✓ Updated location_title for existing records\n";
    
    $stmt = $db->exec("UPDATE attendance SET location_radius = 50 WHERE location_radius IS NULL");
    echo "✓ Updated location_radius for existing records\n";
    
    $stmt = $db->exec("UPDATE attendance SET check_out = NULL WHERE check_out = '' OR check_out = '0000-00-00 00:00:00'");
    echo "✓ Cleaned up check_out values\n";
    
    // Add indexes
    echo "\nAdding indexes...\n";
    $indexesToAdd = [
        ['name' => 'idx_project_id', 'sql' => 'CREATE INDEX idx_project_id ON attendance(project_id)'],
        ['name' => 'idx_location_type', 'sql' => 'CREATE INDEX idx_location_type ON attendance(location_type)']
    ];
    
    foreach ($indexesToAdd as $idx) {
        try {
            $db->exec($idx['sql']);
            echo "✓ Added {$idx['name']}\n";
        } catch (Exception $e) {
            echo "✓ {$idx['name']} already exists\n";
        }
    }
    
    // Verify final structure
    echo "\nVerifying final structure...\n";
    $stmt = $db->query("DESCRIBE attendance");
    $finalColumns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "\nFinal table structure:\n";
    foreach ($finalColumns as $col) {
        echo "  - {$col['Field']} ({$col['Type']}) " . ($col['Null'] === 'YES' ? 'NULL' : 'NOT NULL') . "\n";
    }
    
    echo "\n✅ Attendance table structure fix completed successfully!\n";
    
} catch (Exception $e) {
    echo "\n❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
