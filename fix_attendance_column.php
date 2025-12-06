<?php
require_once __DIR__ . '/app/config/database.php';
$db = Database::connect();

// Modify check_in to allow NULL
$db->exec("ALTER TABLE attendance MODIFY check_in DATETIME NULL");
$db->exec("ALTER TABLE attendance MODIFY check_out DATETIME NULL");

// Update empty strings to NULL
$db->exec("UPDATE attendance SET check_in = NULL WHERE check_in = ''");
$db->exec("UPDATE attendance SET check_out = NULL WHERE check_out = ''");

echo "Fixed column definitions\n";

// Now update the records for today
$db->exec("UPDATE attendance SET check_in = '2025-12-06 09:00:00', manual_entry = 1 WHERE date = '2025-12-06' AND check_in IS NULL");

echo "Updated today's records\n";

// Show result
$stmt = $db->query("SELECT id, user_id, check_in FROM attendance WHERE date='2025-12-06'");
while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "ID {$r['id']}: user={$r['user_id']}, check_in={$r['check_in']}\n";
}
