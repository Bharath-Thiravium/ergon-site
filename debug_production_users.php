<?php
require_once 'app/config/database.php';
require_once 'app/config/environment.php';

echo "<h2>Production Users Debug</h2>";
echo "<p>Environment: " . Environment::detect() . "</p>";
echo "<p>Host: " . ($_SERVER['HTTP_HOST'] ?? 'unknown') . "</p>";
echo "<p>Document Root: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'unknown') . "</p>";
echo "<hr>";

try {
    $pdo = Database::connect();
    
    // 1. Show all active users with their roles
    echo "<h3>1. All Active Users in Production:</h3>";
    $stmt = $pdo->prepare("SELECT id, name, email, role FROM users WHERE status = 'active' ORDER BY role, name");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Should Exclude?</th><th>Reason</th></tr>";
    
    foreach ($users as $user) {
        $shouldExclude = false;
        $reason = '';
        
        // Check role-based exclusion
        if ($user['role'] === 'owner' || $user['role'] === 'company_owner') {
            $shouldExclude = true;
            $reason = 'Role: ' . $user['role'];
        }
        
        // Check name-based exclusion
        if (!$shouldExclude && $user['name']) {
            $nameLower = strtolower($user['name']);
            if (strpos($nameLower, 'owner') !== false) {
                $shouldExclude = true;
                $reason = 'Name contains "owner"';
            } elseif (strpos($nameLower, 'admin') !== false && $user['email'] && strpos($user['email'], 'ergon') !== false) {
                $shouldExclude = true;
                $reason = 'Name contains "admin" + ergon email';
            }
        }
        
        // Check email-based exclusion
        if (!$shouldExclude && $user['email']) {
            if (strpos($user['email'], 'owner') !== false || strpos($user['email'], 'admin@ergon') !== false) {
                $shouldExclude = true;
                $reason = 'Email pattern match';
            }
        }
        
        $bgColor = $shouldExclude ? '#ffcccc' : '#ccffcc';
        $excludeText = $shouldExclude ? '❌ YES' : '✅ NO';
        
        echo "<tr style='background-color: $bgColor;'>";
        echo "<td>" . htmlspecialchars($user['id']) . "</td>";
        echo "<td>" . htmlspecialchars($user['name']) . "</td>";
        echo "<td>" . htmlspecialchars($user['email']) . "</td>";
        echo "<td>" . htmlspecialchars($user['role']) . "</td>";
        echo "<td>$excludeText</td>";
        echo "<td>" . htmlspecialchars($reason) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // 2. Test API query result
    echo "<h3>2. API Query Result (admin/user roles only):</h3>";
    $stmt = $pdo->prepare("SELECT id, name, email, role FROM users WHERE status = 'active' AND role IN ('admin', 'user') ORDER BY name ASC");
    $stmt->execute();
    $apiUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p>Query: <code>SELECT id, name, email, role FROM users WHERE status = 'active' AND role IN ('admin', 'user')</code></p>";
    echo "<p>Results count: " . count($apiUsers) . "</p>";
    
    if (empty($apiUsers)) {
        echo "<p style='color: red;'>⚠️ API query returns no users!</p>";
    } else {
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th></tr>";
        foreach ($apiUsers as $user) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($user['id']) . "</td>";
            echo "<td>" . htmlspecialchars($user['name']) . "</td>";
            echo "<td>" . htmlspecialchars($user['email']) . "</td>";
            echo "<td>" . htmlspecialchars($user['role']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // 3. Generate JavaScript filtering test
    echo "<h3>3. JavaScript Filtering Test:</h3>";
    echo "<p>This simulates what the client-side filtering should do:</p>";
    
    $filteredUsers = [];
    foreach ($users as $user) {
        $skip = false;
        
        // Role check
        if ($user['role'] === 'owner' || $user['role'] === 'company_owner') {
            $skip = true;
        }
        
        // Name check
        if (!$skip && $user['name']) {
            $nameLower = strtolower($user['name']);
            if (strpos($nameLower, 'owner') !== false || 
                (strpos($nameLower, 'admin') !== false && $user['email'] && strpos($user['email'], 'ergon') !== false)) {
                $skip = true;
            }
        }
        
        // Email check
        if (!$skip && $user['email']) {
            if (strpos($user['email'], 'owner') !== false || strpos($user['email'], 'admin@ergon') !== false) {
                $skip = true;
            }
        }
        
        if (!$skip) {
            $filteredUsers[] = $user;
        }
    }
    
    echo "<p>Filtered count: " . count($filteredUsers) . "</p>";
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th></tr>";
    foreach ($filteredUsers as $user) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($user['id']) . "</td>";
        echo "<td>" . htmlspecialchars($user['name']) . "</td>";
        echo "<td>" . htmlspecialchars($user['email']) . "</td>";
        echo "<td>" . htmlspecialchars($user['role']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // 4. Generate exact JavaScript code for production
    echo "<h3>4. Exact JavaScript Filter Code for Production:</h3>";
    $ownerIds = [];
    foreach ($users as $user) {
        if ($user['role'] === 'owner' || $user['role'] === 'company_owner') {
            $ownerIds[] = $user['id'];
        }
    }
    
    echo "<pre>";
    echo "// Add this to both create.php and edit.php:\n";
    echo "// Skip specific owner IDs: " . implode(', ', $ownerIds) . "\n";
    echo "if (user.id == " . implode(' || user.id == ', $ownerIds) . " ||\n";
    echo "    (user.role && (user.role === 'owner' || user.role === 'company_owner')) ||\n";
    echo "    (user.name && user.name.toLowerCase().includes('owner')) ||\n";
    echo "    (user.email && user.email.includes('owner'))) {\n";
    echo "    return;\n";
    echo "}\n";
    echo "</pre>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<hr>";
echo "<p><strong>Upload this file to production server and run it to debug the issue.</strong></p>";
?>