<?php
session_start();

echo "<h2>Module Check</h2>";

try {
    require_once __DIR__ . '/app/helpers/ModuleManager.php';
    
    echo "<h3>Current User Role: " . ($_SESSION['role'] ?? 'Not set') . "</h3>";
    
    echo "<h3>Enabled Modules:</h3>";
    $enabledModules = ModuleManager::getEnabledModules();
    echo "<ul>";
    foreach ($enabledModules as $module) {
        echo "<li>" . $module . "</li>";
    }
    echo "</ul>";
    
    echo "<h3>Users Module Check:</h3>";
    $usersEnabled = ModuleManager::isModuleEnabled('users');
    if ($usersEnabled) {
        echo "<p style='color: green;'>✅ Users module is enabled</p>";
    } else {
        echo "<p style='color: red;'>❌ Users module is disabled</p>";
        echo "<p><strong>This is likely the issue!</strong></p>";
    }
    
    echo "<h3>Quick Fix:</h3>";
    if (!$usersEnabled && isset($_GET['enable_users'])) {
        // Try to enable users module
        try {
            require_once __DIR__ . '/app/config/database.php';
            $db = Database::connect();
            
            // Check if modules table exists
            $stmt = $db->query("SHOW TABLES LIKE 'modules'");
            if ($stmt->rowCount() == 0) {
                // Create modules table
                $db->exec("CREATE TABLE modules (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    name VARCHAR(50) NOT NULL UNIQUE,
                    enabled BOOLEAN DEFAULT TRUE,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )");
                echo "<p>Created modules table</p>";
            }
            
            // Enable users module
            $stmt = $db->prepare("INSERT INTO modules (name, enabled) VALUES ('users', 1) ON DUPLICATE KEY UPDATE enabled = 1");
            $stmt->execute();
            
            echo "<p style='color: green;'>✅ Users module enabled!</p>";
            echo "<p><a href='/ergon-site/users/create'>Try creating a user now</a></p>";
            
        } catch (Exception $e) {
            echo "<p style='color: red;'>Failed to enable module: " . $e->getMessage() . "</p>";
        }
    }
    
    if (!$usersEnabled) {
        echo "<p><a href='?enable_users=1' style='background: #007cba; color: white; padding: 10px; text-decoration: none; border-radius: 4px;'>Enable Users Module</a></p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
    echo "<p>ModuleManager might not be available or configured</p>";
}
?>