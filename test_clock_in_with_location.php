<?php
session_start();
require_once __DIR__ . '/app/config/database.php';
require_once __DIR__ . '/app/helpers/TimezoneHelper.php';

// Set test session for a user
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'user';
$_SESSION['user_name'] = 'Test User';

try {
    $db = Database::connect();
    
    echo "<h2>Testing Clock-In with Location Data</h2>";
    
    // Ensure we have a test user
    $stmt = $db->prepare("SELECT id, name FROM users WHERE id = ?");
    $stmt->execute([1]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        echo "<p>Creating test user...</p>";
        $stmt = $db->prepare("INSERT INTO users (name, email, password, role, status) VALUES (?, ?, ?, 'user', 'active')");
        $stmt->execute(['Test User', 'test@example.com', password_hash('password', PASSWORD_DEFAULT)]);
        $_SESSION['user_id'] = $db->lastInsertId();
        echo "<p>✅ Test user created with ID: {$_SESSION['user_id']}</p>";
    } else {
        echo "<p>✅ Using existing user: {$user['name']} (ID: {$user['id']})</p>";
    }
    
    // Clear any existing attendance for today
    $currentDate = date('Y-m-d');
    $stmt = $db->prepare("DELETE FROM attendance WHERE user_id = ? AND DATE(check_in) = ?");
    $stmt->execute([$_SESSION['user_id'], $currentDate]);
    echo "<p>🧹 Cleared existing attendance for today</p>";
    
    // Test location validation function
    function validateProjectBasedLocation($db, $userLat, $userLng) {
        // Check project locations first
        $stmt = $db->prepare("SELECT id, name, place, latitude, longitude, checkin_radius FROM projects WHERE latitude IS NOT NULL AND longitude IS NOT NULL AND status = 'active'");
        $stmt->execute();
        $projects = $stmt->fetchAll();
        
        foreach ($projects as $project) {
            if ($project['latitude'] != 0 && $project['longitude'] != 0) {
                $distance = calculateDistance($userLat, $userLng, $project['latitude'], $project['longitude']);
                
                if ($distance <= $project['checkin_radius']) {
                    return [
                        'allowed' => true,
                        'location_info' => [
                            'project_id' => $project['id'],
                            'location_name' => $project['place'] ?: $project['name'] . ' Site',
                            'location_display' => $project['place'] ?: $project['name'] . ' Site',
                            'project_name' => $project['name']
                        ]
                    ];
                }
            }
        }
        
        // Check company/office location
        $stmt = $db->prepare("SELECT company_name, base_location_lat, base_location_lng, attendance_radius FROM settings LIMIT 1");
        $stmt->execute();
        $settings = $stmt->fetch();
        
        if ($settings && $settings['base_location_lat'] != 0 && $settings['base_location_lng'] != 0) {
            $distance = calculateDistance($userLat, $userLng, $settings['base_location_lat'], $settings['base_location_lng']);
            
            if ($distance <= $settings['attendance_radius']) {
                return [
                    'allowed' => true,
                    'location_info' => [
                        'project_id' => null,
                        'location_name' => $settings['company_name'] ?: 'Company Office',
                        'location_display' => $settings['company_name'] ?: 'Company Office',
                        'project_name' => null
                    ]
                ];
            }
        }
        
        return ['allowed' => false];
    }
    
    function calculateDistance($lat1, $lng1, $lat2, $lng2) {
        $earthRadius = 6371000; // Earth radius in meters
        
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        
        $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng/2) * sin($dLng/2);
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        
        return $earthRadius * $c; // Distance in meters
    }
    
    // Test scenarios
    $testScenarios = [
        [
            'name' => 'Company Office Location',
            'lat' => 12.9716,
            'lng' => 77.5946,
            'description' => 'Should clock in at Athena Solutions Office'
        ],
        [
            'name' => 'Project Alpha Site',
            'lat' => 12.9716,
            'lng' => 77.5946,
            'description' => 'Should clock in at Alpha Construction Site'
        ]
    ];
    
    echo "<h3>Testing Location Validation:</h3>";
    
    foreach ($testScenarios as $scenario) {
        echo "<h4>{$scenario['name']}</h4>";
        echo "<p>📍 Testing coordinates: ({$scenario['lat']}, {$scenario['lng']})</p>";
        
        $locationValidation = validateProjectBasedLocation($db, $scenario['lat'], $scenario['lng']);
        
        if ($locationValidation['allowed']) {
            $locationInfo = $locationValidation['location_info'];
            echo "<p>✅ Location validated successfully!</p>";
            echo "<p>📍 Location Display: {$locationInfo['location_display']}</p>";
            echo "<p>🏗️ Project Name: " . ($locationInfo['project_name'] ?: '----') . "</p>";
            
            // Simulate clock-in
            $currentTime = date('Y-m-d H:i:s');
            $stmt = $db->prepare("INSERT INTO attendance (user_id, project_id, check_in, location_name, location_display, project_name, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $result = $stmt->execute([
                $_SESSION['user_id'],
                $locationInfo['project_id'],
                $currentTime,
                $locationInfo['location_name'],
                $locationInfo['location_display'],
                $locationInfo['project_name'],
                $currentTime
            ]);
            
            if ($result) {
                echo "<p>✅ Clock-in simulated successfully!</p>";
                $attendanceId = $db->lastInsertId();
                echo "<p>📝 Attendance ID: {$attendanceId}</p>";
                
                // Verify the data was saved correctly
                $stmt = $db->prepare("SELECT * FROM attendance WHERE id = ?");
                $stmt->execute([$attendanceId]);
                $attendance = $stmt->fetch(PDO::FETCH_ASSOC);
                
                echo "<p><strong>Saved Data:</strong></p>";
                echo "<ul>";
                echo "<li>Location Display: {$attendance['location_display']}</li>";
                echo "<li>Project Name: " . ($attendance['project_name'] ?: '----') . "</li>";
                echo "<li>Location Name: {$attendance['location_name']}</li>";
                echo "<li>Check In: {$attendance['check_in']}</li>";
                echo "</ul>";
            } else {
                echo "<p>❌ Failed to simulate clock-in</p>";
            }
        } else {
            echo "<p>❌ Location not allowed</p>";
        }
        
        echo "<hr>";
    }
    
    // Show final attendance records
    echo "<h3>Current Attendance Records:</h3>";
    $stmt = $db->prepare("
        SELECT 
            a.*,
            u.name as user_name,
            CASE 
                WHEN a.location_display IS NOT NULL AND a.location_display != '' THEN a.location_display
                ELSE '---'
            END as display_location,
            CASE 
                WHEN a.project_name IS NOT NULL AND a.project_name != '' THEN a.project_name
                ELSE '----'
            END as display_project
        FROM attendance a 
        LEFT JOIN users u ON a.user_id = u.id 
        WHERE DATE(a.check_in) = ?
        ORDER BY a.created_at DESC
    ");
    $stmt->execute([$currentDate]);
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($records)) {
        echo "<p>No attendance records found for today.</p>";
    } else {
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr style='background-color: #f2f2f2;'><th style='padding: 8px;'>User</th><th style='padding: 8px;'>Check In</th><th style='padding: 8px;'>Location Display</th><th style='padding: 8px;'>Project Name</th></tr>";
        
        foreach ($records as $record) {
            echo "<tr>";
            echo "<td style='padding: 8px;'>{$record['user_name']}</td>";
            echo "<td style='padding: 8px;'>{$record['check_in']}</td>";
            echo "<td style='padding: 8px;'>{$record['display_location']}</td>";
            echo "<td style='padding: 8px;'>{$record['display_project']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    echo "<h3>✅ Test Complete!</h3>";
    echo "<p>Now visit <a href='/ergon-site/attendance' target='_blank'>http://localhost/ergon-site/attendance</a> to see the results in the attendance panel.</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>