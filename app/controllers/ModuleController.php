<?php
/**
 * Module Management Controller
 * For Master Admin to manage module access
 */

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../middlewares/AuthMiddleware.php';
require_once __DIR__ . '/../helpers/ModuleManager.php';

class ModuleController extends Controller {
    
    public function index() {
        AuthMiddleware::requireAuth();
        
        // Only owner can access module management
        if ($_SESSION['role'] !== 'owner') {
            http_response_code(403);
            echo "Access denied. Only master admin can manage modules.";
            return;
        }
        
        $config = require __DIR__ . '/../config/modules.php';
        $enabledModules = ModuleManager::getEnabledModules();
        
        $modules = [];
        foreach (ModuleManager::getAllModules() as $module) {
            $modules[] = [
                'name' => $module,
                'label' => ModuleManager::getModuleLabel($module),
                'enabled' => in_array($module, $enabledModules),
                'is_basic' => in_array($module, $config['basic_modules'])
            ];
        }
        
        $this->view('admin/modules', [
            'modules' => $modules,
            'active_page' => 'modules'
        ]);
    }
    
    public function toggle() {
        AuthMiddleware::requireAuth();
        
        if ($_SESSION['role'] !== 'owner') {
            $this->jsonResponse(['success' => false, 'error' => 'Access denied']);
            return;
        }
        
        $module = $_POST['module'] ?? '';
        $action = $_POST['action'] ?? '';
        
        if (empty($module) || !in_array($action, ['enable', 'disable'])) {
            $this->jsonResponse(['success' => false, 'error' => 'Invalid parameters']);
            return;
        }
        
        $result = $action === 'enable' 
            ? ModuleManager::enableModule($module)
            : ModuleManager::disableModule($module);
        
        if ($result) {
            $this->jsonResponse([
                'success' => true, 
                'message' => ucfirst($action) . 'd module successfully'
            ]);
        } else {
            $this->jsonResponse([
                'success' => false, 
                'error' => 'Failed to ' . $action . ' module'
            ]);
        }
    }
}