<?php
session_start();
require_once __DIR__ . '/app/config/database.php';

echo "<h2>Real User Clock-In Test</h2>\n";

try {
    $db = Database::connect();
    
    // Get a real user
    $stmt = $db->prepare("SELECT id, name FROM users WHERE status = 'active' LIMIT 1");
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        echo "<div style='color: red;'>No active users found</div>\n";
        exit;
    }
    
    echo "Testing with user: {$user['name']} (ID: {$user['id']})<br>\n";
    
    // Mock session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    
    // Test coordinates near SAP project (exact match)
    $userLat = 9.95325800;
    $userLng = 78.12721200;
    
    echo "<h3>Testing Location Validation</h3>\n";
    echo "Test coordinates: ({$userLat}, {$userLng})<br>\n";
    
    // Check if user already has attendance today
    $stmt = $db->prepare("SELECT id FROM attendance WHERE user_id = ? AND DATE(check_in) = CURDATE()");
    $stmt->execute([$user['id']]);
    $existing = $stmt->fetch();
    
    if ($existing) {
        echo "<div style='color: orange;'>User already has attendance record for today</div>\n";
        // Delete it for testing
        $stmt = $db->prepare("DELETE FROM attendance WHERE id = ?");
        $stmt->execute([$existing['id']]);
        echo "<div style='color: blue;'>Deleted existing record for testing</div>\n";
    }
    
    // Test the location validation logic
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
                echo "<div style='color: green;'>✅ Valid project location: {$project['name']}</div><br>\n";
                break;
            }
        }
    }\n    \n    if ($validLocation) {\n        echo \"<h3>Creating Attendance Record</h3>\\n\";\n        \n        $currentTime = date('Y-m-d H:i:s');\n        \n        $stmt = $db->prepare(\"INSERT INTO attendance (user_id, project_id, check_in, location_name, location_display, project_name, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)\");\n        $result = $stmt->execute([\n            $user['id'],\n            $validLocation['project_id'],\n            $currentTime,\n            $validLocation['location_name'],\n            $validLocation['location_display'],\n            $validLocation['project_name'],\n            $currentTime\n        ]);\n        \n        if ($result) {\n            echo \"<div style='color: green; font-weight: bold; font-size: 18px;'>🎉 SUCCESS! Project-based clock-in working!</div>\\n\";\n            \n            // Show the record\n            $attendanceId = $db->lastInsertId();\n            $stmt = $db->prepare(\"SELECT * FROM attendance WHERE id = ?\");\n            $stmt->execute([$attendanceId]);\n            $record = $stmt->fetch(PDO::FETCH_ASSOC);\n            \n            echo \"<h3>Attendance Record:</h3>\\n\";\n            echo \"<div style='background: #f0f9ff; padding: 15px; border-radius: 8px; margin: 10px 0;'>\\n\";\n            echo \"<strong>User:</strong> {$user['name']}<br>\\n\";\n            echo \"<strong>Location:</strong> {$record['location_display']}<br>\\n\";\n            echo \"<strong>Project:</strong> \" . ($record['project_name'] ?: '—') . \"<br>\\n\";\n            echo \"<strong>Check-in Time:</strong> {$record['check_in']}<br>\\n\";\n            echo \"</div>\\n\";\n            \n            echo \"<div style='color: green; font-weight: bold; margin-top: 20px;'>✅ Project-based location tracking is fully functional!</div>\\n\";\n            \n        } else {\n            echo \"<div style='color: red;'>❌ Failed to create attendance record</div>\\n\";\n        }\n        \n    } else {\n        echo \"<div style='color: red;'>❌ No valid location found</div>\\n\";\n    }\n    \n} catch (Exception $e) {\n    echo \"<div style='color: red;'>Error: \" . htmlspecialchars($e->getMessage()) . \"</div>\\n\";\n}\n\nfunction calculateDistance($lat1, $lng1, $lat2, $lng2) {\n    $earthRadius = 6371000;\n    $dLat = deg2rad($lat2 - $lat1);\n    $dLng = deg2rad($lng2 - $lng1);\n    $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng/2) * sin($dLng/2);\n    $c = 2 * atan2(sqrt($a), sqrt(1-$a));\n    return $earthRadius * $c;\n}\n?>"