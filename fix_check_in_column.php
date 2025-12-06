<?php
require_once __DIR__ . '/app/config/database.php';
$db = Database::connect();

$db->exec("SET SESSION sql_mode = 'NO_ENGINE_SUBSTITUTION'");
$db->exec("ALTER TABLE attendance MODIFY COLUMN check_in DATETIME NULL DEFAULT NULL");
$db->exec("ALTER TABLE attendance MODIFY COLUMN check_out DATETIME NULL DEFAULT NULL");
$db->exec("UPDATE attendance SET check_in = NULL WHERE check_in = '' OR check_in = '0000-00-00 00:00:00'");
$db->exec("UPDATE attendance SET check_out = NULL WHERE check_out = '' OR check_out = '0000-00-00 00:00:00'");

echo "Fixed check_in column\n";

$stmt = $db->query("SELECT COUNT(*) as c FROM attendance WHERE date='2025-12-06' AND check_in IS NULL");
echo "Records with NULL check_in: " . $stmt->fetch()['c'] . "\n";

$db->exec("DELETE FROM attendance WHERE date='2025-12-06' AND check_in IS NULL");
echo "Deleted empty records\n";
