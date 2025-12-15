<?php
// Final test of the query
require_once __DIR__ . '/app/config/database.php';
$db = Database::connect();

$stmt = $db->prepare("SELECT u.name, COALESCE(p.place, 'Default Location') as location_display, COALESCE(p.name, 'Default Project') as project_name FROM users u LEFT JOIN attendance a ON u.id = a.user_id AND DATE(a.check_in) = '2025-12-15' LEFT JOIN projects p ON a.project_id = p.id WHERE u.name = 'Nelson Raj'");
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);

echo "Final test - Nelson should show: Location='{$result['location_display']}' Project='{$result['project_name']}'<br><br>";

// Clean up debug files
$files = ['prod_debug.php', 'production_fix.php', 'fix_nelson.php', 'check_controller.php', 'test_query.php', 'clear_cache.php', 'check_view.php', 'fix_latest_records.php', 'force_refresh.php'];

foreach ($files as $file) {
    if (file_exists($file)) {
        unlink($file);
        echo "✅ Deleted $file<br>";
    }
}

echo "<br>✅ Cleanup complete! Nelson should now show proper location and project data.";
?>