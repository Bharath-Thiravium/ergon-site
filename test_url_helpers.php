<?php
echo "=== URL Helper Functions Test ===\n\n";

// Test subdomain
$_SERVER['HTTP_HOST'] = 'aes.athenas.co.in';
$_SERVER['HTTPS'] = 'on';

require_once 'app/config/url_helper.php';

echo "Testing on subdomain: aes.athenas.co.in\n";
echo "Base URL: " . getBaseUrl() . "\n\n";

echo "URL Helper Tests:\n";
echo "url('/login') -> " . url('/login') . "\n";
echo "url('dashboard') -> " . url('dashboard') . "\n";
echo "url('/user/dashboard') -> " . url('/user/dashboard') . "\n";
echo "url('') -> " . url('') . "\n\n";

// Test main domain
$_SERVER['HTTP_HOST'] = 'bkgreenenergy.com';
echo "Testing on main domain: bkgreenenergy.com\n";
echo "Base URL: " . getBaseUrl() . "\n\n";

echo "URL Helper Tests:\n";
echo "url('/login') -> " . url('/login') . "\n";
echo "url('dashboard') -> " . url('dashboard') . "\n";
echo "url('/user/dashboard') -> " . url('/user/dashboard') . "\n";
echo "url('') -> " . url('') . "\n\n";

echo "Expected Results:\n";
echo "✅ All URLs should include the correct domain and /ergon-site path\n";
echo "✅ Subdomain URLs should stay on aes.athenas.co.in\n";
echo "✅ Main domain URLs should stay on bkgreenenergy.com\n";
?>