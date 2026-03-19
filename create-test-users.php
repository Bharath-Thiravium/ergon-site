<?php
/**
 * Create Test User - Run this once to create login credentials
 */

require_once __DIR__ . '/app/config/database.php';

try {
    $db = Database::connect();
    
    // Create test admin user
    $email = 'admin@ergon.com';
    $password = password_hash('admin123', PASSWORD_DEFAULT);
    $name = 'Test Admin';
    $role = 'admin';
    
    $stmt = $db->prepare("INSERT INTO users (name, email, password, role, status, created_at) VALUES (?, ?, ?, ?, 'active', NOW()) ON DUPLICATE KEY UPDATE password = VALUES(password)");
    $stmt->execute([$name, $email, $password, $role]);
    
    // Create test user
    $email2 = 'user@ergon.com';
    $password2 = password_hash('user123', PASSWORD_DEFAULT);
    $name2 = 'Test User';
    $role2 = 'user';
    
    $stmt2 = $db->prepare("INSERT INTO users (name, email, password, role, status, created_at) VALUES (?, ?, ?, ?, 'active', NOW()) ON DUPLICATE KEY UPDATE password = VALUES(password)");
    $stmt2->execute([$name2, $email2, $password2, $role2]);
    
    echo "✅ Test users created successfully!\n\n";
    echo "🔑 Login Credentials:\n";
    echo "Admin: admin@ergon.com / admin123\n";
    echo "User: user@ergon.com / user123\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>