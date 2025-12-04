<?php
// Simple test to check if API route is working
session_start();

// Simulate being logged in (replace with actual user ID)
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'owner';

// Test the API endpoint
$userId = 1; // Replace with actual user ID
$url = "http://localhost/ergon-site/api/users/{$userId}";

$context = stream_context_create([
    'http' => [
        'method' => 'GET',
        'header' => [
            'Cookie: ' . session_name() . '=' . session_id()
        ]
    ]
]);

$response = file_get_contents($url, false, $context);
echo "Response: " . $response . "\n";

// Also test direct database connection
require_once __DIR__ . '/app/config/database.php';
try {
    $db = Database::connect();
    $stmt = $db->prepare("SELECT id, name, email FROM users LIMIT 1");
    $stmt->execute();
    $user = $stmt->fetch();
    echo "Direct DB test: " . json_encode($user) . "\n";
} catch (Exception $e) {
    echo "DB Error: " . $e->getMessage() . "\n";
}
?>