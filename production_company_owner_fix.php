<?php
/**
 * Production Company Owner Fix
 * Run this on production server to enable company owner creation
 */

require_once __DIR__ . '/app/config/database.php';

echo "<h2>Production Company Owner Fix</h2>";

try {
    $db = Database::connect();
    
    echo "<h3>Step 1: Update Role Column</h3>";
    $db->exec("ALTER TABLE users MODIFY COLUMN role ENUM('user', 'admin', 'owner', 'company_owner', 'system_admin') DEFAULT 'user'");
    echo "<p style='color: green;'>✓ Role column updated</p>";
    
    echo "<h3>Step 2: Fix Gender Column</h3>";
    $db->exec("ALTER TABLE users MODIFY COLUMN gender ENUM('male', 'female', 'other') NULL DEFAULT NULL");
    echo "<p style='color: green;'>✓ Gender column fixed</p>";
    
    echo "<h3>Step 3: Add Missing Columns</h3>";
    $stmt = $db->query("DESCRIBE users");
    $existingColumns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $requiredColumns = [
        'employee_id' => 'VARCHAR(20) UNIQUE',
        'phone' => 'VARCHAR(20)',
        'department_id' => 'INT DEFAULT NULL',
        'designation' => 'VARCHAR(255)',
        'joining_date' => 'DATE',
        'salary' => 'DECIMAL(10,2)',
        'date_of_birth' => 'DATE',
        'address' => 'TEXT',
        'emergency_contact' => 'VARCHAR(255)',
        'temp_password' => 'VARCHAR(255)',
        'is_first_login' => 'BOOLEAN DEFAULT FALSE',
        'password_reset_required' => 'BOOLEAN DEFAULT FALSE'
    ];
    
    foreach ($requiredColumns as $column => $definition) {
        if (!in_array($column, $existingColumns)) {
            try {
                $db->exec("ALTER TABLE users ADD COLUMN $column $definition");
                echo "<p style='color: green;'>✓ Added column: $column</p>";
            } catch (Exception $e) {
                echo "<p style='color: orange;'>⚠ Column $column: " . $e->getMessage() . "</p>";
            }
        } else {
            echo "<p style='color: blue;'>ℹ Column exists: $column</p>";
        }
    }
    
    echo "<h3>Step 4: Test Company Owner Creation</h3>";
    $testEmail = 'prod_test_' . time() . '@example.com';
    $tempPassword = 'TEST' . rand(1000, 9999);
    $hashedPassword = password_hash($tempPassword, PASSWORD_BCRYPT);
    
    $stmt = $db->prepare("INSERT INTO users (name, email, password, role, status, created_at) VALUES (?, ?, ?, 'company_owner', 'active', NOW())");
    $result = $stmt->execute([
        'Production Test Company Owner',
        $testEmail,
        $hashedPassword
    ]);
    
    if ($result) {
        $userId = $db->lastInsertId();
        echo "<p style='color: green;'>✓ Company owner creation successful (ID: $userId)</p>";
        
        // Clean up test user
        $db->prepare("DELETE FROM users WHERE id = ?")->execute([$userId]);
        echo "<p>✓ Test user cleaned up</p>";
    }
    
    echo "<h3>Step 5: Add Favicon (Optional)</h3>";
    $faviconPath = __DIR__ . '/favicon.ico';
    if (!file_exists($faviconPath)) {
        // Create a simple favicon
        echo "<p style='color: orange;'>⚠ No favicon found. You can add one later.</p>";
    } else {
        echo "<p style='color: green;'>✓ Favicon exists</p>";
    }
    
    echo "<h3 style='color: green;'>✅ Production Fix Complete!</h3>";
    echo "<p><strong>Company owner creation is now enabled on production.</strong></p>";
    echo "<p>You can now create company owners through the user management interface.</p>";
    
    // Security: Remove this file after running
    echo "<hr>";
    echo "<p style='color: red;'><strong>Security Note:</strong> Delete this file after running for security.</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>
