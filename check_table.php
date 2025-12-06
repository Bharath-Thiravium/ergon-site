<?php
require_once __DIR__ . '/app/config/database.php';

$db = Database::connect();

$stmt = $db->query("DESCRIBE attendance_logs");
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Current attendance_logs columns:\n";
foreach ($columns as $col) {
    echo "- " . $col['Field'] . " (" . $col['Type'] . ")\n";
}
