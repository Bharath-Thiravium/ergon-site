<?php
echo "=== Fixing Critical Hardcoded Redirects ===\n\n";

// List of critical files to fix
$criticalFiles = [
    'app/controllers/AuthController.php',
    'app/guards/auth_guard.php',
    'app/middlewares/AuthMiddleware.php',
    'app/middlewares/RoleMiddleware.php',
    'app/middlewares/SessionValidationMiddleware.php',
    'views/layouts/dashboard.php'
];

$replacements = [
    "header('Location: /ergon-site/login')" => "redirectToLogin()",
    'header("Location: /ergon-site/login")' => 'redirectToLogin()',
    "header('Location: /ergon-site/dashboard')" => "redirect('/dashboard')",
    'header("Location: /ergon-site/dashboard")' => 'redirect("/dashboard")',
];

foreach ($criticalFiles as $file) {
    if (file_exists($file)) {
        echo "Processing: $file\n";
        $content = file_get_contents($file);
        $originalContent = $content;
        
        // Add URL helper include at the top if not already present
        if (strpos($content, 'url_helper.php') === false && strpos($content, '<?php') !== false) {
            $content = str_replace('<?php', "<?php\nrequire_once __DIR__ . '/../config/url_helper.php';", $content);
        }
        
        // Apply replacements
        foreach ($replacements as $search => $replace) {
            $content = str_replace($search, $replace, $content);
        }
        
        if ($content !== $originalContent) {
            file_put_contents($file, $content);
            echo "  ✅ Updated\n";
        } else {
            echo "  ⏭️ No changes needed\n";
        }
    } else {
        echo "  ❌ File not found: $file\n";
    }
}

echo "\n=== Summary ===\n";
echo "✅ Created URL helper functions\n";
echo "✅ Updated index.php to use helpers\n";
echo "✅ Updated Controller base class\n";
echo "✅ Fixed critical authentication redirects\n\n";

echo "NEXT STEPS:\n";
echo "1. Test login/logout on subdomain\n";
echo "2. Gradually update remaining controllers\n";
echo "3. Update view files to use url() helper\n";
?>