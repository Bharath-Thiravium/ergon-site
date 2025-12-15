<?php
require_once __DIR__ . '/app/config/database.php';
$db = Database::connect();

// Test what Nelson should show
$stmt = $db->prepare("SELECT u.name, COALESCE(p.place, 'Default') as location_display, COALESCE(p.name, 'Default') as project_name FROM users u LEFT JOIN attendance a ON u.id = a.user_id AND DATE(a.check_in) = '2025-12-15' LEFT JOIN projects p ON a.project_id = p.id WHERE u.id = 57");
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);

echo "Nelson should show: Location='{$result['location_display']}' Project='{$result['project_name']}'<br>";
echo "If still showing '---' and '----', it's a view template cache issue.";
?>