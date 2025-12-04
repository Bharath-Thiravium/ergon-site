<?php
/**
 * Check Latest Users in Database
 */

require_once __DIR__ . '/app/config/database.php';

echo "<h2>Latest Users in Database</h2>";

try {
    $db = Database::connect();
    
    // Check all users (including any status)
    echo "<h3>All Users (Last 10)</h3>";
    $stmt = $db->query("SELECT id, name, email, role, status, created_at FROM users ORDER BY created_at DESC LIMIT 10");
    $allUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($allUsers)) {
        echo "<p>No users found in database</p>";
    } else {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Created</th></tr>";
        foreach ($allUsers as $user) {
            echo "<tr>";
            echo "<td>" . $user['id'] . "</td>";
            echo "<td>" . htmlspecialchars($user['name']) . "</td>";
            echo "<td>" . htmlspecialchars($user['email']) . "</td>";
            echo "<td>" . htmlspecialchars($user['role']) . "</td>";
            echo "<td>" . htmlspecialchars($user['status']) . "</td>";
            echo "<td>" . $user['created_at'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Check users that would be shown in the index (status != 'deleted')
    echo "<h3>Users Shown in Index (status != 'deleted')</h3>";
    $stmt = $db->query("SELECT id, name, email, role, status, created_at FROM users WHERE status != 'deleted' ORDER BY created_at DESC LIMIT 10");
    $indexUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($indexUsers)) {
        echo "<p>No users found with status != 'deleted'</p>";
    } else {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Created</th></tr>";
        foreach ($indexUsers as $user) {
            echo "<tr>";
            echo "<td>" . $user['id'] . "</td>";
            echo "<td>" . htmlspecialchars($user['name']) . "</td>";
            echo "<td>" . htmlspecialchars($user['email']) . "</td>";
            echo "<td>" . htmlspecialchars($user['role']) . "</td>";
            echo "<td>" . htmlspecialchars($user['status']) . "</td>";
            echo "<td>" . $user['created_at'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Check count
    $stmt = $db->query("SELECT COUNT(*) as total FROM users");
    $total = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<h3>Total Users: " . $total['total'] . "</h3>";
    
    $stmt = $db->query("SELECT COUNT(*) as total FROM users WHERE status != 'deleted'");
    $activeTotal = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<h3>Active Users (status != 'deleted'): " . $activeTotal['total'] . "</h3>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>
