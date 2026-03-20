<?php
// Disable PostgreSQL sync for Hostinger hosting
echo "Disabling PostgreSQL sync for Hostinger...<br>";

// 1. Create disable flag
file_put_contents(__DIR__ . '/.postgresql_disabled', 'Disabled on ' . date('Y-m-d H:i:s'));

// 2. Find and disable sync files
$filesToCheck = [
    'app/helpers/PostgreSQLSync.php',
    'app/cron/postgresql_sync.php',
    'app/config/sync.php',
    'cron/sync.php',
    'sync.php'
];

foreach ($filesToCheck as $file) {
    $fullPath = __DIR__ . '/' . $file;
    if (file_exists($fullPath)) {
        $content = file_get_contents($fullPath);
        
        // Add early return to disable
        $disableCode = "<?php\n// PostgreSQL sync disabled for Hostinger\nif (true) { echo 'PostgreSQL sync disabled'; return; }\n\n";
        $content = str_replace('<?php', $disableCode, $content);
        
        file_put_contents($fullPath, $content);
        echo "✅ Disabled: $file<br>";
    }
}

// 3. Check for database config and disable PostgreSQL references
$dbConfig = __DIR__ . '/app/config/database.php';
if (file_exists($dbConfig)) {
    $content = file_get_contents($dbConfig);
    
    // Comment out PostgreSQL config
    $content = preg_replace('/(\$postgresql.*?;)/s', '// $1 // Disabled for Hostinger', $content);
    $content = str_replace('pgsql:', '// pgsql: // Disabled', $content);
    
    file_put_contents($dbConfig, $content);
    echo "✅ Disabled PostgreSQL in database config<br>";
}

echo "<br>✅ PostgreSQL sync completely disabled for Hostinger hosting<br>";
echo "System will now use MySQL only<br>";
?>