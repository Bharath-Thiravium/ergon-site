<?php
require_once __DIR__ . '/app/config/database.php';

try {
    $db = Database::connect();
    
    // Fix the new record
    $stmt = $db->prepare("UPDATE attendance SET project_id = 14 WHERE id = 26");
    $stmt->execute();
    echo "✅ Fixed new record ID 26<br>";
    
    // Check if view has hardcoded fallbacks
    $viewContent = file_get_contents(__DIR__ . '/views/attendance/index.php');
    
    if (strpos($viewContent, "($record['check_in'] ? '---' : '---')") !== false) {
        echo "❌ View has hardcoded '---' fallback<br>";
        
        // Fix the view template
        $newContent = str_replace(
            "($record['check_in'] ? '---' : '---')",
            "($record['location_display'] ?? '---')",
            $viewContent
        );
        $newContent = str_replace(
            "($record['check_in'] ? '----' : '----')",
            "($record['project_name'] ?? '----')",
            $newContent
        );
        
        file_put_contents(__DIR__ . '/views/attendance/index.php', $newContent);
        echo "✅ Fixed view template<br>";
    }
    
    echo "<br>✅ All fixes applied!";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>