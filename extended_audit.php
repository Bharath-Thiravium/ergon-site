<?php
// Extended audit for remaining directories
$directories = [
    'app/controllers',
    'app/helpers', 
    'app/models',
    'app/config',
    'views',
    'assets/js',
    'assets/css'
];

echo "<h1>Extended Directory Audit</h1>";

foreach ($directories as $dir) {
    if (is_dir($dir)) {
        echo "<h2>📁 $dir</h2>";
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
        $found = false;
        
        foreach ($iterator as $file) {
            if ($file->isFile() && in_array($file->getExtension(), ['php', 'js', 'css'])) {
                $content = file_get_contents($file->getPathname());
                $lines = explode("\n", $content);
                
                foreach ($lines as $num => $line) {
                    if (preg_match('/\/ergon[^-]/', $line)) {
                        $relativePath = str_replace(__DIR__ . '\\', '', $file->getPathname());
                        echo "<p style='color:red;'>❌ $relativePath:" . ($num + 1) . " - " . htmlspecialchars(trim($line)) . "</p>";
                        $found = true;
                    }
                }
            }
        }
        
        if (!$found) {
            echo "<p style='color:green;'>✅ All files clean</p>";
        }
    } else {
        echo "<h2>📁 $dir</h2><p style='color:orange;'>Directory not found</p>";
    }
}

// Check for any JavaScript fetch calls or AJAX references
echo "<h2>🔍 JavaScript/AJAX Check</h2>";
$jsFiles = glob('assets/js/*.js');
foreach ($jsFiles as $jsFile) {
    $content = file_get_contents($jsFile);
    if (preg_match('/fetch\([\'"]\/ergon[^-]/', $content) || preg_match('/ajax.*url.*\/ergon[^-]/', $content)) {
        echo "<p style='color:red;'>❌ $jsFile has AJAX calls to /ergon-site/</p>";
    } else {
        echo "<p style='color:green;'>✅ $jsFile clean</p>";
    }
}
?>
