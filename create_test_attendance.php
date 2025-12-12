<?php
session_start();
require_once __DIR__ . '/app/config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "Please login first to create test attendance.";
    exit;
}

try {
    $db = Database::connect();
    
    $userId = $_SESSION['user_id'];
    $today = date('Y-m-d');
    $checkInTime = $today . ' 08:30:00';
    
    // Check if attendance already exists for today
    $stmt = $db->prepare("SELECT id FROM attendance WHERE user_id = ? AND DATE(check_in) = ?");
    $stmt->execute([$userId, $today]);
    $existing = $stmt->fetch();
    
    if ($existing) {
        echo "Attendance record already exists for today. Updating location data...<br>";
        
        // Update existing record with proper location data
        $stmt = $db->prepare("UPDATE attendance SET location_display = 'ERGON Company', location_name = 'ERGON Company', project_name = '----' WHERE user_id = ? AND DATE(check_in) = ?");
        $result = $stmt->execute([$userId, $today]);
        
        if ($result) {
            echo "✅ Updated existing attendance record with location data!<br>";
        } else {
            echo "❌ Failed to update existing record.<br>";
        }
    } else {
        echo "Creating new attendance record for today...<br>";
        
        // Create new attendance record
        $stmt = $db->prepare("INSERT INTO attendance (user_id, check_in, location_name, location_display, project_name, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        $result = $stmt->execute([
            $userId,
            $checkInTime,
            'ERGON Company',
            'ERGON Company',
            '----'
        ]);
        
        if ($result) {
            echo "✅ Created new attendance record successfully!<br>";
            echo "User ID: $userId<br>";
            echo "Check In: $checkInTime<br>";
            echo "Location: ERGON Company<br>";
        } else {
            echo "❌ Failed to create attendance record.<br>";
        }
    }
    
    // Verify the record
    $stmt = $db->prepare("SELECT * FROM attendance WHERE user_id = ? AND DATE(check_in) = ?");
    $stmt->execute([$userId, $today]);
    $record = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($record) {
        echo "<br><strong>Current Record:</strong><br>";
        echo "ID: " . $record['id'] . "<br>";
        echo "Check In: " . $record['check_in'] . "<br>";
        echo "Check Out: " . ($record['check_out'] ?: 'Not set') . "<br>";
        echo "Location Name: " . ($record['location_name'] ?: 'NULL') . "<br>";
        echo "Location Display: " . ($record['location_display'] ?: 'NULL') . "<br>";
        echo "Project Name: " . ($record['project_name'] ?: 'NULL') . "<br>";
        
        echo "<br><strong>✅ Test attendance record is ready! Please refresh the attendance page.</strong>";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>