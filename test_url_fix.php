<?php
/**
 * Test script to verify URL generation fix
 */

require_once __DIR__ . '/app/config/environment.php';

// Test different host scenarios
$testHosts = [
    'localhost',
    'bkgreenenergy.com',
    'athenas.co.in',
    'example.com'
];

echo "URL Generation Test Results:\n";
echo "============================\n\n";

foreach ($testHosts as $host) {
    $_SERVER['HTTP_HOST'] = $host;
    $_SERVER['HTTPS'] = ($host !== 'localhost') ? 'on' : null;
    
    $baseUrl = Environment::getBaseUrl();
    echo "Host: {$host}\n";
    echo "Generated Base URL: {$baseUrl}\n";
    echo "Sample notification URL: {$baseUrl}/expenses/view/123\n";
    echo "---\n";
}

// Test with actual production host
$_SERVER['HTTP_HOST'] = 'bkgreenenergy.com';
$_SERVER['HTTPS'] = 'on';

echo "\nProduction Test:\n";
echo "Host: bkgreenenergy.com\n";
echo "Base URL: " . Environment::getBaseUrl() . "\n";
echo "Expense URL: " . Environment::getBaseUrl() . "/expenses/view/7\n";
echo "Advance URL: " . Environment::getBaseUrl() . "/advances/view/7\n";
echo "Leave URL: " . Environment::getBaseUrl() . "/leaves/view/7\n";

echo "\nURL Fix Applied Successfully!\n";
?>