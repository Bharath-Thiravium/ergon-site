<?php
session_start();
require_once __DIR__ . '/app/config/database.php';

$db = Database::connect();
$userId = $_SESSION['user_id'];
$today = date('Y-m-d');

// Get attendance record
$stmt = $db->prepare("SELECT * FROM attendance WHERE user_id = ? AND DATE(check_in) = ?");
$stmt->execute([$userId, $today]);
$attendance = $stmt->fetch(PDO::FETCH_ASSOC);

echo "<h2>Final Test Results</h2>";
echo "<h3>Raw Database Data:</h3>";
echo "project_id: " . ($attendance['project_id'] ?: 'NULL') . "<br>";

if ($attendance['project_id']) {
    $stmt = $db->prepare("SELECT * FROM projects WHERE id = ?");
    $stmt->execute([$attendance['project_id']]);
    $project = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<h3>Project Data (ID: {$attendance['project_id']}):</h3>";
    echo "Name: {$project['name']}<br>";
    echo "Place: {$project['place']}<br>";
    
    echo "<h3>Expected Display:</h3>";
    echo "Location: <strong>{$project['place']}</strong><br>";
    echo "Project: <strong>{$project['name']}</strong><br>";
} else {
    echo "❌ No project_id found in attendance record<br>";
}

// Force update the attendance record
if ($attendance && !$attendance['project_id']) {
    echo "<h3>Fixing project_id...</h3>";
    $stmt = $db->prepare("UPDATE attendance SET project_id = 22 WHERE id = ?");
    $stmt->execute([$attendance['id']]);
    echo "✅ Updated project_id to 22<br>";
}

echo "<br><a href='/ergon-site/attendance'>Go to Attendance Page</a>";
?>