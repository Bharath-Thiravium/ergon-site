<?php
/**
 * Accurate Verification Script - Only flag actual directory path issues
 */

echo "🔍 Accurate Verification - Checking for problematic 'ergon' directory references...\n\n";

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
            
            // Only check for problematic patterns - exclude ergon.css references
            $patterns = [
                '/\/ergon\//',           // /ergon/ (directory path)
                '/href="\/ergon\//',     // href="/ergon/ (directory path)  
                '/src="\/ergon\//',      // src="/ergon/ (directory path)
                '/action="\/ergon\//',   // action="/ergon/ (directory path)
                '/url\(\/ergon\//',      // url(/ergon/ (directory path)
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
    echo "🎉 Migration complete! No problematic 'ergon' directory references found.\n";
    echo "ℹ️  Note: References to 'ergon.css' filename are correct and expected.\n";
} else {
    echo "❌ ISSUES FOUND: $totalIssues problematic directory references need to be fixed\n";
    echo "🔧 Please review and fix the issues listed above.\n";
}

echo "\n🏁 Verification complete.\n";
?>