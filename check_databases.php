<?php
// Check both databases for shipping addresses
try {
    // Check ergon database
    $pdo1 = new PDO("mysql:host=localhost;dbname=ergon", "root", "");
    $stmt1 = $pdo1->query("SELECT COUNT(*) as count FROM finance_customershippingaddress");
    $count1 = $stmt1->fetch()['count'];
    echo "ergon database: $count1 shipping addresses\n";
    
    // Check ergon_db database
    $pdo2 = new PDO("mysql:host=localhost;dbname=ergon_db", "root", "");
    $stmt2 = $pdo2->query("SELECT COUNT(*) as count FROM finance_customershippingaddress");
    $count2 = $stmt2->fetch()['count'];
    echo "ergon_db database: $count2 shipping addresses\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
