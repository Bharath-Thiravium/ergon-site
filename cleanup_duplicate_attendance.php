<?php
require_once __DIR__ . '/app/config/database.php';

try {
    $db = Database::connect();
    
    echo "Cleaning up duplicate attendance records...\n";
    
    // Find and remove duplicate attendance records (keep the latest one for each user/date)
    $sql = "
    DELETE a1 FROM attendance a1
    INNER JOIN attendance a2 
    WHERE a1.user_id = a2.user_id 
    AND DATE(a1.check_in) = DATE(a2.check_in)
    AND a1.id < a2.id
    ";
    
    $stmt = $db->prepare($sql);
    $result = $stmt->execute();
    $deletedCount = $stmt->rowCount();
    
    echo "Deleted {$deletedCount} duplicate attendance records.\n";
    echo "Cleanup completed successfully!\n";
    
} catch (Exception $e) {
    echo "Error during cleanup: " . $e->getMessage() . "\n";
}
?>