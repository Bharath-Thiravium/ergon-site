<?php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../services/LocationService.php';

class ApiController extends Controller {
    
    public function userProjects() {
        $this->requireAuth();
        
        header('Content-Type: application/json');
        
        try {
            require_once __DIR__ . '/../config/database.php';
            $projects = LocationService::getUserProjects($_SESSION['user_id']);
            echo json_encode(['success' => true, 'projects' => $projects]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }
    
    public function serviceHistory() {
        $this->requireAuth();
        
        header('Content-Type: application/json');
        
        try {
            require_once __DIR__ . '/../config/database.php';
            $db = Database::connect();
            $userId = $_SESSION['user_id'];
            $startDate = $_GET['start_date'] ?? date('Y-m-01');
            $endDate = $_GET['end_date'] ?? date('Y-m-d');
            
            $stmt = $db->prepare("
                SELECT sh.*, p.name as project_name, p.latitude as project_lat, p.longitude as project_lng
                FROM service_history sh
                JOIN projects p ON sh.project_id = p.id
                WHERE sh.user_id = ? AND sh.service_date BETWEEN ? AND ?
                ORDER BY sh.service_date DESC, sh.start_time DESC
            ");
            $stmt->execute([$userId, $startDate, $endDate]);
            $history = $stmt->fetchAll();
            
            echo json_encode(['success' => true, 'history' => $history]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }
    
    public function departments() {
        $this->requireAuth();
        
        header('Content-Type: application/json');
        
        try {
            require_once __DIR__ . '/../config/database.php';
            $db = Database::connect();
            
            $stmt = $db->prepare("SELECT id, name FROM departments ORDER BY name");
            $stmt->execute();
            $departments = $stmt->fetchAll();
            
            echo json_encode(['success' => true, 'departments' => $departments]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }
    
    public function projects() {
        $this->requireAuth();
        
        header('Content-Type: application/json');
        
        try {
            require_once __DIR__ . '/../config/database.php';
            $db = Database::connect();
            
            $stmt = $db->prepare("SELECT p.id, p.name as project_name, p.description, p.department_id, d.name as department_name FROM projects p LEFT JOIN departments d ON p.department_id = d.id WHERE p.status = 'active' ORDER BY p.name");
            $stmt->execute();
            $projects = $stmt->fetchAll();
            
            echo json_encode(['success' => true, 'projects' => $projects]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }
    
    public function getExpense() {
        $this->requireAuth();
        header('Content-Type: application/json');
        
        $id = $_GET['id'] ?? null;
        if (!$id) {
            echo json_encode(['success' => false, 'error' => 'ID required']);
            exit;
        }
        
        try {
            require_once __DIR__ . '/../config/database.php';
            $db = Database::connect();
            $stmt = $db->prepare("SELECT * FROM expenses WHERE id = ? AND user_id = ?");
            $stmt->execute([$id, $_SESSION['user_id']]);
            $expense = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$expense) {
                echo json_encode(['success' => false, 'error' => 'Expense not found']);
                exit;
            }
            
            echo json_encode(['success' => true, 'expense' => $expense]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => 'Server error']);
        }
        exit;
    }
    
    public function getAdvance() {
        $this->requireAuth();
        header('Content-Type: application/json');
        
        $id = $_GET['id'] ?? null;
        if (!$id) {
            echo json_encode(['success' => false, 'error' => 'ID required']);
            exit;
        }
        
        try {
            require_once __DIR__ . '/../config/database.php';
            $db = Database::connect();
            $stmt = $db->prepare("SELECT * FROM advances WHERE id = ? AND user_id = ?");
            $stmt->execute([$id, $_SESSION['user_id']]);
            $advance = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$advance) {
                echo json_encode(['success' => false, 'error' => 'Advance not found']);
                exit;
            }
            
            echo json_encode(['success' => true, 'advance' => $advance]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => 'Server error']);
        }
        exit;
    }
    
    public function getUser($userId = null) {
        $this->requireAuth();
        
        header('Content-Type: application/json');
        
        try {
            // Debug logging
            error_log('getUser called with userId: ' . var_export($userId, true));
            error_log('REQUEST_URI: ' . $_SERVER['REQUEST_URI']);
            
            // Fallback: extract user ID from URL if not passed as parameter
            if (!$userId) {
                $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
                $path = str_replace('/ergon-site', '', $path);
                if (preg_match('/\/api\/users\/(\d+)/', $path, $matches)) {
                    $userId = $matches[1];
                    error_log('Extracted userId from URL: ' . $userId);
                }
            }
            
            require_once __DIR__ . '/../config/database.php';
            $db = Database::connect();
            
            if (!$userId) {
                echo json_encode(['success' => false, 'error' => 'User ID required', 'debug' => ['userId' => $userId, 'uri' => $_SERVER['REQUEST_URI']]]);
                exit;
            }
            
            $stmt = $db->prepare("SELECT id, name, email, role, department_id, phone, date_of_birth, gender, designation, joining_date, salary, address, emergency_contact, status FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();
            
            if (!$user) {
                echo json_encode(['success' => false, 'error' => 'User not found']);
                exit;
            }
            
            // Get user's assigned projects
            $stmt = $db->prepare("SELECT project_id FROM user_projects WHERE user_id = ? AND status = 'active'");
            $stmt->execute([$userId]);
            $userProjects = $stmt->fetchAll();
            
            echo json_encode(['success' => true, 'user' => $user, 'user_projects' => $userProjects]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }
    
    public function contactPersons() {
        $this->requireAuth();
        
        header('Content-Type: application/json');
        
        try {
            require_once __DIR__ . '/../config/database.php';
            $db = Database::connect();
            
            // Ensure contacts table exists
            $db->exec("CREATE TABLE IF NOT EXISTS contacts (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                phone VARCHAR(50),
                email VARCHAR(255),
                company VARCHAR(255),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )");
            
            $stmt = $db->prepare("SELECT id, name, phone, email, company FROM contacts ORDER BY name");
            $stmt->execute();
            $contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode(['success' => true, 'contacts' => $contacts]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }
    
    public function users() {
        $this->requireAuth();
        
        header('Content-Type: application/json');
        
        try {
            require_once __DIR__ . '/../config/database.php';
            $db = Database::connect();
            
            $stmt = $db->prepare("SELECT id, name, email FROM users WHERE status = 'active' ORDER BY name");
            $stmt->execute();
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode(['success' => true, 'users' => $users]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }
    
    private function requireAuth() {
        if (!isset($_SESSION['user_id'])) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Unauthorized']);
            exit;
        }
    }

}
?>
