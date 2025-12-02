<?php
/**
 * Test Company Owner Role Functionality
 */

require_once __DIR__ . '/app/config/database.php';
require_once __DIR__ . '/app/helpers/ModuleManager.php';

// Simulate company_owner session
$_SESSION['role'] = 'company_owner';
ModuleManager::clearCache(); // Clear any existing cache

echo "<h2>Company Owner Module Test</h2>";

// Test enabled modules
$enabledModules = ModuleManager::getEnabledModules();
echo "<h3>Enabled Modules for Company Owner:</h3>";
echo "<ul>";
foreach ($enabledModules as $module) {
    echo "<li>" . ModuleManager::getModuleLabel($module) . " ($module)</li>";
}
echo "</ul>";

// Test individual module checks
echo "<h3>Individual Module Checks:</h3>";
$testModules = ['dashboard', 'finance', 'tasks', 'attendance', 'users'];
foreach ($testModules as $module) {
    $enabled = ModuleManager::isModuleEnabled($module) ? 'YES' : 'NO';
    echo "<p>$module: <strong>$enabled</strong></p>";
}

// Test with regular user role
echo "<hr>";
$_SESSION['role'] = 'user';
ModuleManager::clearCache(); // Clear cache for new role
echo "<h2>Regular User Module Test</h2>";
$enabledModules = ModuleManager::getEnabledModules();
echo "<h3>Enabled Modules for Regular User:</h3>";
echo "<ul>";
foreach ($enabledModules as $module) {
    echo "<li>" . ModuleManager::getModuleLabel($module) . " ($module)</li>";
}
echo "</ul>";
?>
