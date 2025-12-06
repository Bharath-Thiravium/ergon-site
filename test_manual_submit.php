<?php
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'owner';

require_once __DIR__ . '/app/config/database.php';
$db = Database::connect();

// Test data
$userId = 16;
$entryDate = '2025-12-06';
$entryTime = '09:30';
$reason = 'geo_fencing';
$notes = 'Test manual entry';

$entryDateTime = $entryDate . ' ' . $entryTime . ':00';

// Check existing
$stmt = $db->prepare("SELECT * FROM attendance WHERE user_id = ? AND date = ?");
$stmt->execute([$userId, $entryDate]);
$existing = $stmt->fetch(PDO::FETCH_ASSOC);

echo "Existing record:\n";
print_r($existing);

if ($existing) {
    $stmt = $db->prepare("
        UPDATE attendance 
        SET check_in = ?, manual_entry = 1, edit_reason = ?, edited_by = ?, updated_at = NOW()
        WHERE user_id = ? AND date = ?
    ");
    $stmt->execute([$entryDateTime, "$reason: $notes", 1, $userId, $entryDate]);
    echo "\nUpdated existing record\n";
} else {
    $stmt = $db->prepare("
        INSERT INTO attendance (user_id, check_in, date, status, manual_entry, edit_reason, created_at)
        VALUES (?, ?, ?, 'present', 1, ?, NOW())
    ");
    $stmt->execute([$userId, $entryDateTime, $entryDate, "$reason: $notes"]);
    echo "\nInserted new record\n";
}

// Check result
$stmt = $db->prepare("SELECT id, user_id, date, check_in, check_out, status, manual_entry FROM attendance WHERE user_id = ? AND date = ?");
$stmt->execute([$userId, $entryDate]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);

echo "\nResult:\n";
print_r($result);
