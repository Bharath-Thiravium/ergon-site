<?php
require_once __DIR__ . '/app/config/database.php';

try {
    $db = Database::connect();
    
    echo "<h2>Debug User Roles</h2>";
    
    // Check all users and their roles
    $stmt = $db->prepare("SELECT id, name, role, status FROM users ORDER BY role, name");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>Name</th><th>Role</th><th>Status</th></tr>";
    foreach ($users as $user) {
        echo "<tr>";
        echo "<td>{$user['id']}</td>";
        echo "<td>{$user['name']}</td>";
        echo "<td style='font-weight: bold; color: red;'>{$user['role']}</td>";
        echo "<td>{$user['status']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Test the filtered query
    echo "<h3>Filtered Query Result (admin and user only):</h3>";
    $stmt = $db->prepare("SELECT id, name, role FROM users WHERE status = 'active' AND role IN ('admin', 'user') ORDER BY name");
    $stmt->execute();
    $filteredUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>Name</th><th>Role</th></tr>";
    foreach ($filteredUsers as $user) {
        echo "<tr>";
        echo "<td>{$user['id']}</td>";
        echo "<td>{$user['name']}</td>";
        echo "<td>{$user['role']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>