<?php
require_once __DIR__ . '/app/config/database.php';
$db = Database::connect();

$entryDateTime = '2025-12-06 17:00:00';
$reason = 'test';
$notes = 'test';
$userId = 16;
$entryDate = '2025-12-06';

echo "Updating with:\n";
echo "check_out = $entryDateTime\n";
echo "clock_out = $entryDateTime\n\n";

$stmt = $db->prepare("UPDATE attendance SET check_out = ?, clock_out = ?, manual_entry = 1, edit_reason = ?, edited_by = ?, updated_at = NOW() WHERE user_id = ? AND date = ?");
$stmt->execute([$entryDateTime, $entryDateTime, "$reason: $notes", 1, $userId, $entryDate]);

echo "Rows affected: " . $stmt->rowCount() . "\n\n";

$stmt = $db->query("SELECT check_out, clock_out FROM attendance WHERE user_id=16 AND date='2025-12-06'");
$r = $stmt->fetch(PDO::FETCH_ASSOC);
echo "After update:\n";
echo "check_out = {$r['check_out']}\n";
echo "clock_out = {$r['clock_out']}\n";
