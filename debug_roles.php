<?php
require_once 'app/config/database.php';
require_once 'app/config/environment.php';

echo "<h2>Role Debug Script</h2>";
echo "<p>Environment: " . Environment::detect() . "</p>";
echo "<p>Host: " . ($_SERVER['HTTP_HOST'] ?? 'unknown') . "</p>";
echo "<hr>";

try {
    $pdo = Database::connect();
    
    // 1. Check all roles in database
    echo "<h3>1. All Roles in Database:</h3>";
    $stmt = $pdo->query("SELECT * FROM roles ORDER BY id");
    $allRoles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>Name</th><th>Display Name</th><th>Description</th></tr>";
    foreach ($allRoles as $role) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($role['id']) . "</td>";
        echo "<td>" . htmlspecialchars($role['name']) . "</td>";
        echo "<td>" . htmlspecialchars($role['display_name'] ?? 'N/A') . "</td>";
        echo "<td>" . htmlspecialchars($role['description'] ?? 'N/A') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // 2. Check roles that should be excluded
    echo "<h3>2. Roles That Should Be Excluded:</h3>";
    $excludedRoles = ['owner', 'company_owner'];
    $placeholders = str_repeat('?,', count($excludedRoles) - 1) . '?';
    $stmt = $pdo->prepare("SELECT * FROM roles WHERE name IN ($placeholders)");
    $stmt->execute($excludedRoles);
    $excluded = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($excluded)) {
        echo "<p style='color: green;'>✓ No excluded roles found in database</p>";
    } else {
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>Name</th><th>Display Name</th></tr>";
        foreach ($excluded as $role) {
            echo "<tr style='background-color: #ffcccc;'>";
            echo "<td>" . htmlspecialchars($role['id']) . "</td>";
            echo "<td>" . htmlspecialchars($role['name']) . "</td>";
            echo "<td>" . htmlspecialchars($role['display_name'] ?? 'N/A') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // 3. Test the actual query used in dropdown
    echo "<h3>3. Dropdown Query Result:</h3>";
    $stmt = $pdo->prepare("SELECT * FROM roles WHERE name NOT IN ('owner', 'company_owner') ORDER BY name");
    $stmt->execute();
    $dropdownRoles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p>Query: <code>SELECT * FROM roles WHERE name NOT IN ('owner', 'company_owner') ORDER BY name</code></p>";
    echo "<p>Results count: " . count($dropdownRoles) . "</p>";
    
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>Name</th><th>Display Name</th></tr>";
    foreach ($dropdownRoles as $role) {
        $highlight = in_array($role['name'], ['owner', 'company_owner']) ? 'background-color: #ffcccc;' : '';
        echo "<tr style='$highlight'>";
        echo "<td>" . htmlspecialchars($role['id']) . "</td>";
        echo "<td>" . htmlspecialchars($role['name']) . "</td>";
        echo "<td>" . htmlspecialchars($role['display_name'] ?? 'N/A') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // 4. Check for case sensitivity issues
    echo "<h3>4. Case Sensitivity Check:</h3>";
    $stmt = $pdo->query("SELECT name, LOWER(name) as lower_name FROM roles");
    $caseCheck = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Original Name</th><th>Lowercase</th><th>Match Issue?</th></tr>";
    foreach ($caseCheck as $role) {
        $isIssue = in_array(strtolower($role['name']), ['owner', 'company_owner']) && 
                   !in_array($role['name'], ['owner', 'company_owner']);
        $highlight = $isIssue ? 'background-color: #ffcccc;' : '';
        echo "<tr style='$highlight'>";
        echo "<td>" . htmlspecialchars($role['name']) . "</td>";
        echo "<td>" . htmlspecialchars($role['lower_name']) . "</td>";
        echo "<td>" . ($isIssue ? '⚠️ Case mismatch' : '✓ OK') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // 5. Check for whitespace issues
    echo "<h3>5. Whitespace Check:</h3>";
    $stmt = $pdo->query("SELECT name, LENGTH(name) as length, TRIM(name) as trimmed FROM roles");
    $whitespaceCheck = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Name</th><th>Length</th><th>Trimmed</th><th>Issue?</th></tr>";
    foreach ($whitespaceCheck as $role) {
        $hasWhitespace = $role['name'] !== $role['trimmed'];
        $highlight = $hasWhitespace ? 'background-color: #ffcccc;' : '';
        echo "<tr style='$highlight'>";
        echo "<td>'" . htmlspecialchars($role['name']) . "'</td>";
        echo "<td>" . $role['length'] . "</td>";
        echo "<td>'" . htmlspecialchars($role['trimmed']) . "'</td>";
        echo "<td>" . ($hasWhitespace ? '⚠️ Has whitespace' : '✓ OK') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<hr>";
echo "<p><strong>Instructions:</strong></p>";
echo "<ul>";
echo "<li>Check section 2 - if excluded roles are found, they exist in database</li>";
echo "<li>Check section 3 - if owner/company_owner appear here, the query isn't working</li>";
echo "<li>Check section 4 - for case sensitivity issues (Owner vs owner)</li>";
echo "<li>Check section 5 - for hidden whitespace characters</li>";
echo "</ul>";
?>