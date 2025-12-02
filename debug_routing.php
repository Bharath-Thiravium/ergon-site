<?php
// Debug script for routing issues
// Place this in /public_html/ergon-site/debug_routing.php

echo "<h2>Routing Debug Information</h2>";

echo "<h3>Server Variables:</h3>";
echo "HTTP_HOST: " . ($_SERVER['HTTP_HOST'] ?? 'Not set') . "<br>";
echo "REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? 'Not set') . "<br>";
echo "REQUEST_METHOD: " . ($_SERVER['REQUEST_METHOD'] ?? 'Not set') . "<br>";
echo "SCRIPT_NAME: " . ($_SERVER['SCRIPT_NAME'] ?? 'Not set') . "<br>";
echo "DOCUMENT_ROOT: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'Not set') . "<br>";
echo "PHP_SELF: " . ($_SERVER['PHP_SELF'] ?? 'Not set') . "<br>";

echo "<h3>Path Analysis:</h3>";
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
echo "Parsed Path: " . $path . "<br>";

$basePath = '/ergon-site';
if (strpos($path, $basePath) === 0) {
    $cleanPath = substr($path, strlen($basePath));
    echo "After removing /ergon-site: " . $cleanPath . "<br>";
} else {
    echo "Path does not start with /ergon-site<br>";
}

echo "<h3>File Checks:</h3>";
echo "Current directory: " . __DIR__ . "<br>";
echo "Index.php exists: " . (file_exists(__DIR__ . '/index.php') ? 'Yes' : 'No') . "<br>";
echo ".htaccess exists: " . (file_exists(__DIR__ . '/.htaccess') ? 'Yes' : 'No') . "<br>";
echo "Router.php exists: " . (file_exists(__DIR__ . '/app/core/Router.php') ? 'Yes' : 'No') . "<br>";

echo "<h3>Environment Detection:</h3>";
if (file_exists(__DIR__ . '/app/config/environment.php')) {
    require_once __DIR__ . '/app/config/environment.php';
    echo "Environment: " . Environment::detect() . "<br>";
    echo "Base URL: " . Environment::getBaseUrl() . "<br>";
} else {
    echo "Environment.php not found<br>";
}

echo "<h3>Test Links:</h3>";
echo "<a href='/ergon-site/'>Root</a><br>";
echo "<a href='/ergon-site/login'>Login</a><br>";
echo "<a href='/ergon-site/dashboard'>Dashboard</a><br>";
?>