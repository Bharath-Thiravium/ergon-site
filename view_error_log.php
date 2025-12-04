<?php
echo "<h2>Error Log Viewer</h2>";

// Get PHP error log location
$errorLogFile = ini_get('error_log');
if (!$errorLogFile) {
    $errorLogFile = '/tmp/php_errors.log'; // Default location
}

echo "<h3>PHP Error Log Location: $errorLogFile</h3>";

// Also check common locations
$possibleLogs = [
    $errorLogFile,
    __DIR__ . '/error.log',
    __DIR__ . '/php_errors.log',
    'C:/laragon/logs/php_errors.log',
    'C:/xampp/logs/php_error_log'
];

echo "<h3>Recent Error Logs:</h3>";

foreach ($possibleLogs as $logFile) {
    if (file_exists($logFile)) {
        echo "<h4>Log file: $logFile</h4>";
        $lines = file($logFile);
        $recentLines = array_slice($lines, -20); // Last 20 lines
        
        echo "<pre style='background: #f0f0f0; padding: 10px; max-height: 300px; overflow-y: scroll;'>";
        foreach ($recentLines as $line) {
            if (strpos($line, 'User creation') !== false || strpos($line, 'SQL') !== false || strpos($line, 'PDO') !== false) {
                echo "<strong style='color: red;'>" . htmlspecialchars($line) . "</strong>";
            } else {
                echo htmlspecialchars($line);
            }
        }
        echo "</pre>";
        break;
    }
}

// Also show last error
$lastError = error_get_last();
if ($lastError) {
    echo "<h3>Last PHP Error:</h3>";
    echo "<pre>" . print_r($lastError, true) . "</pre>";
}

echo "<h3>Quick Test:</h3>";
echo "<p><a href='/ergon-site/users/create'>Try creating a user again</a></p>";
echo "<p><a href='?refresh=1'>Refresh this page</a> to see new errors</p>";
?>
