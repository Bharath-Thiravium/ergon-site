<?php
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'owner';

require_once __DIR__ . '/app/config/database.php';
$db = Database::connect();

// Submit manual entry
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST = [
    'user_id' => '16',
    'entry_date' => '2025-12-06',
    'entry_type' => 'clock_in',
    'entry_time' => '09:00',
    'reason' => 'geo_fencing',
    'notes' => 'Test'
];

ob_start();
include __DIR__ . '/api/manual_attendance.php';
$response = ob_get_clean();
echo "API Response: $response\n\n";

// Check what was created
$stmt = $db->query("SELECT id, user_id, check_in, clock_in, date, manual_entry FROM attendance WHERE user_id=16 AND date='2025-12-06'");
echo "Records in DB:\n";
while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "ID {$r['id']}: check_in={$r['check_in']}, clock_in={$r['clock_in']}, manual={$r['manual_entry']}\n";
}

// Test the display query
echo "\nDisplay Query Result:\n";
$stmt = $db->prepare("SELECT u.name, a.check_in FROM users u LEFT JOIN attendance a ON u.id = a.user_id AND (DATE(a.check_in) = ? OR a.date = ?) WHERE u.id=16");
$stmt->execute(['2025-12-06', '2025-12-06']);
$r = $stmt->fetch(PDO::FETCH_ASSOC);
echo "{$r['name']}: check_in={$r['check_in']}\n";
