<?php
/**
 * Debug User Creation Process
 */

require_once __DIR__ . '/app/config/database.php';

echo "<h2>Debug User Creation Process</h2>";

try {
    $db = Database::connect();
    
    // 1. Check current users table structure
    echo "<h3>1. Users Table Structure</h3>";
    $stmt = $db->query("DESCRIBE users");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    foreach ($columns as $col) {
        echo "<tr>";
        echo "<td>" . $col['Field'] . "</td>";
        echo "<td>" . $col['Type'] . "</td>";
        echo "<td>" . $col['Null'] . "</td>";
        echo "<td>" . $col['Key'] . "</td>";
        echo "<td>" . $col['Default'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // 2. Test manual user creation with minimal data
    echo "<h3>2. Test Manual User Creation</h3>";
    
    $testEmail = 'debug_test_' . time() . '@example.com';
    $testPassword = password_hash('test123', PASSWORD_BCRYPT);
    
    // Try with minimal required fields first
    echo "<h4>Test 1: Minimal Fields</h4>";
    try {
        $stmt = $db->prepare("INSERT INTO users (name, email, password, role, status) VALUES (?, ?, ?, ?, ?)");
        $result = $stmt->execute([
            'Debug Test User',
            $testEmail,
            $testPassword,
            'company_owner',
            'active'
        ]);
        
        if ($result) {
            $userId = $db->lastInsertId();
            echo "<p style='color: green;'>✓ Minimal creation successful (ID: $userId)</p>";
            
            // Clean up
            $db->prepare("DELETE FROM users WHERE id = ?")->execute([$userId]);
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>✗ Minimal creation failed: " . $e->getMessage() . "</p>";
    }
    
    // 3. Test with all fields that the form might send
    echo "<h4>Test 2: Full Form Data</h4>";
    $testEmail2 = 'debug_full_' . time() . '@example.com';
    
    try {
        $stmt = $db->prepare("
            INSERT INTO users (
                employee_id, name, email, password, phone, role, status, 
                department_id, designation, joining_date, salary, 
                date_of_birth, gender, address, emergency_contact, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        
        $result = $stmt->execute([
            'EMP999',
            'Debug Full Test',
            $testEmail2,
            $testPassword,
            '1234567890',
            'company_owner',
            'active',
            null, // department_id
            'Test Designation',
            '2024-01-01', // joining_date
            50000.00, // salary
            '1990-01-01', // date_of_birth
            'male',
            'Test Address',
            '9876543210'
        ]);
        
        if ($result) {
            $userId = $db->lastInsertId();
            echo "<p style='color: green;'>✓ Full creation successful (ID: $userId)</p>";
            
            // Clean up
            $db->prepare("DELETE FROM users WHERE id = ?")->execute([$userId]);
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>✗ Full creation failed: " . $e->getMessage() . "</p>";
    }
    
    // 4. Check if there are any constraints or triggers
    echo "<h3>3. Check Table Constraints</h3>";
    
    $stmt = $db->query("SHOW CREATE TABLE users");
    $createTable = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<pre>" . htmlspecialchars($createTable['Create Table']) . "</pre>";
    
    // 5. Check error logs
    echo "<h3>4. Recent Error Logs</h3>";
    $errorLog = error_get_last();
    if ($errorLog) {
        echo "<pre>" . print_r($errorLog, true) . "</pre>";
    } else {
        echo "<p>No recent PHP errors</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Debug Error: " . $e->getMessage() . "</p>";
}
?>
