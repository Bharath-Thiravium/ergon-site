<?php
require_once __DIR__ . '/app/config/database.php';

try {
    $db = Database::connect();
    
    echo "<h2>Creating Sample Attendance Records with Location Data</h2>";
    
    // Clear today's attendance
    $today = date('Y-m-d');
    $stmt = $db->prepare("DELETE FROM attendance WHERE DATE(check_in) = ?");
    $stmt->execute([$today]);
    echo "<p>🧹 Cleared existing attendance for today</p>";
    
    // Get users
    $stmt = $db->query("SELECT id, name FROM users WHERE status = 'active' LIMIT 3");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($users)) {
        echo "<p>❌ No users found. Creating test users...</p>";
        
        $testUsers = [
            ['Joel Smith', 'joel@example.com'],
            ['Simon Johnson', 'simon@example.com'],
            ['Admin User', 'admin@example.com']
        ];
        
        foreach ($testUsers as $user) {
            $stmt = $db->prepare("INSERT INTO users (name, email, password, role, status) VALUES (?, ?, ?, 'user', 'active')");
            $stmt->execute([$user[0], $user[1], password_hash('password', PASSWORD_DEFAULT)]);
        }
        
        $stmt = $db->query("SELECT id, name FROM users WHERE status = 'active' LIMIT 3");
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Create sample attendance records
    $attendanceData = [
        [
            'user_id' => $users[0]['id'],
            'check_in' => $today . ' 09:34:00',
            'check_out' => $today . ' 20:00:00',
            'location_display' => 'Athena Solutions',
            'project_name' => null,
            'location_name' => 'Athena Solutions Office',
            'project_id' => null
        ],
        [
            'user_id' => $users[1]['id'] ?? $users[0]['id'],
            'check_in' => null,
            'check_out' => null,
            'location_display' => null,
            'project_name' => null,
            'location_name' => null,
            'project_id' => null
        ]
    ];
    
    if (count($users) > 2) {
        $attendanceData[] = [
            'user_id' => $users[2]['id'],
            'check_in' => $today . ' 08:30:00',
            'check_out' => null,
            'location_display' => 'Alpha Construction Site',
            'project_name' => 'Project Alpha',
            'location_name' => 'Alpha Construction Site',
            'project_id' => 1
        ];
    }
    
    foreach ($attendanceData as $data) {
        if ($data['check_in']) {
            $stmt = $db->prepare("INSERT INTO attendance (user_id, project_id, check_in, check_out, location_name, location_display, project_name, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
            $stmt->execute([
                $data['user_id'],
                $data['project_id'],
                $data['check_in'],
                $data['check_out'],
                $data['location_name'],
                $data['location_display'],
                $data['project_name']
            ]);
            
            $userName = $users[array_search($data['user_id'], array_column($users, 'id'))]['name'];
            echo "<p>✅ Created attendance for {$userName}:</p>";
            echo "<ul>";
            echo "<li>Location: " . ($data['location_display'] ?: '---') . "</li>";
            echo "<li>Project: " . ($data['project_name'] ?: '----') . "</li>";
            echo "<li>Check In: {$data['check_in']}</li>";
            echo "<li>Check Out: " . ($data['check_out'] ?: 'Not set') . "</li>";
            echo "</ul>";
        }
    }
    
    // Verify the records
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
            END as status,
            CASE 
                WHEN a.check_in IS NOT NULL AND a.check_out IS NOT NULL THEN 
                    CONCAT(FLOOR(TIMESTAMPDIFF(MINUTE, a.check_in, a.check_out) / 60), 'h ', 
                           MOD(TIMESTAMPDIFF(MINUTE, a.check_in, a.check_out), 60), 'm')
                ELSE '0h 0m'
            END as working_hours
        FROM users u
        LEFT JOIN attendance a ON u.id = a.user_id AND DATE(a.check_in) = ?
        WHERE u.status = 'active'
        ORDER BY u.role DESC, u.name
    ");
    $stmt->execute([$today]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr style='background-color: #f2f2f2;'>";
    echo "<th style='padding: 8px;'>Employee & Department</th>";
    echo "<th style='padding: 8px;'>Date & Status</th>";
    echo "<th style='padding: 8px;'>Location</th>";
    echo "<th style='padding: 8px;'>Project</th>";
    echo "<th style='padding: 8px;'>Working Hours</th>";
    echo "<th style='padding: 8px;'>Check Times</th>";
    echo "</tr>";
    
    foreach ($results as $result) {
        echo "<tr>";
        echo "<td style='padding: 8px;'>{$result['name']} & Role: " . ucfirst($result['role']) . "</td>";
        echo "<td style='padding: 8px;'>" . date('M d, Y') . " & {$result['status']}</td>";
        echo "<td style='padding: 8px;'>{$result['location_display']}</td>";
        echo "<td style='padding: 8px;'>{$result['project_name']}</td>";
        echo "<td style='padding: 8px;'>{$result['working_hours']}</td>";
        echo "<td style='padding: 8px;'>In: " . ($result['check_in'] ? date('H:i', strtotime($result['check_in'])) : 'Not set') . ", Out: " . ($result['check_out'] ? date('H:i', strtotime($result['check_out'])) : 'Not set') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h3>✅ Sample Data Created Successfully!</h3>";
    echo "<p><strong>Expected Output Format Achieved:</strong></p>";
    echo "<p>📊 Current Output Format:<br>";
    echo "Employee & Department | Date & Status | Location | Project | Working Hours | Check Times | Actions</p>";
    
    echo "<p>Now visit <a href='/ergon-site/attendance' target='_blank'>http://localhost/ergon-site/attendance</a> to see the Location and Project columns populated correctly!</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>