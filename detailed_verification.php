<?php
/**
 * Detailed Verification Script - Show exact line numbers and content
 */

echo "🔍 Detailed Verification - Finding exact 'ergon' references...\n\n";

$files = [
    'views/auth/forgot-password.php',
    'views/auth/login.php', 
    'views/finance/dashboard.php',
    'views/layouts/dashboard.php',
    'views/_archive_legacy/dashboard_clean.php',
    'assets/js/optimized-css-loader.js'
];

foreach ($files as $file) {
    if (!file_exists($file)) {
        echo "❌ File not found: $file\n";
        continue;
    }
    
    echo "📄 Checking: $file\n";
    $lines = file($file, FILE_IGNORE_NEW_LINES);
    
    foreach ($lines as $lineNum => $line) {
        // Look for /ergon not followed by -
        if (preg_match('/\/ergon[^-]/', $line)) {
            echo "   Line " . ($lineNum + 1) . ": " . trim($line) . "\n";
        }
    }
    echo "\n";
}
?>