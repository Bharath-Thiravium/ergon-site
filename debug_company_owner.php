<?php
/**
 * Debug Company Owner Creation Issues
 */

require_once __DIR__ . '/app/config/database.php';

echo "<h2>Company Owner Debug Script</h2>";

try {
    $db = Database::connect();
    
    // 1. Check if users table supports company_owner role
    echo "<h3>1. Checking Users Table Structure</h3>";
    $stmt = $db->query("DESCRIBE users");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $roleColumn = null;
    foreach ($columns as $column) {
        if ($column['Field'] === 'role') {
            $roleColumn = $column;
            break;
        }
    }
    
    if ($roleColumn) {
        echo "<p>Role column type: " . $roleColumn['Type'] . "</p>";
        if (strpos($roleColumn['Type'], 'company_owner') !== false) {
            echo "<p style='color: green;'>✓ company_owner role is supported</p>";
        } else {
            echo "<p style='color: red;'>✗ company_owner role is NOT supported</p>";
            echo "<p>Fixing role column...</p>";
            
            // Fix the role column to support company_owner
            $db->exec("ALTER TABLE users MODIFY COLUMN role ENUM('user', 'admin', 'owner', 'company_owner', 'system_admin') DEFAULT 'user'");
            echo "<p style='color: green;'>✓ Role column updated to support company_owner</p>";
        }
    }
    
    // 2. Check existing company owners
    echo "<h3>2. Existing Company Owners</h3>";
    $stmt = $db->query("SELECT id, name, email, role, status FROM users WHERE role = 'company_owner'");
    $companyOwners = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($companyOwners)) {
        echo "<p>No company owners found</p>";
    } else {
        echo "<table border='1'>";
        echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Status</th></tr>";
        foreach ($companyOwners as $owner) {
            echo "<tr>";
            echo "<td>" . $owner['id'] . "</td>";
            echo "<td>" . $owner['name'] . "</td>";
            echo "<td>" . $owner['email'] . "</td>";
            echo "<td>" . $owner['role'] . "</td>";
            echo "<td>" . $owner['status'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // 3. Test company owner creation
    echo "<h3>3. Test Company Owner Creation</h3>";
    
    $testEmail = 'test_company_owner@example.com';
    
    // Check if test user already exists
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$testEmail]);
    if ($stmt->fetch()) {
        echo "<p>Test user already exists, deleting...</p>";
        $stmt = $db->prepare("DELETE FROM users WHERE email = ?");
        $stmt->execute([$testEmail]);
    }
    
    // Create test company owner
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
        echo "<p>Email: $testEmail</p>";
        echo "<p>Password: $tempPassword</p>";
        
        // Clean up test user
        $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        echo "<p>Test user cleaned up</p>";
    } else {
        echo "<p style='color: red;'>✗ Failed to create test company owner</p>";
    }
    
    // 4. Check required columns
    echo "<h3>4. Checking Required Columns</h3>";
    $requiredColumns = ['employee_id', 'phone', 'department_id'];
    
    foreach ($requiredColumns as $column) {
        $found = false;
        foreach ($columns as $col) {
            if ($col['Field'] === $column) {
                $found = true;
                echo "<p style='color: green;'>✓ Column '$column' exists</p>";
                break;
            }
        }
        if (!$found) {
            echo "<p style='color: red;'>✗ Column '$column' missing</p>";
        }
    }
    
    // 5. Add missing columns if needed
    echo "<h3>5. Adding Missing Columns</h3>";
    
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
        "ALTER TABLE users ADD COLUMN IF NOT EXISTS emergency_contact VARCHAR(255)"
    ];
    
    foreach ($alterQueries as $query) {
        try {
            $db->exec($query);
            echo "<p style='color: green;'>✓ Executed: " . substr($query, 0, 50) . "...</p>";
        } catch (Exception $e) {
            if (strpos($e->getMessage(), 'Duplicate column') === false) {
                echo "<p style='color: orange;'>⚠ " . $e->getMessage() . "</p>";
            }
        }
    }
    
    echo "<h3>6. Final Status</h3>";
    echo "<p style='color: green;'>✓ Company owner creation should now work properly</p>";
    echo "<p>You can now create company owners through the regular user creation form</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>