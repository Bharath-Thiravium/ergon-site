<?php
/**
 * Test Module System
 */

require_once __DIR__ . '/app/helpers/ModuleManager.php';

echo "🧪 Testing Module System\n\n";

// Test basic modules (should always be enabled)
$basicModules = ['attendance', 'leaves', 'advances', 'expenses', 'dashboard'];
echo "📋 Basic Modules (Always Enabled):\n";
foreach ($basicModules as $module) {
    $enabled = ModuleManager::isModuleEnabled($module) ? '✅' : '❌';
    echo "  {$enabled} {$module}\n";
}

echo "\n🔒 Premium Modules (Require Activation):\n";
$premiumModules = ['tasks', 'projects', 'reports', 'users', 'departments', 'notifications', 'finance'];
foreach ($premiumModules as $module) {
    $enabled = ModuleManager::isModuleEnabled($module) ? '✅' : '❌';
    echo "  {$enabled} {$module}\n";
}

echo "\n🎯 Test enabling a premium module:\n";
$result = ModuleManager::enableModule('tasks');
echo "Enable 'tasks' module: " . ($result ? '✅ Success' : '❌ Failed') . "\n";

$enabled = ModuleManager::isModuleEnabled('tasks') ? '✅' : '❌';
echo "Tasks module status: {$enabled}\n";

echo "\n📊 All enabled modules:\n";
$enabledModules = ModuleManager::getEnabledModules();
foreach ($enabledModules as $module) {
    echo "  ✅ {$module}\n";
}

echo "\n✨ Module system test completed!\n";