<?php
require_once __DIR__ . '/app/config/database.php';

try {
    $db = Database::connect();
    
    echo "Starting attendance location migration...\n";
    
    // Add new columns if they don't exist
    $alterQueries = [
        "ALTER TABLE attendance ADD COLUMN location_display VARCHAR(255) NULL AFTER location_name",
        "ALTER TABLE attendance ADD COLUMN project_name VARCHAR(255) NULL AFTER project_id"
    ];
    
    foreach ($alterQueries as $query) {
        try {
            $db->exec($query);
            echo "✓ Added column successfully\n";
        } catch (Exception $e) {
            echo "- Column already exists or error: " . $e->getMessage() . "\n";
        }
    }
    
    // Update existing records with default values
    $updateQuery = "
        UPDATE attendance 
        SET 
            location_display = CASE 
                WHEN project_id IS NOT NULL THEN 
                    COALESCE(
                        (SELECT CONCAT(name, ' - ', COALESCE(place, 'Site')) FROM projects WHERE id = attendance.project_id),
                        'Project Site'
                    )
                ELSE 
                    COALESCE(
                        (SELECT company_name FROM settings LIMIT 1), 
                        'Company Office'
                    )
            END,
            project_name = CASE 
                WHEN project_id IS NOT NULL THEN 
                    (SELECT name FROM projects WHERE id = attendance.project_id)
                ELSE NULL
            END
        WHERE location_display IS NULL OR project_name IS NULL
    ";
    
    $stmt = $db->prepare($updateQuery);
    $result = $stmt->execute();
    $affected = $stmt->rowCount();
    
    echo "✓ Updated {$affected} attendance records with location data\n";
    echo "Migration completed successfully!\n";
    
} catch (Exception $e) {
    echo "Migration error: " . $e->getMessage() . "\n";
    exit(1);
}
?>