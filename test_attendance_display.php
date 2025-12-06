<?php
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'owner';

require_once __DIR__ . '/app/config/database.php';
$db = Database::connect();

$filterDate = '2025-12-06';
$roleFilter = "u.role IN ('admin', 'user', 'owner')";

$stmt = $db->prepare("
    SELECT 
        u.id,
        u.name,
        u.email,
        a.check_in,
        a.check_out,
        CASE 
            WHEN a.check_in IS NOT NULL THEN 'Present'
            ELSE 'Absent'
        END as status
    FROM users u
    LEFT JOIN attendance a ON u.id = a.user_id AND (DATE(a.check_in) = ? OR a.date = ?)
    WHERE $roleFilter AND u.status = 'active'
    ORDER BY u.name
    LIMIT 5
");
$stmt->execute([$filterDate, $filterDate]);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Query Results:\n";
foreach ($results as $r) {
    echo "{$r['name']}: check_in={$r['check_in']}, status={$r['status']}\n";
}
