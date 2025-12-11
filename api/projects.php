<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit;
}

require_once __DIR__ . '/../app/config/database.php';

try {
    $db = Database::connect();
    
    // Check if projects table exists, create if not
    $stmt = $db->query("SHOW TABLES LIKE 'projects'");
    if ($stmt->rowCount() == 0) {
        $db->exec("CREATE TABLE IF NOT EXISTS projects (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            description TEXT,
            department_id INT,
            status ENUM('active', 'inactive', 'completed') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_department (department_id),
            INDEX idx_status (status)
        )");
        
        // Insert default projects if none exist
        $db->exec("INSERT INTO projects (name, description, status) VALUES 
            ('General Project', 'Default project for general tasks', 'active'),
            ('Internal Operations', 'Internal company operations and maintenance', 'active'),
            ('Client Work', 'Client-related projects and deliverables', 'active')");
    }
    
    $departmentId = $_GET['department_id'] ?? null;
    
    if ($departmentId) {
        $stmt = $db->prepare("SELECT p.id, p.name, p.description, p.department_id, d.name as department_name FROM projects p LEFT JOIN departments d ON p.department_id = d.id WHERE p.department_id = ? AND p.status = 'active' ORDER BY p.name ASC");
        $stmt->execute([$departmentId]);
    } else {
        $stmt = $db->query("SELECT p.id, p.name, p.description, p.department_id, d.name as department_name FROM projects p LEFT JOIN departments d ON p.department_id = d.id WHERE p.status = 'active' ORDER BY p.name ASC");
    }
    
    $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'projects' => $projects]);
    
} catch (Exception $e) {
    error_log('Projects API error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
