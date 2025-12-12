<?php
require_once __DIR__ . '/app/config/database.php';

try {
    $db = Database::connect();
    
    echo "Direct fix for attendance table...\n";
    
    // Drop and recreate the table with proper structure
    $db->exec("DROP TABLE IF EXISTS attendance_backup");
    $db->exec("CREATE TABLE attendance_backup AS SELECT * FROM attendance");
    echo "✓ Created backup\n";
    
    $db->exec("DROP TABLE attendance");
    echo "✓ Dropped original table\n";
    
    $createSQL = "CREATE TABLE attendance (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        project_id INT NULL,
        check_in DATETIME NOT NULL,
        check_out DATETIME NULL,
        location_name VARCHAR(255) DEFAULT 'Office',
        location_display VARCHAR(255) NULL,
        project_name VARCHAR(255) NULL,
        status VARCHAR(20) DEFAULT 'present',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_user_id (user_id),
        INDEX idx_check_in_date (check_in)
    )";
    
    $db->exec($createSQL);
    echo "✓ Created new table\n";
    
    // Copy data back, fixing datetime issues
    $db->exec("INSERT INTO attendance (id, user_id, project_id, check_in, check_out, location_name, location_display, project_name, status, created_at, updated_at)
               SELECT id, user_id, project_id, check_in, 
                      CASE WHEN check_out = '' OR check_out = '0000-00-00 00:00:00' THEN NULL ELSE check_out END,
                      location_name, location_display, project_name, status, created_at, updated_at
               FROM attendance_backup");
    
    echo "✓ Restored data with fixes\n";
    
    $db->exec("DROP TABLE attendance_backup");
    echo "✓ Cleaned up backup\n";
    
    echo "Attendance table fixed successfully!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>