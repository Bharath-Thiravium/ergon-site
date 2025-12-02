<?php
/**
 * Comprehensive Audit Script for "ergon" to "ergon-site" Migration
 */

function scanDirectory($dir, $pattern = '/ergon(?!-site)/', $extensions = ['php', 'js', 'css', 'html', 'json', 'md']) {
    $results = [];
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    
    foreach ($iterator as $file) {
        if ($file->isFile()) {
            $extension = pathinfo($file->getFilename(), PATHINFO_EXTENSION);
            if (in_array($extension, $extensions)) {
                $content = file_get_contents($file->getPathname());
                $lines = explode("\n", $content);
                
                foreach ($lines as $lineNum => $line) {
                    if (preg_match($pattern, $line, $matches)) {
                        $results[] = [
                            'file' => str_replace('\\', '/', $file->getPathname()),
                            'line' => $lineNum + 1,
                            'content' => trim($line),
                            'match' => $matches[0],
                            'type' => detectType($line)
                        ];
                    }
                }
            }
        }
    }
    
    return $results;
}

function detectType($line) {
    if (preg_match('/href=|action=|src=|url\(/', $line)) return 'ROUTE/URL';
    if (preg_match('/require|include|autoload/', $line)) return 'PATH';
    if (preg_match('/namespace|use |class /', $line)) return 'NAMESPACE';
    if (preg_match('/fetch\(|ajax|XMLHttpRequest/', $line)) return 'AJAX';
    if (preg_match('/\/\*|\*\/|\/\//', $line)) return 'COMMENT';
    if (preg_match('/config|setting|constant/', $line)) return 'CONFIG';
    return 'OTHER';
}

echo "<h1>🔍 Ergon to Ergon-Site Migration Audit</h1>";

$baseDir = __DIR__;
$excludeDirs = ['vendor', 'node_modules', '.git', 'storage/sessions'];

// Scan for "ergon" references (excluding "ergon-site")
$issues = scanDirectory($baseDir, '/\/ergon(?!-site)\/|ergon(?!-site)(?=\/|\\\\|\.|\s|$)/');

// Filter out excluded directories
$filteredIssues = array_filter($issues, function($issue) use ($excludeDirs) {
    foreach ($excludeDirs as $exclude) {
        if (strpos($issue['file'], $exclude) !== false) {
            return false;
        }
    }
    return true;
});

echo "<h2>📊 Summary</h2>";
echo "<p>Total issues found: " . count($filteredIssues) . "</p>";

// Group by type
$byType = [];
foreach ($filteredIssues as $issue) {
    $byType[$issue['type']][] = $issue;
}

echo "<h2>📋 Issues by Type</h2>";
foreach ($byType as $type => $issues) {
    echo "<h3>$type (" . count($issues) . " issues)</h3>";
    echo "<table border='1' style='width:100%; border-collapse:collapse;'>";
    echo "<tr><th>File</th><th>Line</th><th>Content</th><th>Recommended Fix</th></tr>";
    
    foreach ($issues as $issue) {
        $file = str_replace($baseDir, '', $issue['file']);
        $recommendedFix = str_replace('/ergon-site/', '/ergon-site/', $issue['content']);
        $recommendedFix = str_replace('ergon/', 'ergon-site/', $recommendedFix);
        
        echo "<tr>";
        echo "<td style='font-size:12px;'>" . htmlspecialchars($file) . "</td>";
        echo "<td>" . $issue['line'] . "</td>";
        echo "<td style='font-size:12px; background:#ffe6e6;'>" . htmlspecialchars($issue['content']) . "</td>";
        echo "<td style='font-size:12px; background:#e6ffe6;'>" . htmlspecialchars($recommendedFix) . "</td>";
        echo "</tr>";
    }
    echo "</table><br>";
}

// Critical files check
echo "<h2>🚨 Critical Files Analysis</h2>";
$criticalFiles = [
    'index.php',
    '.htaccess',
    'app/config/routes.php',
    'app/config/constants.php',
    'composer.json',
    'package.json'
];

foreach ($criticalFiles as $criticalFile) {
    $fullPath = $baseDir . '/' . $criticalFile;
    if (file_exists($fullPath)) {
        $content = file_get_contents($fullPath);
        if (preg_match('/\/ergon(?!-site)\/|ergon(?!-site)(?=\/|\\\\|\.|\s|$)/', $content)) {
            echo "<p style='color:red;'>⚠️ CRITICAL: $criticalFile contains old references</p>";
        } else {
            echo "<p style='color:green;'>✅ OK: $criticalFile</p>";
        }
    } else {
        echo "<p style='color:orange;'>❓ NOT FOUND: $criticalFile</p>";
    }
}

echo "<h2>🔧 Quick Fix Script</h2>";
echo "<p>Run this to fix most issues:</p>";
echo "<pre>";
echo "find . -name '*.php' -not -path './vendor/*' -exec sed -i 's|/ergon-site/|/ergon-site/|g' {} +\n";
echo "find . -name '*.js' -not -path './vendor/*' -exec sed -i 's|/ergon-site/|/ergon-site/|g' {} +\n";
echo "find . -name '*.css' -not -path './vendor/*' -exec sed -i 's|/ergon-site/|/ergon-site/|g' {} +\n";
echo "</pre>";

?>
