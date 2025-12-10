<?php
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../helpers/TimezoneHelper.php';
require_once __DIR__ . '/../helpers/LocationHelper.php';
require_once __DIR__ . '/../helpers/DatabaseHelper.php';

class SimpleAttendanceController extends Controller {
    private $db;
    
    public function __construct() {
        require_once __DIR__ . '/../config/database.php';
        $this->db = Database::connect();
        $this->ensureAttendanceTable($this->db);
    }
    
    public function index() {
        $this->requireAuth();
        
        $role = $_SESSION['role'] ?? 'user';
        $userId = $_SESSION['user_id'];
        $selectedDate = $_GET['date'] ?? TimezoneHelper::getCurrentDate();
        $filter = $_GET['filter'] ?? 'today';
        
        // Query to get all users with their attendance data for the selected date
        $roleFilter = '';
        $roleParams = [];
        if ($role === 'user') {
            $roleFilter = "AND u.id = ?";
            $roleParams[] = $userId;
        } elseif ($role === 'admin') {
            // Include both admin's own attendance and employee attendance
            $roleFilter = "AND (u.role IN ('user') OR u.id = ?)";
            $roleParams[] = $userId;
        } else {
            $roleFilter = "AND u.role IN ('admin', 'user')";
        }
        
        // Use date filter if provided, otherwise use time-based filter
        if (isset($_GET['date']) && $_GET['date'] !== TimezoneHelper::getCurrentDate()) {
            $dateCondition = "DATE(a.check_in) = ?";
            $roleParams[] = $selectedDate;
        } else {
            $dateCondition = $this->getDateCondition($filter);
        }
        
        $stmt = $this->db->prepare("
            SELECT 
                u.id as user_id,
                u.name,
                u.email,
                u.role,
                a.id as attendance_id,
                a.check_in,
                a.check_out,
                CASE 
                    WHEN a.check_in IS NOT NULL THEN 'Present'
                    ELSE 'Absent'
                END as status,
                COALESCE(TIME_FORMAT(a.check_in, '%H:%i'), '00:00') as check_in_time,
                COALESCE(TIME_FORMAT(a.check_out, '%H:%i'), '00:00') as check_out_time,
                CASE 
                    WHEN a.check_in IS NOT NULL AND a.check_out IS NOT NULL THEN 
                        CONCAT(TIMESTAMPDIFF(HOUR, a.check_in, a.check_out), 'h ', 
                               TIMESTAMPDIFF(MINUTE, a.check_in, a.check_out) % 60, 'm')
                    ELSE '0h 0m'
                END as working_hours
            FROM users u
            LEFT JOIN attendance a ON u.id = a.user_id AND {$dateCondition}
            WHERE u.status = 'active' {$roleFilter}
            ORDER BY u.role DESC, u.name
        ");
        $stmt->execute($roleParams);
        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Group by role for owner and admin view
        if ($role === 'owner' || $role === 'admin') {
            $attendance = ['admin' => [], 'user' => []];
            foreach ($records as $record) {
                $userRole = $record['role'] === 'admin' ? 'admin' : 'user';
                $attendance[$userRole][] = $record;
            }
            $isGrouped = true;
        } else {
            $attendance = $records;
            $isGrouped = false;
        }
        
        // Calculate stats
        $stats = ['total_hours' => 0, 'total_minutes' => 0, 'present_days' => 0];
        if (!empty($records)) {
            if ($role === 'user') {
                $stats = $this->calculateUserStats($records);
            } elseif ($role === 'admin') {
                // Calculate stats for admin's own records only
                $adminRecords = array_filter($records, function($record) {
                    return $record['user_id'] == $_SESSION['user_id'];
                });
                $stats = $this->calculateUserStats($adminRecords);
            }
        }
        
        $this->view('attendance/index', [
            'attendance' => $attendance,
            'stats' => $stats,
            'current_filter' => $filter,
            'selected_date' => $selectedDate,
            'user_role' => $role,
            'active_page' => 'attendance',
            'is_grouped' => $isGrouped
        ]);
    }
    
    public function status() {
        $this->requireAuth();
        
        header('Content-Type: application/json');
        
        try {
            $currentDate = TimezoneHelper::getCurrentDate();
            
            $stmt = $this->db->prepare("SELECT * FROM attendance WHERE user_id = ? AND DATE(check_in) = ?");
            $stmt->execute([$_SESSION['user_id'], $currentDate]);
            $todayAttendance = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $onLeave = false;
            try {
                $stmt = $this->db->prepare("SELECT id FROM leaves WHERE user_id = ? AND status = 'approved' AND CURDATE() BETWEEN start_date AND end_date");
                $stmt->execute([$_SESSION['user_id']]);
                $onLeave = $stmt->fetch() ? true : false;
            } catch (Exception $e) {
                $onLeave = false;
            }
            
            echo json_encode([
                'success' => true,
                'attendance' => $todayAttendance,
                'on_leave' => $onLeave
            ]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }
    
    private function ensureAttendanceTable($db) {
        try {
            DatabaseHelper::safeExec($db, "CREATE TABLE IF NOT EXISTS attendance (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                check_in DATETIME NOT NULL,
                check_out DATETIME NULL,
                latitude DECIMAL(10, 8) NULL,
                longitude DECIMAL(11, 8) NULL,
                location_name VARCHAR(255) DEFAULT 'Office',
                status VARCHAR(20) DEFAULT 'present',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_user_id (user_id),
                INDEX idx_check_in_date (check_in)
            )", "Create table");
            
        } catch (Exception $e) {
            error_log('ensureAttendanceTable error: ' . $e->getMessage());
        }
    }
    
    private function getDateCondition($filter) {
        switch ($filter) {
            case 'today':
                return "DATE(a.check_in) = CURDATE()";
            case 'week':
                return "DATE(a.check_in) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
            case 'two_weeks':
                return "DATE(a.check_in) >= DATE_SUB(CURDATE(), INTERVAL 14 DAY)";
            case 'month':
                return "DATE(a.check_in) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
            default:
                return "DATE(a.check_in) = CURDATE()";
        }
    }
    
    private function calculateUserStats($attendance) {
        $totalMinutes = 0;
        $presentDays = 0;
        
        foreach ($attendance as $record) {
            // Count as present if there's a check_in (regardless of check_out)
            if ($record['check_in'] && $record['status'] === 'Present') {
                $presentDays++;
                
                // Calculate working hours only if both check_in and check_out exist
                if ($record['check_out']) {
                    $minutes = (strtotime($record['check_out']) - strtotime($record['check_in'])) / 60;
                    $totalMinutes += $minutes;
                }
            }
        }
        
        $totalHours = (int)floor($totalMinutes / 60);
        $remainingMinutes = (int)((int)$totalMinutes % 60);
        
        return [
            'total_hours' => $totalHours,
            'total_minutes' => $remainingMinutes,
            'present_days' => $presentDays
        ];
    }
}
?>