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

$id = $_GET['id'] ?? null;

if (!$id) {
    echo json_encode(['success' => false, 'error' => 'ID required']);
    exit;
}

try {
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
    error_log('Advance API error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Server error']);
}
