<?php
/**
 * Setup Module System
 * Run this once to create the enabled_modules table
 */

require_once __DIR__ . '/app/config/database.php';

try {
    $db = Database::connect();
    
    // Create enabled_modules table
    $sql = "CREATE TABLE IF NOT EXISTS enabled_modules (
        id INT AUTO_INCREMENT PRIMARY KEY,
        module_name VARCHAR(50) NOT NULL UNIQUE,
        status ENUM('active', 'inactive') DEFAULT 'active',
        enabled_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        disabled_at TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_module_status (module_name, status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $db->exec($sql);
    echo "✅ Module system setup completed successfully!\n";
    echo "📋 Basic modules (always enabled): attendance, leaves, advances, expenses, dashboard\n";
    echo "🔒 Premium modules (require activation): tasks, projects, reports, users, departments, notifications, finance, followups, gamification, analytics, system_admin\n";
    echo "\n🎯 Access /ergon-site/modules as owner to manage module access\n";
    
} catch (Exception $e) {
    echo "❌ Setup failed: " . $e->getMessage() . "\n";
}
