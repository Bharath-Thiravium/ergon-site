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
    
    $departmentId = $_GET['department_id'] ?? null;
    
    if ($departmentId) {
        $stmt = $db->prepare("SELECT id, name, department_id FROM projects WHERE department_id = ? AND status = 'active' ORDER BY name ASC");
        $stmt->execute([$departmentId]);
    } else {
        $stmt = $db->query("SELECT id, name, department_id FROM projects WHERE status = 'active' ORDER BY name ASC");
    }
    
    $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'projects' => $projects]);
    
} catch (Exception $e) {
    error_log('Projects API error: ' . $e->getMessage());
    http_response_code(200);
    echo json_encode(['success' => true, 'projects' => []]);
}
