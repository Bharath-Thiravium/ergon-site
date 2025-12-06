<?php
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
        a.date,
        CASE 
            WHEN a.check_in IS NOT NULL THEN 'Present'
            ELSE 'Absent'
        END as status
    FROM users u
    LEFT JOIN attendance a ON u.id = a.user_id AND (DATE(a.check_in) = ? OR a.date = ?)
    WHERE $roleFilter AND u.status = 'active' AND u.id IN (16, 37)
    ORDER BY u.name
");
$stmt->execute([$filterDate, $filterDate]);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($results as $r) {
    echo "{$r['name']}: check_in={$r['check_in']}, status={$r['status']}\n";
}
