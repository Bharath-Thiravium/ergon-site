<?php
header('Content-Type: application/json');
if (session_status() === PHP_SESSION_NONE) session_start();

if (empty($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit;
}

try {
    require_once __DIR__ . '/../config/database.php';
    $db = Database::connect();
    
    $user_id = $_SESSION['user_id'];
    $user_lat = floatval($_POST['latitude'] ?? 0);
    $user_lng = floatval($_POST['longitude'] ?? 0);
    
    // Get user's current project
    $stmt = $db->prepare("
        SELECT u.current_project_id, u.project_name, p.latitude, p.longitude, p.checkin_radius, p.name as project_name_db
        FROM users u 
        LEFT JOIN projects p ON u.current_project_id = p.id 
        WHERE u.id = ?
    ");
    $stmt->execute([$user_id]);
    $user_project = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user_project || !$user_project['current_project_id']) {
        echo json_encode([
            'success' => false, 
            'error' => 'No project assigned. Contact admin to assign a project.',
            'requires_admin' => true
        ]);
        exit;
    }
    
    $project_lat = floatval($user_project['latitude']);
    $project_lng = floatval($user_project['longitude']);
    $radius = intval($user_project['checkin_radius']);
    
    // Calculate distance using Haversine formula
    $distance = calculateDistance($user_lat, $user_lng, $project_lat, $project_lng);
    
    if ($distance <= $radius) {
        echo json_encode([
            'success' => true,
            'within_range' => true,
            'distance' => round($distance, 2),
            'allowed_radius' => $radius,
            'project_name' => $user_project['project_name_db'] ?: $user_project['project_name']
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'within_range' => false,
            'distance' => round($distance, 2),
            'allowed_radius' => $radius,
            'project_name' => $user_project['project_name_db'] ?: $user_project['project_name'],
            'error' => 'You are ' . round($distance - $radius, 2) . 'm outside the allowed check-in area. Contact admin for manual attendance.',
            'requires_admin' => true
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

function calculateDistance($lat1, $lng1, $lat2, $lng2) {
    $earthRadius = 6371000; // Earth radius in meters
    
    $dLat = deg2rad($lat2 - $lat1);
    $dLng = deg2rad($lng2 - $lng1);
    
    $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng/2) * sin($dLng/2);
    $c = 2 * atan2(sqrt($a), sqrt(1-$a));
    
    return $earthRadius * $c;
}
?>