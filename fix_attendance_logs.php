<?php
require_once __DIR__ . '/app/config/database.php';

$db = Database::connect();

try {
    // Create attendance_logs table
    $sql = "CREATE TABLE IF NOT EXISTS `attendance_logs` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `user_id` int(11) NOT NULL,
      `action` varchar(50) NOT NULL,
      `details` text DEFAULT NULL,
      `created_by` int(11) DEFAULT NULL,
      `created_at` timestamp NULL DEFAULT current_timestamp(),
      PRIMARY KEY (`id`),
      KEY `idx_user_id` (`user_id`),
      KEY `idx_action` (`action`),
      KEY `idx_created_at` (`created_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $db->exec($sql);
    echo "✓ attendance_logs table created successfully\n";
    
    // Create attendance_conflicts table
    $sql2 = "CREATE TABLE IF NOT EXISTS `attendance_conflicts` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `user_id` int(11) NOT NULL,
      `attendance_id` int(11) NOT NULL,
      `conflict_type` varchar(50) NOT NULL,
      `details` text DEFAULT NULL,
      `resolved` tinyint(1) DEFAULT 0,
      `resolved_by` int(11) DEFAULT NULL,
      `resolved_at` datetime DEFAULT NULL,
      `created_at` timestamp NULL DEFAULT current_timestamp(),
      PRIMARY KEY (`id`),
      KEY `idx_user_id` (`user_id`),
      KEY `idx_attendance_id` (`attendance_id`),
      KEY `idx_resolved` (`resolved`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $db->exec($sql2);
    echo "✓ attendance_conflicts table created successfully\n";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
