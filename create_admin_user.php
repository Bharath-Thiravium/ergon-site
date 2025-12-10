<?php
require_once __DIR__ . '/app/config/database.php';

try {
    $db = Database::connect();
    
    // Check if admin user exists
    $stmt = $db->prepare("SELECT id FROM users WHERE role = 'admin' LIMIT 1");
    $stmt->execute();
    $adminExists = $stmt->fetch();
    
    if ($adminExists) {
        echo "<h2>Admin User Already Exists</h2>";
        echo "<p>An admin user already exists in the system.</p>";
        echo "<p><a href='/ergon-site/login'>Go to Login</a></p>";
    } else {
        // Create admin user
        $username = 'admin';
        $password = 'admin123';
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $db->prepare("INSERT INTO users (username, password, role, name, email, status) VALUES (?, ?, 'admin', 'System Admin', 'admin@ergon.com', 'active')");
        $result = $stmt->execute([$username, $hashedPassword]);
        
        if ($result) {
            echo "<h2>✅ Admin User Created Successfully</h2>";
            echo "<p><strong>Username:</strong> admin</p>";
            echo "<p><strong>Password:</strong> admin123</p>";
            echo "<p><a href='/ergon-site/login'>Go to Login</a></p>";
        } else {
            echo "<h2>❌ Failed to Create Admin User</h2>";
        }
    }
    
} catch (Exception $e) {
    echo "<h2>❌ Database Error</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?>