<?php
header('Content-Type: application/json; charset=utf-8');
session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Authentication required']);
    exit;
}

require_once __DIR__ . '/../app/config/database.php';
require_once __DIR__ . '/../app/helpers/DatabaseHelper.php';

// Parse input
$raw = file_get_contents('php://input');
$input = json_decode($raw, true);
if (!is_array($input)) $input = $_POST;

$action = $_GET['action'] ?? $input['action'] ?? null;
$task_id = $input['task_id'] ?? null;

if (empty($action)) {
    http_response_code(400);
    echo json_encode(['error' => 'missing action']);
    exit;
}

if (in_array($action, ['start', 'pause', 'resume', 'update-progress', 'postpone']) && !$task_id) {
    http_response_code(400);
    echo json_encode(['error' => 'missing task_id']);
    exit;
}

$userId = (int)$_SESSION['user_id'];

try {
    $db = Database::connect();
    
    switch ($action) {
        case 'start':
            try {
                // Get task with current progress
                $stmt = $db->prepare("
                    SELECT id, status, completed_percentage FROM daily_tasks 
                    WHERE id = ? AND user_id = ?
                ");
                $stmt->execute([$task_id, $userId]);
                $task = $stmt->fetch();
                
                if (!$task) {
                    http_response_code(404);
                    echo json_encode(['error' => 'Task not found']);
                    exit;
                }
                
                if ($task['status'] !== 'not_started') {
                    http_response_code(400);
                    echo json_encode(['error' => 'Task cannot be started from current status: ' . $task['status']]);
                    exit;
                }
                
                $now = date('Y-m-d H:i:s');
                $slaEndTime = date('Y-m-d H:i:s', strtotime('+15 minutes')); // Default 15 min SLA
                
                // Preserve existing progress when starting
                $stmt = $db->prepare("
                    UPDATE daily_tasks 
                    SET status = 'in_progress', 
                        start_time = ?, 
                        sla_end_time = ?,
                        updated_at = NOW()
                    WHERE id = ? AND user_id = ?
                ");
                
                $result = $stmt->execute([$now, $slaEndTime, $task_id, $userId]);
                
                if ($result && $stmt->rowCount() > 0) {
                    echo json_encode([
                        'success' => true,
                        'status' => 'in_progress',
                        'label' => 'Break',
                        'message' => 'Task started successfully',
                        'progress' => (int)($task['completed_percentage'] ?? 0)
                    ]);
                } else {
                    http_response_code(400);
                    echo json_encode(['error' => 'Failed to start task']);
                }
                
            } catch (Exception $e) {
                error_log('Start task error: ' . $e->getMessage());
                http_response_code(500);
                echo json_encode(['error' => 'Start task error: ' . $e->getMessage()]);
            }
            break;
            
        case 'pause':
            try {
                $stmt = $db->prepare("
                    SELECT id, status FROM daily_tasks 
                    WHERE id = ? AND user_id = ? AND status = 'in_progress'
                ");
                $stmt->execute([$task_id, $userId]);
                $task = $stmt->fetch();
                
                if (!$task) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Task not found or not in progress']);
                    exit;
                }
                
                $now = date('Y-m-d H:i:s');
                $stmt = $db->prepare("
                    UPDATE daily_tasks 
                    SET status = 'on_break', 
                        pause_start_time = ?,
                        updated_at = NOW()
                    WHERE id = ? AND user_id = ?
                ");
                
                $result = $stmt->execute([$now, $task_id, $userId]);
                
                if ($result && $stmt->rowCount() > 0) {
                    echo json_encode([
                        'success' => true,
                        'status' => 'on_break',
                        'label' => 'Resume'
                    ]);
                } else {
                    http_response_code(400);
                    echo json_encode(['error' => 'Failed to pause task']);
                }
                
            } catch (Exception $e) {
                error_log('Pause task error: ' . $e->getMessage());
                http_response_code(500);
                echo json_encode(['error' => 'Pause task error: ' . $e->getMessage()]);
            }
            break;
            
        case 'resume':
            try {
                $stmt = $db->prepare("
                    SELECT id, status FROM daily_tasks 
                    WHERE id = ? AND user_id = ? AND status = 'on_break'
                ");
                $stmt->execute([$task_id, $userId]);
                $task = $stmt->fetch();
                
                if (!$task) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Task not found or not on break']);
                    exit;
                }
                
                $now = date('Y-m-d H:i:s');
                $stmt = $db->prepare("
                    UPDATE daily_tasks 
                    SET status = 'in_progress', 
                        resume_time = ?,
                        pause_start_time = NULL,
                        updated_at = NOW()
                    WHERE id = ? AND user_id = ?
                ");
                
                $result = $stmt->execute([$now, $task_id, $userId]);
                
                if ($result && $stmt->rowCount() > 0) {
                    echo json_encode([
                        'success' => true,
                        'status' => 'in_progress',
                        'label' => 'Break'
                    ]);
                } else {
                    http_response_code(400);
                    echo json_encode(['error' => 'Failed to resume task']);
                }
                
            } catch (Exception $e) {
                error_log('Resume task error: ' . $e->getMessage());
                http_response_code(500);
                echo json_encode(['error' => 'Resume task error: ' . $e->getMessage()]);
            }
            break;
            
        case 'update-progress':
            try {
                $progress = $input['progress'] ?? null;
                $status = $input['status'] ?? null;
                
                if ($progress === null || $status === null) {
                    http_response_code(400);
                    echo json_encode(['error' => 'missing progress or status']);
                    exit;
                }
                
                $progress = (int)$progress;
                
                // Get the original task ID for syncing
                $stmt = $db->prepare("SELECT original_task_id, task_id FROM daily_tasks WHERE id = ? AND user_id = ?");
                $stmt->execute([$task_id, $userId]);
                $dailyTask = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$dailyTask) {
                    http_response_code(404);
                    echo json_encode(['error' => 'Daily task not found']);
                    exit;
                }
                
                $db->beginTransaction();
                
                // Update daily_tasks table
                if ($progress >= 100 || $status === 'completed') {
                    $status = 'completed';
                    $progress = 100;
                    $completionTime = date('Y-m-d H:i:s');
                    
                    $stmt = $db->prepare("
                        UPDATE daily_tasks 
                        SET status = 'completed', 
                            completed_percentage = 100,
                            completion_time = ?,
                            updated_at = NOW()
                        WHERE id = ? AND user_id = ?
                    ");
                    $result = $stmt->execute([$completionTime, $task_id, $userId]);
                } else {
                    $stmt = $db->prepare("
                        UPDATE daily_tasks 
                        SET status = ?, 
                            completed_percentage = ?,
                            updated_at = NOW()
                        WHERE id = ? AND user_id = ?
                    ");
                    $result = $stmt->execute([$status, $progress, $task_id, $userId]);
                }
                
                if ($result) {
                    // Sync with main tasks table if linked
                    $originalTaskId = $dailyTask['original_task_id'] ?: $dailyTask['task_id'];
                    if ($originalTaskId) {
                        $stmt = $db->prepare("UPDATE tasks SET status = ?, progress = ?, updated_at = NOW() WHERE id = ?");
                        $stmt->execute([$status, $progress, $originalTaskId]);
                    }
                    
                    $db->commit();
                    echo json_encode([
                        'success' => true,
                        'message' => 'Task synced with planner',
                        'progress' => $progress,
                        'status' => $status
                    ]);
                } else {
                    $db->rollback();
                    http_response_code(400);
                    echo json_encode(['error' => 'Failed to sync daily task']);
                }
                
            } catch (Exception $e) {
                if ($db && $db->inTransaction()) {
                    $db->rollback();
                }
                error_log('Update progress error: ' . $e->getMessage());
                http_response_code(500);
                echo json_encode(['error' => 'Update progress error: ' . $e->getMessage()]);
            }
            break;
            
        case 'postpone':
            try {
                $new_date = $input['new_date'] ?? null;
                $reason = $input['reason'] ?? 'Postponed via daily planner';
                
                if (!$new_date) {
                    http_response_code(400);
                    echo json_encode(['error' => 'missing new_date']);
                    exit;
                }
                
                require_once __DIR__ . '/../app/models/DailyPlanner.php';
                $planner = new DailyPlanner();
                
                if ($planner->postponeTask($task_id, $userId, $new_date)) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'Task postponed to ' . $new_date
                    ]);
                } else {
                    http_response_code(400);
                    echo json_encode(['error' => 'Failed to postpone task']);
                }
                
            } catch (Exception $e) {
                error_log('Postpone task error: ' . $e->getMessage());
                http_response_code(500);
                echo json_encode(['error' => 'Postpone task error: ' . $e->getMessage()]);
            }
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Unknown action: ' . $action]);
            break;
    }
    
} catch (Exception $e) {
    error_log('Daily planner workflow error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error: ' . $e->getMessage()]);
}
?>