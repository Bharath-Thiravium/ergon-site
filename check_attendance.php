<?php
require_once __DIR__ . '/app/config/database.php';
$db = Database::connect();
$stmt = $db->query("SELECT id, user_id, date, clock_in, clock_out, status, manual_entry FROM attendance WHERE date = '2025-12-06' ORDER BY id DESC LIMIT 5");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    print_r($row);
    echo "\n";
}
