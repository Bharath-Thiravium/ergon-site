<?php
require_once 'app/config/database.php';
require_once 'app/config/environment.php';

header('Content-Type: application/json');

echo "<h2>API Users Test (No Auth)</h2>";
echo "<p>Testing the same query as /api/users.php</p>";
echo "<hr>";

try {
    $db = Database::connect();
    
    // Same query as in api/users.php
    $stmt = $db->prepare("SELECT id, name, email FROM users WHERE status = 'active' AND role IN ('admin', 'user') ORDER BY name ASC");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>Filtered Users (admin/user only):</h3>";
    echo "<pre>" . json_encode(['success' => true, 'users' => $users], JSON_PRETTY_PRINT) . "</pre>";
    
    // Also test what happens if we remove the role filter
    echo "<h3>All Active Users (any role):</h3>";
    $stmt = $db->prepare("SELECT id, name, email, role FROM users WHERE status = 'active' ORDER BY name ASC");
    $stmt->execute();
    $allUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<pre>" . json_encode(['success' => true, 'users' => $allUsers], JSON_PRETTY_PRINT) . "</pre>";
    
} catch (Exception $e) {
    echo "<pre>" . json_encode(['success' => false, 'error' => $e->getMessage()], JSON_PRETTY_PRINT) . "</pre>";
}
?>