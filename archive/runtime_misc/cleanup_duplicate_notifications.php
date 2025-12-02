<?php
require_once __DIR__ . '/app/config/database.php';

try {
    $db = Database::connect();
    
    echo "🧹 Starting aggressive duplicate notification cleanup...\n\n";
    
    // Get all duplicate notifications grouped by message and receiver
    $stmt = $db->query("
        SELECT message, receiver_id, COUNT(*) as count, MIN(id) as keep_id
        FROM notifications 
        GROUP BY message, receiver_id 
        HAVING count > 1
        ORDER BY count DESC
    ");
    $duplicateGroups = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $totalRemoved = 0;
    
    foreach ($duplicateGroups as $group) {
        echo "🔄 Processing: '{$group['message']}' ({$group['count']} duplicates)\n";
        
        // Delete all except the oldest one (keep_id)
        $stmt = $db->prepare("
            DELETE FROM notifications 
            WHERE message = ? 
            AND receiver_id = ? 
            AND id != ?
        ");
        $stmt->execute([$group['message'], $group['receiver_id'], $group['keep_id']]);
        
        $removed = $stmt->rowCount();
        $totalRemoved += $removed;
        echo "   ✅ Removed {$removed} duplicates, kept 1\n";
    }
    
    echo "\n📊 Summary:\n";
    echo "   • Processed " . count($duplicateGroups) . " duplicate groups\n";
    echo "   • Removed {$totalRemoved} duplicate notifications\n";
    
    // Final count
    $stmt = $db->query("SELECT COUNT(*) as total FROM notifications");
    $total = $stmt->fetchColumn();
    echo "   • Total notifications remaining: {$total}\n";
    
    echo "\n✅ Duplicate cleanup completed!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
