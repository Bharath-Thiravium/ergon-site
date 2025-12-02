<?php
/**
 * Module Access Middleware
 * Checks if user can access requested module
 */

require_once __DIR__ . '/../helpers/ModuleManager.php';

class ModuleMiddleware {
    
    public static function checkModuleAccess($module) {
        if (!ModuleManager::isModuleEnabled($module)) {
            self::showAccessDenied($module);
            exit;
        }
    }
    
    public static function requireModule($module) {
        self::checkModuleAccess($module);
    }
    
    private static function showAccessDenied($module) {
        $moduleLabel = ModuleManager::getModuleLabel($module);
        
        if (self::isAjaxRequest()) {
            header('Content-Type: application/json');
            http_response_code(403);
            echo json_encode([
                'success' => false,
                'error' => "Access denied: {$moduleLabel} is not available in your subscription",
                'module' => $module,
                'upgrade_required' => true
            ]);
        } else {
            http_response_code(403);
            echo "<!DOCTYPE html>
<html>
<head>
    <title>Access Denied</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; padding: 50px; }
        .container { max-width: 500px; margin: 0 auto; }
        .icon { font-size: 64px; margin-bottom: 20px; }
        h1 { color: #e74c3c; }
        .upgrade-btn { background: #3498db; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block; margin-top: 20px; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='icon'>🔒</div>
        <h1>Access Denied</h1>
        <p><strong>{$moduleLabel}</strong> is not available in your current subscription.</p>
        <p>Please contact your administrator to upgrade your subscription.</p>
        <a href='/ergon/dashboard' class='upgrade-btn'>Back to Dashboard</a>
    </div>
</body>
</html>";
        }
    }
    
    private static function isAjaxRequest() {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    }
}