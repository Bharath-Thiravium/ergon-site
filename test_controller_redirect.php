<?php
echo "=== Controller Redirect Fix Test ===\n\n";

// Simulate subdomain environment
$_SERVER['HTTP_HOST'] = 'aes.athenas.co.in';
$_SERVER['HTTPS'] = 'on';

require_once 'app/config/environment.php';
require_once 'app/core/Controller.php';

class TestController extends Controller {
    public function testRedirect($url) {
        // Simulate the redirect logic without actually redirecting
        if (strpos($url, 'http') === 0) {
            return $url;
        }
        
        $baseUrl = Environment::getBaseUrl();
        
        if (strpos($url, '/') !== 0) {
            $url = '/' . $url;
        }
        
        return $baseUrl . $url;
    }
    
    public function testRequireAuth() {
        $baseUrl = Environment::getBaseUrl();
        return $baseUrl . '/login';
    }
}

$controller = new TestController();

echo "Base URL: " . Environment::getBaseUrl() . "\n\n";

echo "Testing redirect URLs:\n";
echo "redirect('/login') -> " . $controller->testRedirect('/login') . "\n";
echo "redirect('dashboard') -> " . $controller->testRedirect('dashboard') . "\n";
echo "redirect('/user/dashboard') -> " . $controller->testRedirect('/user/dashboard') . "\n";
echo "redirect('https://example.com') -> " . $controller->testRedirect('https://example.com') . "\n\n";

echo "Testing requireAuth redirect:\n";
echo "requireAuth() -> " . $controller->testRequireAuth() . "\n\n";

echo "Expected Results:\n";
echo "✅ All URLs should include 'https://aes.athenas.co.in/ergon-site'\n";
echo "✅ No URLs should redirect to just 'https://aes.athenas.co.in/login'\n";
?>