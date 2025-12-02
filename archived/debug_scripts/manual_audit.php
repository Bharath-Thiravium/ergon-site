<?php
// Simple manual audit for Windows
$files = [
    'views/users/create.php',
    'views/users/edit.php', 
    'views/admin/modules.php',
    'index.php',
    '.htaccess'
];

echo "<h1>Manual Audit Results</h1>";

foreach ($files as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        $lines = explode("\n", $content);
        
        echo "<h3>$file</h3>";
        $found = false;
        
        foreach ($lines as $num => $line) {
            if (preg_match('/\/ergon[^-]/', $line)) {
                echo "<p style='color:red;'>Line " . ($num + 1) . ": " . htmlspecialchars(trim($line)) . "</p>";
                $found = true;
            }
        }
        
        if (!$found) {
            echo "<p style='color:green;'>✅ Clean</p>";
        }
    } else {
        echo "<h3>$file</h3><p style='color:orange;'>File not found</p>";
    }
}
?>
