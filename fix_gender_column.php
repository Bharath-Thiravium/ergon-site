<?php
/**
 * Fix Gender Column Issue
 */

require_once __DIR__ . '/app/config/database.php';

echo "<h2>Fixing Gender Column Issue</h2>";

try {
    $db = Database::connect();
    
    // Check current gender column definition
    echo "<h3>Current Gender Column:</h3>";
    $stmt = $db->query("SHOW COLUMNS FROM users LIKE 'gender'");
    $genderColumn = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($genderColumn) {
        echo "<p>Current Type: " . $genderColumn['Type'] . "</p>";
        echo "<p>Current Null: " . $genderColumn['Null'] . "</p>";
        echo "<p>Current Default: " . $genderColumn['Default'] . "</p>";
    }
    
    echo "<h3>Fixing Gender Column:</h3>";
    
    // Fix the gender column to allow NULL and proper ENUM values
    $db->exec("ALTER TABLE users MODIFY COLUMN gender ENUM('male', 'female', 'other') NULL DEFAULT NULL");
    echo "<p style='color: green;'>✓ Gender column fixed to allow NULL values</p>";
    
    // Test the fix
    echo "<h3>Testing Fix:</h3>";
    
    $testEmail = 'gender_test_' . time() . '@example.com';
    $tempPassword = 'TEST' . rand(1000, 9999);
    $hashedPassword = password_hash($tempPassword, PASSWORD_BCRYPT);
    
    // Test with male gender
    $stmt = $db->prepare("INSERT INTO users (name, email, password, role, gender, status, created_at) VALUES (?, ?, ?, 'company_owner', 'male', 'active', NOW())");
    $result1 = $stmt->execute([
        'Test Male User',
        $testEmail,
        $hashedPassword
    ]);
    
    if ($result1) {
        $userId1 = $db->lastInsertId();
        echo "<p style='color: green;'>✓ Test with 'male' gender successful (ID: $userId1)</p>";
        $db->prepare("DELETE FROM users WHERE id = ?")->execute([$userId1]);
    }
    
    // Test with NULL gender
    $testEmail2 = 'gender_null_' . time() . '@example.com';
    $stmt = $db->prepare("INSERT INTO users (name, email, password, role, gender, status, created_at) VALUES (?, ?, ?, 'company_owner', NULL, 'active', NOW())");
    $result2 = $stmt->execute([
        'Test Null Gender User',
        $testEmail2,
        $hashedPassword
    ]);
    
    if ($result2) {
        $userId2 = $db->lastInsertId();
        echo "<p style='color: green;'>✓ Test with NULL gender successful (ID: $userId2)</p>";
        $db->prepare("DELETE FROM users WHERE id = ?")->execute([$userId2]);
    }
    
    echo "<h3 style='color: green;'>✅ Gender Column Fixed!</h3>";
    echo "<p><strong>Company owner creation should now work properly.</strong></p>";
    echo "<p><a href='/ergon-site/users/create'>Try creating a company owner now</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>