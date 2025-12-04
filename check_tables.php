<?php
require_once __DIR__ . '/app/config/database.php';

try {
    $db = Database::connect();
    
    echo "=== ALL FINANCE TABLES ===\n";
    $result = $db->query("SHOW TABLES LIKE 'finance_%'");
    while ($row = $result->fetch(PDO::FETCH_NUM)) {
        echo $row[0] . "\n";
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
?>
