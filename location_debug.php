<?php
session_start();
require_once __DIR__ . '/app/config/database.php';

echo "<h2>Location & Clock-in Debug</h2>";

try {
    $db = Database::connect();
    $today = date('Y-m-d');
    
    // Test coordinates for Sector 7, Madurai
    $testLat = 9.9816;
    $testLng = 78.1434;
    
    echo "<h3>1. Testing Location: Sector 7, Madurai</h3>";
    echo "Coordinates: $testLat, $testLng<br>";
    
    // Check projects with location data
    echo "<h3>2. Projects with GPS Coordinates</h3>";
    $stmt = $db->prepare("SELECT id, name, place, latitude, longitude, checkin_radius, status FROM projects WHERE latitude IS NOT NULL AND longitude IS NOT NULL");
    $stmt->execute();
    $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($projects)) {
        echo "❌ No projects with GPS coordinates found<br>";
    } else {
        echo "<table border='1'>";
        echo "<tr><th>ID</th><th>Name</th><th>Place</th><th>Lat</th><th>Lng</th><th>Radius</th><th>Status</th><th>Distance</th></tr>";
        
        foreach ($projects as $project) {
            // Calculate distance
            $distance = calculateDistance($testLat, $testLng, $project['latitude'], $project['longitude']);
            $withinRadius = $distance <= $project['checkin_radius'];
            
            echo "<tr style='background:" . ($withinRadius ? '#d4edda' : '#f8d7da') . "'>";
            echo "<td>{$project['id']}</td>";
            echo "<td>{$project['name']}</td>";
            echo "<td>{$project['place']}</td>";
            echo "<td>{$project['latitude']}</td>";
            echo "<td>{$project['longitude']}</td>";
            echo "<td>{$project['checkin_radius']}m</td>";
            echo "<td>{$project['status']}</td>";
            echo "<td>" . round($distance) . "m " . ($withinRadius ? '✅' : '❌') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Check company location
    echo "<h3>3. Company Location Settings</h3>";
    $stmt = $db->prepare("SELECT company_name, base_location_lat, base_location_lng, attendance_radius FROM settings LIMIT 1");
    $stmt->execute();
    $settings = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($settings) {
        $companyDistance = calculateDistance($testLat, $testLng, $settings['base_location_lat'], $settings['base_location_lng']);
        $withinCompanyRadius = $companyDistance <= $settings['attendance_radius'];
        
        echo "<table border='1'>";
        echo "<tr><th>Company</th><th>Lat</th><th>Lng</th><th>Radius</th><th>Distance</th><th>Valid</th></tr>";
        echo "<tr style='background:" . ($withinCompanyRadius ? '#d4edda' : '#f8d7da') . "'>";
        echo "<td>{$settings['company_name']}</td>";
        echo "<td>{$settings['base_location_lat']}</td>";
        echo "<td>{$settings['base_location_lng']}</td>";
        echo "<td>{$settings['attendance_radius']}m</td>";
        echo "<td>" . round($companyDistance) . "m</td>";
        echo "<td>" . ($withinCompanyRadius ? '✅ Valid' : '❌ Invalid') . "</td>";
        echo "</tr></table>";
    } else {
        echo "❌ No company settings found<br>";
    }
    
    // Test location validation function
    echo "<h3>4. Location Validation Test</h3>";
    $validLocation = null;
    
    // Check projects first
    foreach ($projects as $project) {
        if ($project['latitude'] != 0 && $project['longitude'] != 0 && $project['status'] === 'active') {
            $distance = calculateDistance($testLat, $testLng, $project['latitude'], $project['longitude']);
            
            if ($distance <= $project['checkin_radius']) {
                $validLocation = [
                    'type' => 'project',
                    'project_id' => $project['id'],
                    'location_name' => $project['place'] ?: $project['name'] . ' Site',
                    'location_display' => $project['place'] ?: $project['name'] . ' Site',
                    'project_name' => $project['name'],
                    'distance' => $distance
                ];
                break;
            }
        }
    }
    
    // Check company location if no project match
    if (!$validLocation && $settings && $settings['base_location_lat'] != 0 && $settings['base_location_lng'] != 0) {
        $distance = calculateDistance($testLat, $testLng, $settings['base_location_lat'], $settings['base_location_lng']);
        
        if ($distance <= $settings['attendance_radius']) {
            $validLocation = [
                'type' => 'company',
                'project_id' => null,
                'location_name' => $settings['company_name'] ?: 'Company Office',
                'location_display' => $settings['company_name'] ?: 'Company Office',
                'project_name' => null,
                'distance' => $distance
            ];
        }
    }
    
    if ($validLocation) {
        echo "✅ <strong>Valid Location Found!</strong><br>";
        echo "Type: {$validLocation['type']}<br>";
        echo "Location Display: {$validLocation['location_display']}<br>";
        echo "Project Name: " . ($validLocation['project_name'] ?: '----') . "<br>";
        echo "Distance: " . round($validLocation['distance']) . "m<br>";
    } else {
        echo "❌ <strong>No valid location found for coordinates $testLat, $testLng</strong><br>";
    }
    
    // Check today's attendance
    echo "<h3>5. Today's Attendance Records</h3>";
    if (isset($_SESSION['user_id'])) {
        $stmt = $db->prepare("SELECT * FROM attendance WHERE user_id = ? AND DATE(check_in) = ?");
        $stmt->execute([$_SESSION['user_id'], $today]);
        $attendance = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($attendance) {
            echo "<table border='1'>";
            echo "<tr><th>Field</th><th>Value</th></tr>";
            foreach ($attendance as $key => $value) {
                echo "<tr><td>$key</td><td>" . ($value ?: 'NULL') . "</td></tr>";
            }
            echo "</table>";
        } else {
            echo "❌ No attendance record found for today<br>";
            
            // Create test attendance if valid location
            if ($validLocation) {
                echo "<h4>Creating Test Attendance Record...</h4>";
                $checkIn = $today . ' ' . date('H:i:s');
                
                $stmt = $db->prepare("INSERT INTO attendance (user_id, project_id, check_in, location_name, location_display, project_name, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
                $result = $stmt->execute([
                    $_SESSION['user_id'],
                    $validLocation['project_id'],
                    $checkIn,
                    $validLocation['location_name'],
                    $validLocation['location_display'],
                    $validLocation['project_name']
                ]);
                
                if ($result) {
                    echo "✅ Test attendance record created successfully!<br>";
                    echo "Location: {$validLocation['location_display']}<br>";
                    echo "Project: " . ($validLocation['project_name'] ?: '----') . "<br>";
                } else {
                    echo "❌ Failed to create test attendance record<br>";
                }
            }
        }
    } else {
        echo "❌ No user session found<br>";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
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

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
table { border-collapse: collapse; margin: 10px 0; width: 100%; }
th, td { padding: 8px; text-align: left; border: 1px solid #ddd; }
th { background-color: #f2f2f2; }
h3 { color: #333; border-bottom: 2px solid #007cba; padding-bottom: 5px; }
</style>