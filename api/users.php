<?php
require_once __DIR__ . '/../app/config/session.php';
require_once __DIR__ . '/../app/config/database.php';

// Check authentication
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');

try {
    $db = Database::connect();
    
    $stmt = $db->prepare("SELECT id, name, email, role FROM users WHERE status = 'active' AND role != 'owner' ORDER BY name ASC");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'users' => $users]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>