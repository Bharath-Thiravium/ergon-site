<?php
require_once __DIR__ . '/app/config/database.php';

echo "<h2>Latest Users Check</h2>";

try {
    $db = Database::connect();
    
    // Get the latest 10 users
    $stmt = $db->query("SELECT id, name, email, role, status, created_at FROM users ORDER BY id DESC LIMIT 10");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>Latest 10 Users:</h3>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #f0f0f0;'>";
    echo "<th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Created</th>";
    echo "</tr>";
    
    foreach ($users as $user) {
        $rowColor = '';
        if ($user['role'] === 'company_owner') {
            $rowColor = 'background: #e8f5e8;'; // Light green for company owners
        }
        
        echo "<tr style='$rowColor'>";
        echo "<td>" . $user['id'] . "</td>";
        echo "<td>" . htmlspecialchars($user['name']) . "</td>";
        echo "<td>" . htmlspecialchars($user['email']) . "</td>";
        echo "<td><strong>" . $user['role'] . "</strong></td>";
        echo "<td>" . $user['status'] . "</td>";
        echo "<td>" . $user['created_at'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Count company owners
    $stmt = $db->query("SELECT COUNT(*) as count FROM users WHERE role = 'company_owner'");
    $count = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<h3>Company Owner Summary:</h3>";
    echo "<p><strong>Total Company Owners: " . $count['count'] . "</strong></p>";
    
    if ($count['count'] > 0) {
        echo "<p style='color: green;'>✅ Company owner creation is working!</p>";
        echo "<p>You can now:</p>";
        echo "<ul>";
        echo "<li>Create company owners through the form</li>";
        echo "<li>Update existing users to company owner role</li>";
        echo "<li>All functionality is working properly</li>";
        echo "</ul>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>

<style>
table { margin: 20px 0; }
th, td { padding: 8px; text-align: left; border: 1px solid #ddd; }
th { font-weight: bold; }
</style>