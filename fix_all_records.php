<?php
require_once __DIR__ . '/app/config/database.php';
$db = Database::connect();

// Copy clock_in to check_in and clock_out to check_out for all records
$db->exec("UPDATE attendance SET check_in = clock_in WHERE clock_in IS NOT NULL AND clock_in != '' AND (check_in IS NULL OR check_in = '')");
$db->exec("UPDATE attendance SET check_out = clock_out WHERE clock_out IS NOT NULL AND clock_out != '' AND (check_out IS NULL OR check_out = '')");

echo "Fixed all records\n";

$stmt = $db->query("SELECT COUNT(*) as c FROM attendance WHERE date='2025-12-06' AND check_in IS NOT NULL AND check_in != ''");
echo "Records with check_in: " . $stmt->fetchColumn();
