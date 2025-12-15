<?php
require_once __DIR__ . '/app/config/database.php';

// Fix the new record first
$db = Database::connect();
$stmt = $db->prepare("UPDATE attendance SET project_id = 14 WHERE id = 26");
$stmt->execute();

// Fix the view file
$viewFile = __DIR__ . '/views/attendance/index.php';
$content = file_get_contents($viewFile);

$content = str_replace(
    "(\$record['check_in'] ? '---' : '---')",
    "'No Location'",
    $content
);

$content = str_replace(
    "(\$record['check_in'] ? '----' : '----')",
    "'No Project'",
    $content
);

file_put_contents($viewFile, $content);

echo "✅ Fixed record 26 and view template";
?>