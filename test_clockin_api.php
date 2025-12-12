<?php
session_start();
require_once __DIR__ . '/app/config/database.php';

// Mock user session for testing
$_SESSION['user_id'] = 1;
$_SESSION['user_name'] = 'Test User';

echo "<h2>Clock-In API Test</h2>\n";

// Test coordinates near ERGON project (9.95325800, 78.12721200)
$testLat = 9.95325800;
$testLng = 78.12721200;

echo "<h3>Testing Clock-In with Project Location</h3>\n";
echo "Test coordinates: ({$testLat}, {$testLng})<br>\n";

// Simulate POST request to clock-in
$_POST = [
    'type' => 'in',
    'latitude' => $testLat,
    'longitude' => $testLng
];
$_SERVER['REQUEST_METHOD'] = 'POST';

// Include the attendance controller
require_once __DIR__ . '/app/controllers/AttendanceController.php';

try {
    $controller = new AttendanceController();
    
    // Capture output
    ob_start();
    $controller->clock();
    $output = ob_get_clean();
    
    echo "<h3>API Response:</h3>\n";
    echo "<pre>" . htmlspecialchars($output) . "</pre>\n";
    
    // Check if it's JSON
    $response = json_decode($output, true);
    if ($response) {
        if ($response['success']) {
            echo "<div style='color: green; font-weight: bold;'>✅ Clock-in successful!</div>\n";
            echo "<div>Message: " . htmlspecialchars($response['message']) . "</div>\n";
        } else {
            echo "<div style='color: red; font-weight: bold;'>❌ Clock-in failed!</div>\n";
            echo "<div>Error: " . htmlspecialchars($response['error']) . "</div>\n";
        }
    }
    
} catch (Exception $e) {
    echo "<div style='color: red;'>Error: " . htmlspecialchars($e->getMessage()) . "</div>\n";
}

// Check attendance record
try {
    $db = Database::connect();
    $stmt = $db->prepare("SELECT * FROM attendance WHERE user_id = ? ORDER BY created_at DESC LIMIT 1");
    $stmt->execute([1]);
    $record = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($record) {
        echo "<h3>Latest Attendance Record:</h3>\n";
        echo "<table border='1' style='border-collapse: collapse;'>\n";
        foreach ($record as $key => $value) {
            echo "<tr><td><strong>{$key}</strong></td><td>" . htmlspecialchars($value ?? 'NULL') . "</td></tr>\n";
        }
        echo "</table>\n";
        
        if ($record['location_display'] && $record['project_name']) {
            echo "<div style='color: green; font-weight: bold;'>✅ Project-based location data stored correctly!</div>\n";
            echo "<div>Location: " . htmlspecialchars($record['location_display']) . "</div>\n";
            echo "<div>Project: " . htmlspecialchars($record['project_name']) . "</div>\n";
        }
    } else {
        echo "<div style='color: orange;'>No attendance record found</div>\n";
    }
    
} catch (Exception $e) {
    echo "<div style='color: red;'>Database error: " . htmlspecialchars($e->getMessage()) . "</div>\n";
}
?>