<?php
// Disable PostgreSQL sync by creating a flag file
file_put_contents(__DIR__ . '/app/config/disable_postgresql.flag', 'PostgreSQL sync disabled - ' . date('Y-m-d H:i:s'));

// Check for sync files and disable them
$syncFiles = [
    __DIR__ . '/app/helpers/PostgreSQLSync.php',
    __DIR__ . '/app/cron/postgresql_sync.php'
];

foreach ($syncFiles as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        $content = "<?php\n// PostgreSQL sync disabled\nreturn;\n" . $content;
        file_put_contents($file, $content);
        echo "✅ Disabled: " . basename($file) . "<br>";
    }
}

echo "✅ PostgreSQL sync has been disabled<br>";
echo "The system will now use MySQL only<br>";
?>