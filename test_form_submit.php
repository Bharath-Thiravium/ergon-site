<?php
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'owner';

$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST = [
    'user_id' => '16',
    'entry_date' => date('Y-m-d'),
    'entry_type' => 'clock_in',
    'entry_time' => '09:30',
    'reason' => 'geo_fencing',
    'notes' => 'Test'
];

echo "Submitting with date: " . $_POST['entry_date'] . "\n\n";

ob_start();
include __DIR__ . '/api/manual_attendance.php';
$response = ob_get_clean();

echo $response;
