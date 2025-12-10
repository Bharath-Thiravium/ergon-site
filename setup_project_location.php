<?php
// Setup script to add place field to projects table
require_once __DIR__ . '/app/config/database.php';

try {
    $db = Database::connect();
    
    // Add place column if it doesn't exist
    $db->exec("ALTER TABLE projects ADD COLUMN place VARCHAR(255) NULL AFTER description");
    echo "✅ Place column added successfully\n";
    
    // Update existing projects with default place names if they have coordinates
    $stmt = $db->prepare("UPDATE projects SET place = CONCAT('Location (', ROUND(latitude, 4), ', ', ROUND(longitude, 4), ')') WHERE latitude IS NOT NULL AND longitude IS NOT NULL AND (place IS NULL OR place = '')");
    $result = $stmt->execute();
    
    if ($result) {
        echo "✅ Existing projects updated with default place names\n";
    }
    
    echo "✅ Project location setup completed successfully!\n";
    
} catch (Exception $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "ℹ️ Place column already exists\n";
    } else {
        echo "❌ Error: " . $e->getMessage() . "\n";
    }
}
?>