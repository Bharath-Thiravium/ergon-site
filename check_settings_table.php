<?php
require_once __DIR__ . '/app/config/database.php';

try {
    $db = Database::connect();
    
    echo "=== SETTINGS TABLE STRUCTURE ===\n";
    $result = $db->query("DESCRIBE settings");
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        echo "{$row['Field']} - {$row['Type']} - {$row['Null']} - {$row['Default']}\n";
    }
    
    echo "\n=== CURRENT SETTINGS DATA ===\n";
    $result = $db->query("SELECT * FROM settings LIMIT 1");
    $settings = $result->fetch(PDO::FETCH_ASSOC);
    if ($settings) {
        foreach ($settings as $key => $value) {
            echo "$key: $value\n";
        }
    } else {
        echo "No settings found\n";
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
?>