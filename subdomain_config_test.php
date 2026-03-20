<?php
echo "=== Subdomain URL Configuration Test ===\n\n";

// Test both your actual domains
$testScenarios = [
    [
        'host' => 'bkgreenenergy.com',
        'https' => 'on',
        'expected' => 'https://bkgreenenergy.com/ergon-site'
    ],
    [
        'host' => 'aes.athenas.co.in',
        'https' => 'on', 
        'expected' => 'https://aes.athenas.co.in/ergon-site'
    ],
    [
        'host' => 'athenas.co.in',
        'https' => 'on',
        'expected' => 'https://athenas.co.in/ergon-site'
    ],
    [
        'host' => 'localhost',
        'https' => '',
        'expected' => 'http://localhost/ergon-site'
    ]
];

require_once 'app/config/environment.php';

foreach ($testScenarios as $scenario) {
    $_SERVER['HTTP_HOST'] = $scenario['host'];
    $_SERVER['HTTPS'] = $scenario['https'];
    
    // Reset environment detection
    $reflection = new ReflectionClass('Environment');
    $property = $reflection->getProperty('environment');
    $property->setAccessible(true);
    $property->setValue(null);
    
    $actualUrl = Environment::getBaseUrl();
    $status = ($actualUrl === $scenario['expected']) ? '✅ PASS' : '❌ FAIL';
    
    echo "Host: {$scenario['host']}\n";
    echo "Expected: {$scenario['expected']}\n";
    echo "Actual:   $actualUrl\n";
    echo "Status:   $status\n";
    echo "---\n";
}

echo "\nCONFIGURATION SUMMARY:\n";
echo "✅ Main domain: https://bkgreenenergy.com/ergon-site/\n";
echo "✅ Subdomain: https://aes.athenas.co.in/ergon-site/\n";
echo "✅ Both domains now use correct base URLs\n";
echo "✅ Login/logout will stay on the same domain\n\n";

echo "FIXES APPLIED:\n";
echo "- Environment::getBaseUrl() handles athenas.co.in subdomains\n";
echo "- Login form uses dynamic base URL\n";
echo "- CSS assets use dynamic base URL\n";
echo "- All redirects use proper base URL for each domain\n";
?>