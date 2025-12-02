<?php
/**
 * Debug Company Owner Login
 */

require_once __DIR__ . '/app/config/database.php';

echo "<h2>Company Owner Login Debug</h2>";

try {
    $db = Database::connect();
    
    // Get the latest company owner
    $stmt = $db->query("SELECT id, name, email, password, role, status FROM users WHERE role = 'company_owner' ORDER BY created_at DESC LIMIT 1");
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        echo "<h3>Latest Company Owner:</h3>";
        echo "<p><strong>ID:</strong> " . $user['id'] . "</p>";
        echo "<p><strong>Name:</strong> " . htmlspecialchars($user['name']) . "</p>";
        echo "<p><strong>Email:</strong> " . htmlspecialchars($user['email']) . "</p>";
        echo "<p><strong>Role:</strong> " . htmlspecialchars($user['role']) . "</p>";
        echo "<p><strong>Status:</strong> " . htmlspecialchars($user['status']) . "</p>";
        
        // Check if there are stored credentials in session
        session_start();
        if (isset($_SESSION['new_credentials'])) {
            echo "<h3>Stored Credentials:</h3>";
            echo "<p><strong>Email:</strong> " . htmlspecialchars($_SESSION['new_credentials']['email']) . "</p>";
            echo "<p><strong>Password:</strong> " . htmlspecialchars($_SESSION['new_credentials']['password']) . "</p>";
            echo "<p><strong>Employee ID:</strong> " . htmlspecialchars($_SESSION['new_credentials']['employee_id']) . "</p>";
        } else {
            echo "<h3>No stored credentials found in session</h3>";
            echo "<p>The temporary password was generated but not stored in session.</p>";
            
            // Generate a new temporary password for this user
            $tempPassword = 'PWD' . rand(1000, 9999);
            $hashedPassword = password_hash($tempPassword, PASSWORD_BCRYPT);
            
            $stmt = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
            $result = $stmt->execute([$hashedPassword, $user['id']]);
            
            if ($result) {
                echo "<h3>New Temporary Password Generated:</h3>";
                echo "<p><strong>Email:</strong> " . htmlspecialchars($user['email']) . "</p>";
                echo "<p><strong>Password:</strong> " . htmlspecialchars($tempPassword) . "</p>";
                echo "<p style='color: green;'>Password updated successfully. You can now login with these credentials.</p>";
            } else {
                echo "<p style='color: red;'>Failed to update password</p>";
            }
        }
        
        // Test password verification
        echo "<h3>Password Hash Info:</h3>";
        echo "<p><strong>Hash:</strong> " . substr($user['password'], 0, 50) . "...</p>";
        echo "<p><strong>Hash Length:</strong> " . strlen($user['password']) . "</p>";
        
    } else {
        echo "<p>No company owner found in database</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>