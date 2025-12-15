<?php
session_start();
require_once __DIR__ . '/app/config/database.php';

$db = Database::connect();
$filterDate = '2025-12-15';

// Simulate exact controller query
$stmt = $db->prepare("SELECT name, place FROM projects WHERE status = 'active' ORDER BY name ASC LIMIT 1");
$stmt->execute();
$defaultProject = $stmt->fetch(PDO::FETCH_ASSOC);

$defaultProjectName = $defaultProject['name'] ?? '----';
$defaultLocation = $defaultProject['place'] ?? '---';

$stmt = $db->prepare("
    SELECT 
        u.name,
        u.id as user_id,
        COALESCE(p.place, ?) as location_display,
        COALESCE(p.name, ?) as project_name,
        a.check_in,
        CASE 
            WHEN a.check_in IS NOT NULL THEN 'Present'
            ELSE 'Absent'
        END as status
    FROM users u
    LEFT JOIN attendance a ON u.id = a.user_id AND DATE(a.check_in) = ?
    LEFT JOIN projects p ON a.project_id = p.id
    WHERE u.role = 'user' AND u.status = 'active'
    ORDER BY u.name
");
$stmt->execute([$defaultLocation, $defaultProjectName, $filterDate]);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Controller data:<br>";
foreach ($results as $r) {
    echo "User: {$r['name']} | Location: '{$r['location_display']}' | Project: '{$r['project_name']}' | Status: {$r['status']}<br>";
}
?>