<?php
echo "=== Subdomain Redirect Debug ===\n\n";

// Simulate the subdomain environment
$_SERVER['HTTP_HOST'] = 'aes.athenas.co.in';
$_SERVER['HTTPS'] = 'on';
$_SERVER['SERVER_NAME'] = 'aes.athenas.co.in';

require_once 'app/config/environment.php';

echo "Current Host: " . $_SERVER['HTTP_HOST'] . "\n";
echo "HTTPS: " . $_SERVER['HTTPS'] . "\n";
echo "Base URL: " . Environment::getBaseUrl() . "\n";
echo "Environment: " . Environment::detect() . "\n\n";

// Test the redirect URLs that would be generated
echo "Expected Redirect URLs:\n";
echo "Login: " . Environment::getBaseUrl() . "/login\n";
echo "Dashboard: " . Environment::getBaseUrl() . "/dashboard\n";
echo "Owner Dashboard: " . Environment::getBaseUrl() . "/owner/dashboard\n";
echo "Admin Dashboard: " . Environment::getBaseUrl() . "/admin/dashboard\n";
echo "User Dashboard: " . Environment::getBaseUrl() . "/user/dashboard\n\n";

// Check if the issue is in the AuthController logic
echo "Testing AuthController getRedirectUrl logic:\n";
require_once 'app/config/constants.php';

function testGetRedirectUrl($role) {
    $baseUrl = Environment::getBaseUrl();
    
    switch ($role) {
        case ROLE_OWNER:
        case 'company_owner':
            return $baseUrl . '/owner/dashboard';
        case ROLE_ADMIN:
            return $baseUrl . '/admin/dashboard';
        case ROLE_USER:
            return $baseUrl . '/user/dashboard';
        default:
            return $baseUrl . '/user/dashboard';
    }
}

echo "Owner role redirect: " . testGetRedirectUrl(ROLE_OWNER) . "\n";
echo "Admin role redirect: " . testGetRedirectUrl(ROLE_ADMIN) . "\n";
echo "User role redirect: " . testGetRedirectUrl(ROLE_USER) . "\n";
?>