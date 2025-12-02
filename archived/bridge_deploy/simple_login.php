<?php
/**
 * Simple login endpoint for testing
 */

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

if (empty($email) || empty($password)) {
    http_response_code(400);
    echo json_encode(['error' => 'Email and password are required']);
    exit;
}

// Check against actual database
try {
    require_once __DIR__ . '/app/config/database.php';
    $db = Database::connect();
    
    $stmt = $db->prepare("SELECT id, name, email, role, password FROM users WHERE email = ? AND status = 'active'");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && password_verify($password, $user['password'])) {
        session_start();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['login_time'] = time();
        $_SESSION['last_activity'] = time();
        
        header('Location: /ergon-site/dashboard');
        exit;
    } else {
        header('Location: /ergon-site/login?error=invalid');
        exit;
    }
} catch (Exception $e) {
    error_log('Login error: ' . $e->getMessage());
    header('Location: /ergon-site/login?error=system');
    exit;
}
?>
