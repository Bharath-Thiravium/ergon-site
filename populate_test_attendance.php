<?php
require_once __DIR__ . '/app/config/database.php';

try {
    $db = Database::connect();
    
    echo "<h2>Populating Test Attendance Data</h2>";
    
    // First, check if we have users
    $stmt = $db->query("SELECT id, name, role FROM users WHERE status = 'active' LIMIT 5");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($users)) {
        echo "<p>No active users found. Creating test users...</p>";
        
        // Create test users
        $stmt = $db->prepare("INSERT INTO users (name, email, password, role, status) VALUES (?, ?, ?, ?, 'active')");
        $stmt->execute(['Joel Smith', 'joel@example.com', password_hash('password', PASSWORD_DEFAULT), 'user']);
        $stmt->execute(['Simon Johnson', 'simon@example.com', password_hash('password', PASSWORD_DEFAULT), 'user']);
        $stmt->execute(['Admin User', 'admin@example.com', password_hash('password', PASSWORD_DEFAULT), 'admin']);
        
        // Get the created users
        $stmt = $db->query("SELECT id, name, role FROM users WHERE status = 'active' ORDER BY id DESC LIMIT 3");
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    echo "<h3>Active Users:</h3>";
    foreach ($users as $user) {
        echo "<p>ID: {$user['id']}, Name: {$user['name']}, Role: {$user['role']}</p>";
    }
    
    // Clear existing attendance for today
    $today = date('Y-m-d');
    $stmt = $db->prepare("DELETE FROM attendance WHERE DATE(check_in) = ?");
    $stmt->execute([$today]);
    
    echo "<h3>Creating Test Attendance Records for Today ($today):</h3>";
    
    // Create test attendance records with different scenarios
    $testData = [
        [
            'user_id' => $users[0]['id'],
            'check_in' => $today . ' 09:34:00',
            'check_out' => $today . ' 20:00:00',
            'location_display' => 'Athena Solutions',
            'project_name' => null, // Company location
            'location_name' => 'Athena Solutions Office'
        ],
        [
            'user_id' => $users[1]['id'],
            'check_in' => null,
            'check_out' => null,
            'location_display' => null,
            'project_name' => null,
            'location_name' => null
        ]
    ];
    
    if (count($users) > 2) {
        $testData[] = [
            'user_id' => $users[2]['id'],
            'check_in' => $today . ' 08:30:00',
            'check_out' => null,
            'location_display' => 'Project Alpha Site',
            'project_name' => 'Project Alpha',
            'location_name' => 'Project Alpha Construction Site'
        ];
    }
    
    foreach ($testData as $data) {
        if ($data['check_in']) {
            $stmt = $db->prepare("INSERT INTO attendance (user_id, check_in, check_out, location_name, location_display, project_name, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
            $stmt->execute([
                $data['user_id'],
                $data['check_in'],
                $data['check_out'],
                $data['location_name'],
                $data['location_display'],
                $data['project_name']
            ]);
            
            $userName = $users[array_search($data['user_id'], array_column($users, 'id'))]['name'];
            echo "<p>✅ Created attendance for {$userName} - Location: " . ($data['location_display'] ?: '---') . ", Project: " . ($data['project_name'] ?: '----') . "</p>";
        } else {
            $userName = $users[array_search($data['user_id'], array_column($users, 'id'))]['name'];
            echo "<p>⚪ No attendance record for {$userName} (Absent)</p>";
        }
    }
    
    echo "<h3>Verification - Current Attendance Records:</h3>";
    $stmt = $db->prepare("
        SELECT 
            u.name,
            u.role,
            a.check_in,
            a.check_out,
            CASE 
                WHEN a.location_display IS NOT NULL AND a.location_display != '' THEN a.location_display
                ELSE '---'
            END as location_display,
            CASE 
                WHEN a.project_name IS NOT NULL AND a.project_name != '' THEN a.project_name
                ELSE '----'
            END as project_name,
            CASE 
                WHEN a.check_in IS NOT NULL THEN 'Present'
                ELSE 'Absent'
            END as status
        FROM users u
        LEFT JOIN attendance a ON u.id = a.user_id AND DATE(a.check_in) = ?
        WHERE u.status = 'active'
        ORDER BY u.role DESC, u.name
    ");
    $stmt->execute([$today]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr style='background-color: #f2f2f2;'><th style='padding: 8px;'>Name & Role</th><th style='padding: 8px;'>Status</th><th style='padding: 8px;'>Location</th><th style='padding: 8px;'>Project</th><th style='padding: 8px;'>Check In</th><th style='padding: 8px;'>Check Out</th></tr>";
    
    foreach ($results as $result) {
        echo "<tr>";
        echo "<td style='padding: 8px;'>{$result['name']} & Role: " . ucfirst($result['role']) . "</td>";
        echo "<td style='padding: 8px;'>{$result['status']}</td>";
        echo "<td style='padding: 8px;'>{$result['location_display']}</td>";
        echo "<td style='padding: 8px;'>{$result['project_name']}</td>";
        echo "<td style='padding: 8px;'>" . ($result['check_in'] ? date('H:i', strtotime($result['check_in'])) : 'Not set') . "</td>";
        echo "<td style='padding: 8px;'>" . ($result['check_out'] ? date('H:i', strtotime($result['check_out'])) : 'Not set') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<p><strong>✅ Test data populated successfully!</strong></p>";
    echo "<p>Now visit <a href='/ergon-site/attendance' target='_blank'>http://localhost/ergon-site/attendance</a> to see the results.</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>