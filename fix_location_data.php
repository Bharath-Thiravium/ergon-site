<?php
require_once __DIR__ . '/app/config/database.php';

try {
    $db = Database::connect();
    
    echo "<h2>Fixing Location Data for Testing</h2>";
    
    // Clear existing data
    $db->exec("DELETE FROM projects");
    $db->exec("DELETE FROM settings");
    
    echo "<p>🧹 Cleared existing projects and settings</p>";
    
    // Insert test projects with same coordinates as test
    $stmt = $db->prepare("INSERT INTO projects (name, place, description, latitude, longitude, checkin_radius, status) VALUES (?, ?, ?, ?, ?, ?, 'active')");
    
    $projects = [
        ['Project Alpha', 'Alpha Construction Site', 'Main construction project', 12.9716, 77.5946, 200],
        ['Project Beta', 'Beta Development Site', 'Software development project', 12.9352, 77.6245, 150]
    ];
    
    foreach ($projects as $project) {
        $stmt->execute($project);
        echo "<p>➕ Created project: {$project[0]} at ({$project[3]}, {$project[4]}) with {$project[5]}m radius</p>";
    }
    
    // Insert company settings with same coordinates
    $stmt = $db->prepare("INSERT INTO settings (company_name, base_location_lat, base_location_lng, attendance_radius, location_title) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute(['Athena Solutions', 12.9716, 77.5946, 500, 'Athena Solutions Office']);
    
    echo "<p>➕ Created company settings: Athena Solutions at (12.9716, 77.5946) with 500m radius</p>";
    
    // Test location validation
    echo "<h3>Testing Location Validation:</h3>";
    
    $testLat = 12.9716;
    $testLng = 77.5946;
    
    // Check projects
    $stmt = $db->prepare("SELECT id, name, place, latitude, longitude, checkin_radius FROM projects WHERE status = 'active'");
    $stmt->execute();
    $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p>📍 Testing coordinates: ($testLat, $testLng)</p>";
    echo "<p>Found " . count($projects) . " active projects</p>";
    
    $found = false;
    foreach ($projects as $project) {
        $distance = 6371000 * 2 * asin(sqrt(pow(sin(deg2rad($testLat - $project['latitude']) / 2), 2) + cos(deg2rad($project['latitude'])) * cos(deg2rad($testLat)) * pow(sin(deg2rad($testLng - $project['longitude']) / 2), 2)));
        
        echo "<p>Project {$project['name']}: Distance = " . round($distance) . "m (Radius: {$project['checkin_radius']}m)</p>";
        
        if ($distance <= $project['checkin_radius']) {
            echo "<p>✅ MATCH: {$project['name']} at {$project['place']}</p>";
            $found = true;
        }
    }
    
    // Check company location
    $stmt = $db->prepare("SELECT company_name, base_location_lat, base_location_lng, attendance_radius FROM settings LIMIT 1");
    $stmt->execute();
    $settings = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($settings) {
        $distance = 6371000 * 2 * asin(sqrt(pow(sin(deg2rad($testLat - $settings['base_location_lat']) / 2), 2) + cos(deg2rad($settings['base_location_lat'])) * cos(deg2rad($testLat)) * pow(sin(deg2rad($testLng - $settings['base_location_lng']) / 2), 2)));
        
        echo "<p>Company {$settings['company_name']}: Distance = " . round($distance) . "m (Radius: {$settings['attendance_radius']}m)</p>";
        
        if ($distance <= $settings['attendance_radius']) {
            echo "<p>✅ MATCH: Company office {$settings['company_name']}</p>";
            $found = true;
        }
    }
    
    if (!$found) {
        echo "<p>❌ No location matches found</p>";
    }
    
    // Now create a test attendance record
    echo "<h3>Creating Test Attendance Record:</h3>";
    
    $currentTime = date('Y-m-d H:i:s');
    $stmt = $db->prepare("INSERT INTO attendance (user_id, project_id, check_in, location_name, location_display, project_name, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $result = $stmt->execute([1, 1, $currentTime, 'Alpha Construction Site', 'Alpha Construction Site', 'Project Alpha', $currentTime]);
    
    if ($result) {
        echo "<p>✅ Created test attendance record with location data</p>";
    }
    
    echo "<h3>✅ Fix Complete!</h3>";
    echo "<p>Now test again: <a href='/ergon-site/test_clock_in_with_location.php' target='_blank'>Test Clock-In</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>