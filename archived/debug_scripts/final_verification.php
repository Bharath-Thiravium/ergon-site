<?php
/**
 * Final Verification Script - Check for remaining "ergon" references
 * Run this to confirm all directory migrations are complete
 */

echo "🔍 Final Verification - Checking for remaining 'ergon' references...\n\n";

$directories = [
    'app/helpers',
    'views/auth',
    'views/finance',
    'views/layouts',
    'views/_archive_legacy',
    'assets/js'
];

$totalIssues = 0;

foreach ($directories as $dir) {
    echo "📁 Checking $dir\n";
    
    if (!is_dir($dir)) {
        echo "   ⚠️ Directory not found: $dir\n";
        continue;
    }
    
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS)
    );
    
    $dirIssues = 0;
    
    foreach ($iterator as $file) {
        if ($file->isFile() && in_array($file->getExtension(), ['php', 'js', 'css', 'html'])) {
            $content = file_get_contents($file->getPathname());
            
            // Check for problematic patterns
            $patterns = [
                '/\/ergon[^-]/',  // /ergon not followed by -
                '/href="\/ergon[^-]/',  // href="/ergon not followed by -
                '/src="\/ergon[^-]/',   // src="/ergon not followed by -
                '/url\(\/ergon[^-]/',   // url(/ergon not followed by -
            ];
            
            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $content, $matches)) {
                    echo "   ❌ " . $file->getFilename() . " - Found: " . trim($matches[0]) . "\n";
                    $dirIssues++;
                    $totalIssues++;
                }
            }
        }
    }
    
    if ($dirIssues === 0) {
        echo "   ✅ All files clean\n";
    }
    
    echo "\n";
}

echo "📊 SUMMARY\n";
echo "==========\n";
if ($totalIssues === 0) {
    echo "✅ SUCCESS: All directory references have been updated to 'ergon-site'\n";
    echo "🎉 Migration complete! No remaining 'ergon' references found.\n";
} else {
    echo "❌ ISSUES FOUND: $totalIssues remaining references need to be fixed\n";
    echo "🔧 Please review and fix the issues listed above.\n";
}

echo "\n🏁 Verification complete.\n";
?>