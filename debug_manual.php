<?php
session_start();
require_once __DIR__ . '/app/config/database.php';

$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'owner';

$db = Database::connect();

// Simulate POST data
$_POST = [
    'user_id' => 2,
    'entry_date' => '2025-01-20',
    'entry_type' => 'clock_in',
    'entry_time' => '09:00',
    'reason' => 'geo_fencing',
    'notes' => 'Test entry'
];

$_SERVER['REQUEST_METHOD'] = 'POST';

ob_start();
include __DIR__ . '/api/manual_attendance.php';
$response = ob_get_clean();

echo "Response:\n";
echo $response . "\n\n";

// Check attendance table
$stmt = $db->query("SELECT * FROM attendance ORDER BY id DESC LIMIT 1");
$att = $stmt->fetch(PDO::FETCH_ASSOC);
echo "Last attendance record:\n";
print_r($att);

// Check logs
$stmt = $db->query("SELECT * FROM attendance_logs ORDER BY id DESC LIMIT 1");
$log = $stmt->fetch(PDO::FETCH_ASSOC);
echo "\nLast log record:\n";
print_r($log);
