<?php
echo "=== Adding URL Helper to All Controllers ===\n\n";

$controllerDir = 'app/controllers/';
$controllers = glob($controllerDir . '*.php');

$fixed = 0;
$skipped = 0;

foreach ($controllers as $controller) {
    $filename = basename($controller);
    echo "Processing: $filename\n";
    
    $content = file_get_contents($controller);
    
    // Check if URL helper is already included
    if (strpos($content, 'url_helper.php') !== false) {
        echo "  ⏭️ Already has URL helper\n";
        $skipped++;
        continue;
    }
    
    // Check if it has Controller.php include (most controllers do)
    if (strpos($content, "require_once __DIR__ . '/../core/Controller.php';") !== false) {
        // Add URL helper after Controller.php include
        $content = str_replace(
            "require_once __DIR__ . '/../core/Controller.php';",
            "require_once __DIR__ . '/../core/Controller.php';\nrequire_once __DIR__ . '/../config/url_helper.php';",
            $content
        );
        
        file_put_contents($controller, $content);
        echo "  ✅ Added URL helper\n";
        $fixed++;
    } else {
        echo "  ⚠️ No Controller.php include found - manual check needed\n";
        $skipped++;
    }
}

echo "\n=== Summary ===\n";
echo "Fixed: $fixed controllers\n";
echo "Skipped: $skipped controllers\n";
echo "\nThis should resolve 500 errors caused by missing URL helper functions.\n";
?>