<?php
require_once 'app/config/database.php';
require_once 'app/config/environment.php';

echo "<h2>Task Users Debug</h2>";
echo "<p>Environment: " . Environment::detect() . "</p>";
echo "<hr>";

try {
    $pdo = Database::connect();
    
    // 1. Check what getActiveUsers() method returns
    echo "<h3>1. getActiveUsers() Query Result:</h3>";
    $stmt = $pdo->prepare("SELECT id, name, email FROM users WHERE status = 'active' AND role IN ('admin', 'user') ORDER BY name");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p>Query: <code>SELECT id, name, email FROM users WHERE status = 'active' AND role IN ('admin', 'user') ORDER BY name</code></p>";
    echo "<p>Results count: " . count($users) . "</p>";
    
    if (empty($users)) {
        echo "<p style='color: red;'>⚠️ No users found! This is why dropdown is empty.</p>";
    } else {
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>Name</th><th>Email</th></tr>";
        foreach ($users as $user) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($user['id']) . "</td>";
            echo "<td>" . htmlspecialchars($user['name']) . "</td>";
            echo "<td>" . htmlspecialchars($user['email']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // 2. Check API endpoint result
    echo "<h3>2. API Endpoint (/api/users.php) Result:</h3>";
    echo "<p>This should match the above query result.</p>";
    
    // 3. Check all active users regardless of role
    echo "<h3>3. All Active Users (Any Role):</h3>";
    $stmt = $pdo->prepare("SELECT id, name, email, role FROM users WHERE status = 'active' ORDER BY name");
    $stmt->execute();
    $allUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Excluded?</th></tr>";
    foreach ($allUsers as $user) {
        $excluded = in_array($user['role'], ['owner', 'company_owner']);
        $highlight = $excluded ? 'background-color: #ffcccc;' : '';
        echo "<tr style='$highlight'>";
        echo "<td>" . htmlspecialchars($user['id']) . "</td>";
        echo "<td>" . htmlspecialchars($user['name']) . "</td>";
        echo "<td>" . htmlspecialchars($user['email']) . "</td>";
        echo "<td>" . htmlspecialchars($user['role']) . "</td>";
        echo "<td>" . ($excluded ? '❌ YES' : '✅ NO') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<hr>";
echo "<p><strong>Next Steps:</strong></p>";
echo "<ul>";
echo "<li>If section 1 is empty, no admin/user role users exist</li>";
echo "<li>If section 1 has users but dropdown is empty, it's a JavaScript/API issue</li>";
echo "<li>Check section 3 to see which users are being excluded</li>";
echo "</ul>";
?>