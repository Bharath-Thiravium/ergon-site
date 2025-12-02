<?php
set_time_limit(300);
ini_set('memory_limit', '512M');

function quickScan($dir) {
    $issues = [];
    $excludes = ['vendor', 'node_modules', '.git', 'storage/sessions', 'storage/cache'];
    
    $cmd = "grep -r --include=\"*.php\" --include=\"*.js\" --include=\"*.css\" --exclude-dir=\"" . implode("\" --exclude-dir=\"", $excludes) . "\" \"/ergon[^-]\" \"$dir\" 2>/dev/null";
    $output = shell_exec($cmd);
    
    if ($output) {
        $lines = explode("\n", trim($output));
        foreach ($lines as $line) {
            if (preg_match('/^([^:]+):(\d+):(.+)$/', $line, $matches)) {
                $issues[] = [
                    'file' => str_replace($dir . '/', '', $matches[1]),
                    'line' => $matches[2],
                    'content' => trim($matches[3])
                ];
            }
        }
    }
    
    return $issues;
}

echo "<h1>🔍 Quick Ergon Migration Audit</h1>";

$baseDir = __DIR__;
$issues = quickScan($baseDir);

echo "<h2>📊 Found " . count($issues) . " issues</h2>";

if (empty($issues)) {
    echo "<p style='color:green;'>✅ No /ergon-site/ references found!</p>";
} else {
    echo "<table border='1' style='width:100%; border-collapse:collapse;'>";
    echo "<tr><th>File</th><th>Line</th><th>Current</th><th>Fix</th></tr>";
    
    foreach ($issues as $issue) {
        $fix = str_replace('/ergon-site/', '/ergon-site/', $issue['content']);
        echo "<tr>";
        echo "<td style='font-size:12px;'>" . htmlspecialchars($issue['file']) . "</td>";
        echo "<td>" . $issue['line'] . "</td>";
        echo "<td style='background:#ffe6e6; font-size:11px;'>" . htmlspecialchars($issue['content']) . "</td>";
        echo "<td style='background:#e6ffe6; font-size:11px;'>" . htmlspecialchars($fix) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

// Manual check of critical files
echo "<h2>🔍 Critical Files Check</h2>";
$criticals = ['.htaccess', 'index.php', 'composer.json'];
foreach ($criticals as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        if (strpos($content, '/ergon-site/') !== false) {
            echo "<p style='color:red;'>❌ $file has /ergon-site/ references</p>";
        } else {
            echo "<p style='color:green;'>✅ $file is clean</p>";
        }
    }
}
?>
