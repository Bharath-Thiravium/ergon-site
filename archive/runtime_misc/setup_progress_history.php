<?php
/**
 * Setup Task Progress History System
 * Run this script once to initialize the progress tracking system
 */

require_once __DIR__ . '/app/config/database.php';

try {
    $db = Database::connect();
    
    echo "Setting up Task Progress History System...\n";
    
    // Create progress history table
    $db->exec("CREATE TABLE IF NOT EXISTS task_progress_history (
        id INT AUTO_INCREMENT PRIMARY KEY,
        task_id INT NOT NULL,
        user_id INT NOT NULL,
        progress_from INT NOT NULL DEFAULT 0,
        progress_to INT NOT NULL,
        description TEXT,
        status_from VARCHAR(50),
        status_to VARCHAR(50),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_task_id (task_id),
        INDEX idx_user_id (user_id),
        INDEX idx_created_at (created_at)
    )");
    
    echo "✅ Created task_progress_history table\n";
    
    // Add progress_description column to tasks table if not exists
    $stmt = $db->prepare("SHOW COLUMNS FROM tasks LIKE 'progress_description'");
    $stmt->execute();
    if ($stmt->rowCount() == 0) {
        $db->exec("ALTER TABLE tasks ADD COLUMN progress_description TEXT");
        echo "✅ Added progress_description column to tasks table\n";
    } else {
        echo "ℹ️  progress_description column already exists in tasks table\n";
    }
    
    // Check if we need to populate initial history for existing tasks
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM task_progress_history");
    $stmt->execute();
    $historyCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    if ($historyCount == 0) {
        echo "Creating initial progress history for existing tasks...\n";
        
        // Get existing tasks with progress > 0
        $stmt = $db->prepare("SELECT id, title, progress, status, assigned_to, created_at FROM tasks WHERE progress > 0 ORDER BY created_at DESC");
        $stmt->execute();
        $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $insertStmt = $db->prepare("INSERT INTO task_progress_history (task_id, user_id, progress_from, progress_to, description, status_from, status_to, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        
        $count = 0;
        foreach ($tasks as $task) {
            // Create initial progress entry
            $insertStmt->execute([
                $task['id'],
                $task['assigned_to'] ?? 1,
                0,
                $task['progress'],
                'Initial progress entry (migrated from existing data)',
                'assigned',
                $task['status'],
                date('Y-m-d H:i:s', strtotime($task['created_at'] . ' +1 hour'))
            ]);
            $count++;
        }
        
        echo "✅ Created initial progress history entries for {$count} tasks\n";
    } else {
        echo "ℹ️  Progress history already contains {$historyCount} entries\n";
    }
    
    echo "\n🎉 Task Progress History System setup completed successfully!\n";
    echo "\nFeatures enabled:\n";
    echo "- Progress updates now require descriptions\n";
    echo "- Complete progress history tracking\n";
    echo "- Enhanced progress modal with timeline view\n";
    echo "- Progress analytics and reporting\n";
    
} catch (Exception $e) {
    echo "❌ Error setting up progress history system: " . $e->getMessage() . "\n";
    exit(1);
}
?>
