<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'owner';

$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST = [
    'user_id' => '37',
    'entry_date' => '2025-12-06',
    'entry_type' => 'clock_in',
    'entry_time' => '10:00',
    'reason' => 'geo_fencing',
    'notes' => 'Test entry'
];

echo "POST data:\n";
print_r($_POST);
echo "\n\n";

ob_start();
include __DIR__ . '/api/manual_attendance.php';
$output = ob_get_clean();

echo "API Response:\n";
echo $output;
echo "\n\n";

require_once __DIR__ . '/app/config/database.php';
$db = Database::connect();
$stmt = $db->query("SELECT id, user_id, date, check_in, manual_entry, edit_reason FROM attendance WHERE user_id=37 AND date='2025-12-06' ORDER BY id DESC LIMIT 1");
echo "Database record:\n";
print_r($stmt->fetch(PDO::FETCH_ASSOC));
