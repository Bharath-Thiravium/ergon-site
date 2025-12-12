<?php
require_once __DIR__ . '/app/config/database.php';

try {
    $db = Database::connect();
    
    echo "<h2>Setting up Projects and Settings Tables</h2>";
    
    // Create projects table
    $db->exec("CREATE TABLE IF NOT EXISTS projects (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        place VARCHAR(255) NULL,
        description TEXT NULL,
        latitude DECIMAL(10,8) NULL,
        longitude DECIMAL(11,8) NULL,
        checkin_radius INT DEFAULT 100,
        status VARCHAR(20) DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");
    
    echo "<p>✅ Projects table created/verified</p>";
    
    // Create settings table
    $db->exec("CREATE TABLE IF NOT EXISTS settings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        company_name VARCHAR(255) DEFAULT 'ERGON Company',
        base_location_lat DECIMAL(10,8) DEFAULT 0,
        base_location_lng DECIMAL(11,8) DEFAULT 0,
        attendance_radius INT DEFAULT 500,
        location_title VARCHAR(255) DEFAULT 'Main Office',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");
    
    echo "<p>✅ Settings table created/verified</p>";
    
    // Insert sample projects if none exist
    $stmt = $db->query("SELECT COUNT(*) as count FROM projects");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result['count'] == 0) {
        echo "<h3>Creating Sample Projects:</h3>";
        
        $sampleProjects = [
            [
                'name' => 'Project Alpha',
                'place' => 'Alpha Construction Site',
                'description' => 'Main construction project',
                'latitude' => 12.9716,
                'longitude' => 77.5946,
                'checkin_radius' => 200
            ],
            [
                'name' => 'Project Beta',
                'place' => 'Beta Development Site',
                'description' => 'Software development project',
                'latitude' => 12.9352,
                'longitude' => 77.6245,
                'checkin_radius' => 150
            ],
            [
                'name' => 'Project Gamma',
                'place' => 'Gamma Research Center',
                'description' => 'Research and development',
                'latitude' => 12.9698,
                'longitude' => 77.7500,
                'checkin_radius' => 100
            ]
        ];
        
        $stmt = $db->prepare("INSERT INTO projects (name, place, description, latitude, longitude, checkin_radius, status) VALUES (?, ?, ?, ?, ?, ?, 'active')");
        
        foreach ($sampleProjects as $project) {
            $stmt->execute([
                $project['name'],
                $project['place'],
                $project['description'],
                $project['latitude'],
                $project['longitude'],
                $project['checkin_radius']
            ]);
            echo "<p>➕ Created project: {$project['name']} at {$project['place']}</p>";
        }
    } else {
        echo "<p>ℹ️ Projects already exist ({$result['count']} projects found)</p>";
    }
    
    // Insert/update settings if none exist
    $stmt = $db->query("SELECT COUNT(*) as count FROM settings");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result['count'] == 0) {
        echo "<h3>Creating Company Settings:</h3>";
        
        $stmt = $db->prepare("INSERT INTO settings (company_name, base_location_lat, base_location_lng, attendance_radius, location_title) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            'Athena Solutions',
            12.9716,  // Bangalore coordinates
            77.5946,
            300,      // 300 meter radius
            'Athena Solutions Office'
        ]);
        
        echo "<p>➕ Created company settings: Athena Solutions Office</p>";
    } else {
        echo "<p>ℹ️ Company settings already exist</p>";
        
        // Show current settings
        $stmt = $db->query("SELECT * FROM settings LIMIT 1");
        $settings = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($settings) {
            echo "<p>📍 Current company: {$settings['company_name']} at ({$settings['base_location_lat']}, {$settings['base_location_lng']}) with {$settings['attendance_radius']}m radius</p>";
        }
    }
    
    // Show current projects
    echo "<h3>Current Projects:</h3>";
    $stmt = $db->query("SELECT * FROM projects WHERE status = 'active'");
    $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($projects)) {
        echo "<p>No active projects found.</p>";
    } else {
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr style='background-color: #f2f2f2;'><th style='padding: 8px;'>ID</th><th style='padding: 8px;'>Name</th><th style='padding: 8px;'>Place</th><th style='padding: 8px;'>Coordinates</th><th style='padding: 8px;'>Radius</th></tr>";
        
        foreach ($projects as $project) {
            echo "<tr>";
            echo "<td style='padding: 8px;'>{$project['id']}</td>";
            echo "<td style='padding: 8px;'>{$project['name']}</td>";
            echo "<td style='padding: 8px;'>{$project['place']}</td>";
            echo "<td style='padding: 8px;'>({$project['latitude']}, {$project['longitude']})</td>";
            echo "<td style='padding: 8px;'>{$project['checkin_radius']}m</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    echo "<h3>✅ Setup Complete!</h3>";
    echo "<p><strong>Next Steps:</strong></p>";
    echo "<ul>";
    echo "<li>Visit <a href='/ergon-site/attendance/clock' target='_blank'>Clock In/Out Page</a> to test location-based attendance</li>";
    echo "<li>The system will now detect if you're at a project site or company office</li>";
    echo "<li>Location and Project columns will be populated based on your GPS location</li>";
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>