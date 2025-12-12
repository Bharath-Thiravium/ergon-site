<?php
session_start();
require_once __DIR__ . '/app/config/database.php';

try {
    $db = Database::connect();
    $today = date('Y-m-d');
    
    echo "<h2>Fixing Attendance Record</h2>";
    
    if (!isset($_SESSION['user_id'])) {
        echo "❌ Please login first";
        exit;
    }
    
    $userId = $_SESSION['user_id'];
    
    // Get the attendance record
    $stmt = $db->prepare("SELECT * FROM attendance WHERE user_id = ? AND DATE(check_in) = ?");
    $stmt->execute([$userId, $today]);
    $attendance = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$attendance) {
        echo "❌ No attendance record found for today";
        exit;
    }
    
    echo "Current Record ID: {$attendance['id']}<br>";
    echo "Current project_id: {$attendance['project_id']}<br>";
    echo "Current location_display: " . ($attendance['location_display'] ?: 'NULL') . "<br>";
    echo "Current project_name: " . ($attendance['project_name'] ?: 'NULL') . "<br><br>";
    
    // Update with correct Sector 7 data
    $stmt = $db->prepare("
        UPDATE attendance 
        SET 
            project_id = 22,
            location_display = 'Madurai',
            project_name = 'Sector 7',
            location_name = 'Madurai'
        WHERE id = ?
    ");
    
    $result = $stmt->execute([$attendance['id']]);
    
    if ($result) {
        echo "✅ <strong>Attendance record updated successfully!</strong><br><br>";
        
        // Verify the update
        $stmt = $db->prepare("SELECT project_id, location_display, project_name, location_name FROM attendance WHERE id = ?");
        $stmt->execute([$attendance['id']]);
        $updated = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo "<strong>Updated Values:</strong><br>";
        echo "project_id: {$updated['project_id']}<br>";
        echo "location_display: {$updated['location_display']}<br>";
        echo "project_name: {$updated['project_name']}<br>";
        echo "location_name: {$updated['location_name']}<br><br>";
        
        echo "🎉 <strong>Now refresh your attendance page - Location should show 'Madurai' and Project should show 'Sector 7'</strong>";
        
    } else {
        echo "❌ Failed to update attendance record";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h2 { color: #333; }
</style>