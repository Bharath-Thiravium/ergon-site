<?php
// Test login route directly
require_once __DIR__ . '/app/config/environment.php';
require_once __DIR__ . '/app/core/Router.php';

// Simulate login request
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/ergon-site/login';
$_SERVER['HTTP_HOST'] = 'bkgreenenergy.com';

echo "Testing /ergon-site/login route...<br>";

$router = new Router();
require_once __DIR__ . '/app/config/routes.php';

echo "Routes loaded. Processing...<br>";
$router->handleRequest();
?>
