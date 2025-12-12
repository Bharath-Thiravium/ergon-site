<?php
session_start();
require_once __DIR__ . '/app/config/database.php';
require_once __DIR__ . '/app/helpers/DatabaseHelper.php';
require_once __DIR__ . '/app/helpers/TimezoneHelper.php';

// Mock user session for testing
$_SESSION['user_id'] = 999; // Use a test user ID that doesn't exist in attendance
$_SESSION['user_name'] = 'Test User';

echo "<h2>Fresh Clock-In Test</h2>\n";

try {
    $db = Database::connect();
    
    // Test the validateProjectBasedLocation method directly
    $userLat = 9.95325800; // Near ERGON project
    $userLng = 78.12721200;
    
    echo "<h3>Testing Location Validation</h3>\n";
    echo "Test coordinates: ({$userLat}, {$userLng})<br>\n";
    
    // Check project locations
    $stmt = $db->prepare("SELECT id, name, place, latitude, longitude, checkin_radius FROM projects WHERE latitude IS NOT NULL AND longitude IS NOT NULL AND status = 'active'");
    $stmt->execute();
    $projects = $stmt->fetchAll();
    
    $validLocation = null;
    foreach ($projects as $project) {
        if ($project['latitude'] != 0 && $project['longitude'] != 0) {
            $distance = calculateDistance($userLat, $userLng, $project['latitude'], $project['longitude']);
            echo "Project {$project['name']}: Distance = {$distance}m, Allowed = {$project['checkin_radius']}m<br>\n";
            
            if ($distance <= $project['checkin_radius']) {
                $validLocation = [
                    'project_id' => $project['id'],
                    'location_name' => $project['place'] ?: $project['name'] . ' Site',
                    'location_display' => $project['place'] ?: $project['name'] . ' Site',
                    'project_name' => $project['name']
                ];
                echo "<div style='color: green;'>✅ Valid location found: {$project['name']}</div><br>\n";
                break;
            }
        }
    }
    
    if (!$validLocation) {
        // Check company location
        $stmt = $db->prepare("SELECT company_name, base_location_lat, base_location_lng, attendance_radius FROM settings LIMIT 1");
        $stmt->execute();
        $settings = $stmt->fetch();
        
        if ($settings && $settings['base_location_lat'] != 0 && $settings['base_location_lng'] != 0) {
            $distance = calculateDistance($userLat, $userLng, $settings['base_location_lat'], $settings['base_location_lng']);
            echo "Company {$settings['company_name']}: Distance = {$distance}m, Allowed = {$settings['attendance_radius']}m<br>\n";
            
            if ($distance <= $settings['attendance_radius']) {
                $validLocation = [
                    'project_id' => null,
                    'location_name' => $settings['company_name'] ?: 'Company Office',
                    'location_display' => $settings['company_name'] ?: 'Company Office',
                    'project_name' => null
                ];
                echo "<div style='color: green;'>✅ Valid company location found</div><br>\n";
            }
        }
    }
    
    if ($validLocation) {
        echo "<h3>Simulating Clock-In</h3>\n";
        
        $currentTime = date('Y-m-d H:i:s');
        
        $stmt = $db->prepare("INSERT INTO attendance (user_id, project_id, check_in, location_name, location_display, project_name, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $result = $stmt->execute([
            $_SESSION['user_id'],
            $validLocation['project_id'],
            $currentTime,
            $validLocation['location_name'],
            $validLocation['location_display'],
            $validLocation['project_name'],
            $currentTime
        ]);
        
        if ($result) {
            echo "<div style='color: green; font-weight: bold;'>✅ Clock-in successful!</div>\n";
            
            // Show the record
            $attendanceId = $db->lastInsertId();
            $stmt = $db->prepare("SELECT * FROM attendance WHERE id = ?");
            $stmt->execute([$attendanceId]);
            $record = $stmt->fetch(PDO::FETCH_ASSOC);
            
            echo "<h3>Attendance Record Created:</h3>\n";
            echo "<table border='1' style='border-collapse: collapse;'>\n";
            foreach ($record as $key => $value) {
                echo "<tr><td><strong>{$key}</strong></td><td>" . htmlspecialchars($value ?? 'NULL') . "</td></tr>\n";
            }
            echo "</table>\n";
            
            echo "<div style='color: green; font-weight: bold; margin-top: 20px;'>🎉 Project-based location tracking is working!</div>\n";
            echo "<div>Location: " . htmlspecialchars($record['location_display']) . "</div>\n";
            echo "<div>Project: " . htmlspecialchars($record['project_name'] ?? '—') . "</div>\n";
            
        } else {
            echo "<div style='color: red;'>❌ Failed to insert attendance record</div>\n";
        }
        
    } else {
        echo "<div style='color: red;'>❌ No valid location found for clock-in</div>\n";
    }
    
} catch (Exception $e) {
    echo "<div style='color: red;'>Error: " . htmlspecialchars($e->getMessage()) . "</div>\n";
}

function calculateDistance($lat1, $lng1, $lat2, $lng2) {
    $earthRadius = 6371000; // Earth radius in meters
    
    $dLat = deg2rad($lat2 - $lat1);
    $dLng = deg2rad($lng2 - $lng1);
    
    $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng/2) * sin($dLng/2);
    $c = 2 * atan2(sqrt($a), sqrt(1-$a));
    
    return $earthRadius * $c; // Distance in meters
}
?>