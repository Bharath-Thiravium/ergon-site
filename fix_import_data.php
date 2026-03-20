<?php
require_once __DIR__ . '/app/config/database.php';

try {
    $db = Database::connect();
    
    // 1. Add missing employee "S. johnkennady"
    $stmt = $db->prepare("INSERT INTO users (employee_id, name, email, password, role, phone, designation, joining_date, salary, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        'EMP021',
        'S. johnkennady', 
        'johnkennady@athenas.co.in',
        password_hash('temp123', PASSWORD_DEFAULT),
        'user',
        '9876543210',
        'Site Engineer',
        '2025-12-15',
        25000.00,
        'active'
    ]);
    echo "✅ Added employee S. johnkennady<br>";
    
    // 2. Add missing project "Enrich Navia2"
    $stmt = $db->prepare("INSERT INTO projects (name, description, budget, status, place) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([
        'Enrich Navia2',
        'Residential construction project',
        5000000.00,
        'active',
        'Chennai'
    ]);
    echo "✅ Added project Enrich Navia2<br>";
    
    echo "<br>✅ Missing data added. Re-run the import process.";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>