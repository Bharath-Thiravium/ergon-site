<?php
// Web-based setup script to add place field to projects table
require_once __DIR__ . '/app/config/database.php';

echo "<h2>Project Location Setup</h2>";

try {
    $db = Database::connect();
    
    // Add place column if it doesn't exist
    try {
        $db->exec("ALTER TABLE projects ADD COLUMN place VARCHAR(255) NULL AFTER description");
        echo "<p>✅ Place column added successfully</p>";
    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
            echo "<p>ℹ️ Place column already exists</p>";
        } else {
            throw $e;
        }
    }
    
    // Update existing projects with default place names if they have coordinates
    $stmt = $db->prepare("UPDATE projects SET place = CONCAT('Location (', ROUND(latitude, 4), ', ', ROUND(longitude, 4), ')') WHERE latitude IS NOT NULL AND longitude IS NOT NULL AND (place IS NULL OR place = '')");
    $result = $stmt->execute();
    
    if ($result) {
        echo "<p>✅ Existing projects updated with default place names</p>";
    }
    
    echo "<p><strong>✅ Project location setup completed successfully!</strong></p>";
    echo "<p><a href='/ergon-site/project-management'>Go to Project Management</a></p>";
    
} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
}
?>