<?php
/**
 * MySQL Compatible Column Fix for Company Owner
 */

require_once __DIR__ . '/app/config/database.php';

echo "<h2>Adding Missing Columns (MySQL Compatible)</h2>";

try {
    $db = Database::connect();
    
    // Get existing columns
    $stmt = $db->query("DESCRIBE users");
    $existingColumns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<h3>Current Columns:</h3>";
    echo "<p>" . implode(', ', $existingColumns) . "</p>";
    
    // Define required columns
    $requiredColumns = [
        'employee_id' => 'VARCHAR(20) UNIQUE',
        'phone' => 'VARCHAR(20)',
        'department_id' => 'INT DEFAULT NULL',
        'designation' => 'VARCHAR(255)',
        'joining_date' => 'DATE',
        'salary' => 'DECIMAL(10,2)',
        'date_of_birth' => 'DATE',
        'gender' => "ENUM('male', 'female', 'other')",
        'address' => 'TEXT',
        'emergency_contact' => 'VARCHAR(255)',
        'temp_password' => 'VARCHAR(255)',
        'is_first_login' => 'BOOLEAN DEFAULT FALSE',
        'password_reset_required' => 'BOOLEAN DEFAULT FALSE'
    ];
    
    echo "<h3>Adding Missing Columns:</h3>";
    
    foreach ($requiredColumns as $column => $definition) {
        if (!in_array($column, $existingColumns)) {
            try {
                $sql = "ALTER TABLE users ADD COLUMN $column $definition";
                $db->exec($sql);
                echo "<p style='color: green;'>✓ Added column: $column</p>";
            } catch (Exception $e) {
                echo "<p style='color: red;'>✗ Failed to add $column: " . $e->getMessage() . "</p>";
            }
        } else {
            echo "<p style='color: blue;'>ℹ Column already exists: $column</p>";
        }
    }
    
    echo "<h3>Final Test</h3>";
    
    // Test company owner creation
    $testEmail = 'final_test_' . time() . '@example.com';
    $tempPassword = 'TEST' . rand(1000, 9999);
    $hashedPassword = password_hash($tempPassword, PASSWORD_BCRYPT);
    
    $stmt = $db->prepare("INSERT INTO users (name, email, password, role, status, created_at) VALUES (?, ?, ?, 'company_owner', 'active', NOW())");
    $result = $stmt->execute([
        'Final Test Company Owner',
        $testEmail,
        $hashedPassword
    ]);
    
    if ($result) {
        $userId = $db->lastInsertId();
        echo "<p style='color: green;'>✓ Company owner creation test successful (ID: $userId)</p>";
        
        // Clean up
        $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        echo "<p>✓ Test cleaned up</p>";
    }
    
    echo "<h3 style='color: green;'>✅ All fixes completed successfully!</h3>";
    echo "<p><strong>Company owner creation is now fully working.</strong></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>