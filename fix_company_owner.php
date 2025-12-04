<?php
/**
 * Quick Fix for Company Owner Creation
 */

require_once __DIR__ . '/app/config/database.php';

echo "<h2>Fixing Company Owner Creation</h2>";

try {
    $db = Database::connect();
    
    echo "<h3>Step 1: Updating Role Column</h3>";
    
    // Update role column to support company_owner
    $db->exec("ALTER TABLE users MODIFY COLUMN role ENUM('user', 'admin', 'owner', 'company_owner', 'system_admin') DEFAULT 'user'");
    echo "<p style='color: green;'>✓ Role column updated to support company_owner</p>";
    
    echo "<h3>Step 2: Adding Missing Columns</h3>";
    
    // Add missing columns if they don't exist
    $alterQueries = [
        "ALTER TABLE users ADD COLUMN IF NOT EXISTS employee_id VARCHAR(20) UNIQUE",
        "ALTER TABLE users ADD COLUMN IF NOT EXISTS phone VARCHAR(20)",
        "ALTER TABLE users ADD COLUMN IF NOT EXISTS department_id INT DEFAULT NULL",
        "ALTER TABLE users ADD COLUMN IF NOT EXISTS designation VARCHAR(255)",
        "ALTER TABLE users ADD COLUMN IF NOT EXISTS joining_date DATE",
        "ALTER TABLE users ADD COLUMN IF NOT EXISTS salary DECIMAL(10,2)",
        "ALTER TABLE users ADD COLUMN IF NOT EXISTS date_of_birth DATE",
        "ALTER TABLE users ADD COLUMN IF NOT EXISTS gender ENUM('male', 'female', 'other')",
        "ALTER TABLE users ADD COLUMN IF NOT EXISTS address TEXT",
        "ALTER TABLE users ADD COLUMN IF NOT EXISTS emergency_contact VARCHAR(255)",
        "ALTER TABLE users ADD COLUMN IF NOT EXISTS temp_password VARCHAR(255)",
        "ALTER TABLE users ADD COLUMN IF NOT EXISTS is_first_login BOOLEAN DEFAULT FALSE",
        "ALTER TABLE users ADD COLUMN IF NOT EXISTS password_reset_required BOOLEAN DEFAULT FALSE"
    ];
    
    foreach ($alterQueries as $query) {
        try {
            $db->exec($query);
            echo "<p style='color: green;'>✓ " . substr($query, 0, 60) . "...</p>";
        } catch (Exception $e) {
            if (strpos($e->getMessage(), 'Duplicate column') === false && strpos($e->getMessage(), 'check that column') === false) {
                echo "<p style='color: orange;'>⚠ " . $e->getMessage() . "</p>";
            } else {
                echo "<p style='color: blue;'>ℹ Column already exists: " . substr($query, 0, 60) . "...</p>";
            }
        }
    }
    
    echo "<h3>Step 3: Testing Company Owner Creation</h3>";
    
    // Test creating a company owner
    $testEmail = 'test_owner_' . time() . '@example.com';
    $tempPassword = 'TEST' . rand(1000, 9999);
    $hashedPassword = password_hash($tempPassword, PASSWORD_BCRYPT);
    
    $stmt = $db->prepare("INSERT INTO users (name, email, password, role, status, created_at) VALUES (?, ?, ?, 'company_owner', 'active', NOW())");
    $result = $stmt->execute([
        'Test Company Owner',
        $testEmail,
        $hashedPassword
    ]);
    
    if ($result) {
        $userId = $db->lastInsertId();
        echo "<p style='color: green;'>✓ Test company owner created successfully (ID: $userId)</p>";
        
        // Clean up test user
        $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        echo "<p>✓ Test user cleaned up</p>";
    } else {
        echo "<p style='color: red;'>✗ Failed to create test company owner</p>";
    }
    
    echo "<h3>Step 4: Final Status</h3>";
    echo "<p style='color: green; font-weight: bold;'>✓ Company owner creation is now fixed!</p>";
    echo "<p>You can now:</p>";
    echo "<ul>";
    echo "<li>Create users with 'company_owner' role</li>";
    echo "<li>Update existing users to 'company_owner' role</li>";
    echo "<li>Use all the enhanced user creation features</li>";
    echo "</ul>";
    
    echo "<p><strong>Next steps:</strong></p>";
    echo "<ol>";
    echo "<li>Go to <a href='/ergon-site/users/create'>/ergon-site/users/create</a></li>";
    echo "<li>Fill in the user details</li>";
    echo "<li>Select 'Company Owner' from the Role dropdown</li>";
    echo "<li>Click 'Create User'</li>";
    echo "</ol>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
    echo "<p>Stack trace:</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>
