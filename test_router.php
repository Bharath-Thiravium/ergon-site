<?php
// Test the router directly
require_once __DIR__ . '/app/config/environment.php';
require_once __DIR__ . '/app/core/Router.php';

echo "<h2>Router Test</h2>";

// Simulate the request
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/ergon-site/';
$_SERVER['HTTP_HOST'] = 'bkgreenenergy.com';

echo "Testing route: /ergon-site/<br>";

$router = new Router();

// Load routes
require_once __DIR__ . '/app/config/routes.php';

echo "Routes loaded. Handling request...<br>";

// Handle the request
$router->handleRequest();
?>