<?php
require_once 'app/config/database.php';
require_once 'app/config/environment.php';

echo "<h2>Database Tables Debug</h2>";
echo "<p>Environment: " . Environment::detect() . "</p>";
echo "<p>Host: " . ($_SERVER['HTTP_HOST'] ?? 'unknown') . "</p>";
echo "<hr>";

try {
    $pdo = Database::connect();
    
    // 1. Show all tables
    echo "<h3>1. All Tables in Database:</h3>";
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($tables)) {
        echo "<p style='color: red;'>No tables found!</p>";
    } else {
        echo "<ul>";
        foreach ($tables as $table) {
            echo "<li>" . htmlspecialchars($table) . "</li>";
        }
        echo "</ul>";
    }
    
    // 2. Look for role-related tables
    echo "<h3>2. Role-Related Tables:</h3>";
    $rolesTables = array_filter($tables, function($table) {
        return stripos($table, 'role') !== false || stripos($table, 'user') !== false;
    });
    
    if (empty($rolesTables)) {
        echo "<p>No role-related tables found</p>";
    } else {
        foreach ($rolesTables as $table) {
            echo "<h4>Table: $table</h4>";
            $stmt = $pdo->query("DESCRIBE `$table`");
            $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "<table border='1' style='border-collapse: collapse;'>";
            echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
            foreach ($columns as $col) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($col['Field']) . "</td>";
                echo "<td>" . htmlspecialchars($col['Type']) . "</td>";
                echo "<td>" . htmlspecialchars($col['Null']) . "</td>";
                echo "<td>" . htmlspecialchars($col['Key']) . "</td>";
                echo "<td>" . htmlspecialchars($col['Default'] ?? 'NULL') . "</td>";
                echo "</tr>";
            }
            echo "</table>";
            
            // Show sample data
            $stmt = $pdo->query("SELECT * FROM `$table` LIMIT 10");
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (!empty($data)) {
                echo "<h5>Sample Data:</h5>";
                echo "<table border='1' style='border-collapse: collapse;'>";
                echo "<tr>";
                foreach (array_keys($data[0]) as $header) {
                    echo "<th>" . htmlspecialchars($header) . "</th>";
                }
                echo "</tr>";
                foreach ($data as $row) {
                    echo "<tr>";
                    foreach ($row as $value) {
                        echo "<td>" . htmlspecialchars($value ?? 'NULL') . "</td>";
                    }
                    echo "</tr>";
                }
                echo "</table>";
            }
            echo "<br>";
        }
    }
    
    // 3. Check users table for role column
    if (in_array('users', $tables)) {
        echo "<h3>3. Users Table Role Data:</h3>";
        $stmt = $pdo->query("SELECT DISTINCT role FROM users WHERE role IS NOT NULL");
        $roles = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        echo "<p>Distinct roles in users table:</p>";
        echo "<ul>";
        foreach ($roles as $role) {
            echo "<li>" . htmlspecialchars($role) . "</li>";
        }
        echo "</ul>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<hr>";
echo "<p><strong>Next Steps:</strong></p>";
echo "<ul>";
echo "<li>Check if roles are stored in users table or another table</li>";
echo "<li>Look for role column in users table</li>";
echo "<li>Find where the dropdown is getting role data from</li>";
echo "</ul>";
?>