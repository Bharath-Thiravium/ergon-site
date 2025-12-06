<?php
require_once __DIR__ . '/app/config/database.php';
$db = Database::connect();
$stmt = $db->query("SELECT id, name, email, status FROM users LIMIT 5");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "{$row['id']}: {$row['name']} ({$row['email']}) - {$row['status']}\n";
}
