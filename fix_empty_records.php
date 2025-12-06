<?php
require_once __DIR__ . '/app/config/database.php';
$db = Database::connect();

// Find and update empty records for today
$db->exec("SET sql_mode = ''");
$stmt = $db->prepare("UPDATE attendance SET check_in = CONCAT(date, ' 09:00:00'), manual_entry = 1 WHERE date = '2025-12-06' AND (check_in = '' OR check_in IS NULL)");
$stmt->execute();
echo "Fixed empty records\n";

// Show current state
$stmt = $db->query("SELECT id, user_id, date, check_in, manual_entry FROM attendance WHERE date='2025-12-06'");
while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "ID {$r['id']}: user={$r['user_id']}, check_in={$r['check_in']}, manual={$r['manual_entry']}\n";
}
