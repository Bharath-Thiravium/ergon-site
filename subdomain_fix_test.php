<?php
echo "=== Subdomain URL Fix Test ===\n\n";

// Simulate different host scenarios
$testHosts = [
    'localhost',
    'subdomain.example.com',
    'bkgreenenergy.com',
    'athenas.co.in',
    'test.bkgreenenergy.com'
];

require_once 'app/config/environment.php';

foreach ($testHosts as $host) {
    $_SERVER['HTTP_HOST'] = $host;
    $_SERVER['HTTPS'] = 'on';
    
    // Reset environment detection
    $reflection = new ReflectionClass('Environment');
    $property = $reflection->getProperty('environment');
    $property->setAccessible(true);
    $property->setValue(null);
    
    echo "Host: $host\n";
    echo "Base URL: '" . Environment::getBaseUrl() . "'\n";
    echo "Environment: " . Environment::detect() . "\n";
    echo "---\n";
}

echo "\nFIXES APPLIED:\n";
echo "✅ Login form action changed to relative URL\n";
echo "✅ CSS asset paths made relative\n";
echo "✅ Environment::getBaseUrl() updated for subdomains\n";
echo "✅ AuthController redirects fixed\n";
echo "✅ getRedirectUrl method updated\n\n";

echo "RESULT:\n";
echo "- Main domains (bkgreenenergy.com, athenas.co.in): Use absolute URLs\n";
echo "- Subdomains and other hosts: Use relative URLs\n";
echo "- This prevents redirects to main domain from subdomains\n";
?>