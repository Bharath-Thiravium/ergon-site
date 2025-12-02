<?php
/**
 * Debug Users Index Query
 */

session_start();
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'owner';

require_once __DIR__ . '/app/config/database.php';

echo "<h2>Debug Users Index Query</h2>";

try {
    $db = Database::connect();
    
    // Test the exact same query from UsersController::index()
    echo "<h3>Testing UsersController Query</h3>";
    $stmt = $db->prepare("SELECT DISTINCT u.*, d.name as department_name FROM users u LEFT JOIN departments d ON u.department_id = d.id WHERE u.status != 'deleted' ORDER BY u.created_at DESC");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p>Query returned " . count($users) . " users</p>";
    
    if (!empty($users)) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Department</th></tr>";
        foreach ($users as $user) {
            echo "<tr>";
            echo "<td>" . $user['id'] . "</td>";
            echo "<td>" . htmlspecialchars($user['name']) . "</td>";
            echo "<td>" . htmlspecialchars($user['email']) . "</td>";
            echo "<td>" . htmlspecialchars($user['role']) . "</td>";
            echo "<td>" . htmlspecialchars($user['status']) . "</td>";
            echo "<td>" . htmlspecialchars($user['department_name'] ?? 'None') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Test deduplication logic
    echo "<h3>After Deduplication</h3>";
    $uniqueUsers = [];
    foreach ($users as $user) {
        $uniqueUsers[$user['id']] = $user;
    }
    $users = array_values($uniqueUsers);
    
    echo "<p>After deduplication: " . count($users) . " users</p>";
    
    // Test the actual controller
    echo "<h3>Testing Actual Controller</h3>";
    
    require_once __DIR__ . '/app/controllers/UsersController.php';
    
    ob_start();
    $controller = new UsersController();
    $controller->index();
    $output = ob_get_clean();
    
    echo "<p>Controller executed. Output length: " . strlen($output) . " characters</p>";
    
    if (strlen($output) > 0) {
        echo "<h4>Controller Output (first 500 chars):</h4>";
        echo "<pre>" . htmlspecialchars(substr($output, 0, 500)) . "...</pre>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>