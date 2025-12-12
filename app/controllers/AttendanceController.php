<?php
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../helpers/TimezoneHelper.php';

class AttendanceController extends Controller {
    
    public function index() {
        $this->requireAuth();
        
        $role = $_SESSION['role'] ?? 'user';
        
        if ($role === 'user') {
            $this->handleUserView();
        } else {
            $this->handleAdminView();
        }
    }
    
    private function handleUserView() {
        $attendance = [];
        $filter = $_GET['filter'] ?? 'today';
        
        try {
            require_once __DIR__ . '/../config/database.php';
            $db = Database::connect();
            
            $dateCondition = $this->getDateCondition($filter);
            
            $stmt = $db->prepare("SELECT a.*, u.name as user_name, COALESCE(a.location_type, 'office') as location_type, COALESCE(a.location_title, CASE WHEN a.location_name IS NOT NULL THEN a.location_name WHEN p.location_title IS NOT NULL THEN p.location_title WHEN s.location_title IS NOT NULL THEN s.location_title ELSE 'Main Office' END) as location_title, COALESCE(a.location_radius, CASE WHEN p.checkin_radius IS NOT NULL THEN p.checkin_radius WHEN s.attendance_radius IS NOT NULL THEN s.attendance_radius ELSE 50 END) as location_radius, COALESCE(d.name, 'Not Assigned') as department, CASE WHEN a.check_in IS NOT NULL AND a.check_out IS NOT NULL THEN CONCAT(FLOOR(TIMESTAMPDIFF(MINUTE, a.check_in, a.check_out) / 60), 'h ', MOD(TIMESTAMPDIFF(MINUTE, a.check_in, a.check_out), 60), 'm') ELSE '0h 0m' END as working_hours FROM attendance a LEFT JOIN users u ON a.user_id = u.id LEFT JOIN departments d ON u.department_id = d.id LEFT JOIN projects p ON a.project_id = p.id LEFT JOIN settings s ON s.id = 1 WHERE a.user_id = ? AND $dateCondition ORDER BY a.check_in DESC");
            $stmt->execute([$_SESSION['user_id']]);
            $attendance = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Times are already in IST, no conversion needed
            
            $stats = $this->calculateUserStats($attendance);
            
        } catch (Exception $e) {
            error_log('Attendance index error: ' . $e->getMessage());
            $stats = ['total_hours' => 0, 'total_minutes' => 0, 'present_days' => 0];
        }
        
        $this->view('attendance/index', [
            'attendance' => $attendance, 
            'stats' => $stats,
            'current_filter' => $filter,
            'active_page' => 'attendance'
        ]);
    }
    
    private function handleAdminView() {
        $employeeAttendance = [];
        $adminAttendance = null;
        
        try {
            require_once __DIR__ . '/../config/database.php';
            $db = Database::connect();
            
            DatabaseHelper::safeExec($db, "SET time_zone = '+00:00'", "Set variable");
            $this->ensureAttendanceTable($db);
            
            $filterDate = $_GET['date'] ?? date('Y-m-d');
            $role = $_SESSION['role'] ?? 'admin';
            
            $roleFilter = ($role === 'owner') ? "u.role IN ('admin', 'user', 'owner')" : "u.role = 'user'";
            
            // Debug log: Fetching attendance with location data
            error_log("[ATTENDANCE_DEBUG] Fetching attendance for date: $filterDate with role filter: $roleFilter");
            
            // Get users with attendance including location data
            $stmt = $db->prepare("
                SELECT 
                    u.id,
                    u.id as user_id,
                    u.name,
                    u.email,
                    u.role,
                    COALESCE(d.name, 'Not Assigned') as department,
                    a.check_in,
                    a.check_out,
                    COALESCE(a.location_type, 'office') as location_type,
                    COALESCE(a.location_title, 
                        CASE 
                            WHEN a.location_name IS NOT NULL THEN a.location_name
                            WHEN p.location_title IS NOT NULL THEN p.location_title
                            WHEN s.location_title IS NOT NULL THEN s.location_title
                            ELSE 'Main Office'
                        END
                    ) as location_title,
                    COALESCE(a.location_radius, 
                        CASE 
                            WHEN p.checkin_radius IS NOT NULL THEN p.checkin_radius
                            WHEN s.attendance_radius IS NOT NULL THEN s.attendance_radius
                            ELSE 50
                        END
                    ) as location_radius,
                    CASE 
                        WHEN a.location_name = 'On Approved Leave' THEN 'On Leave'
                        WHEN a.check_in IS NOT NULL THEN 'Present'
                        ELSE 'Absent'
                    END as status,
                    CASE 
                        WHEN a.check_in IS NOT NULL AND a.check_out IS NOT NULL THEN 
                            CONCAT(FLOOR(TIMESTAMPDIFF(MINUTE, a.check_in, a.check_out) / 60), 'h ', 
                                   MOD(TIMESTAMPDIFF(MINUTE, a.check_in, a.check_out), 60), 'm')
                        ELSE '0h 0m'
                    END as working_hours
                FROM users u
                LEFT JOIN departments d ON u.department_id = d.id
                LEFT JOIN attendance a ON u.id = a.user_id AND (DATE(a.check_in) = ? OR a.date = ?)
                LEFT JOIN projects p ON a.project_id = p.id
                LEFT JOIN settings s ON s.id = 1
                WHERE $roleFilter AND u.status = 'active'
                ORDER BY u.role DESC, u.name
            ");
            $stmt->execute([$filterDate, $filterDate]);
            $employeeAttendance = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Debug log: Location data fetch results
            foreach ($employeeAttendance as $emp) {
                if ($emp['check_in']) {
                    error_log("[ATTENDANCE_DEBUG] Employee {$emp['name']}: Location={$emp['location_title']}, Type={$emp['location_type']}, Radius={$emp['location_radius']}");
                }
            }
            
            // Times are already in IST, no conversion needed
            
            // Get admin's own attendance with location data
            $stmt = $db->prepare("SELECT a.*, COALESCE(a.location_type, 'office') as location_type, COALESCE(a.location_title, CASE WHEN a.location_name IS NOT NULL THEN a.location_name WHEN p.location_title IS NOT NULL THEN p.location_title WHEN s.location_title IS NOT NULL THEN s.location_title ELSE 'Main Office' END) as location_title, COALESCE(a.location_radius, CASE WHEN p.checkin_radius IS NOT NULL THEN p.checkin_radius WHEN s.attendance_radius IS NOT NULL THEN s.attendance_radius ELSE 50 END) as location_radius, CASE WHEN a.check_in IS NOT NULL AND a.check_out IS NOT NULL THEN CONCAT(FLOOR(TIMESTAMPDIFF(MINUTE, a.check_in, a.check_out) / 60), 'h ', MOD(TIMESTAMPDIFF(MINUTE, a.check_in, a.check_out), 60), 'm') ELSE '0h 0m' END as working_hours FROM attendance a LEFT JOIN projects p ON a.project_id = p.id LEFT JOIN settings s ON s.id = 1 WHERE a.user_id = ? AND DATE(a.check_in) = ?");
            $stmt->execute([$_SESSION['user_id'], $filterDate]);
            $adminAttendance = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Debug log: Admin attendance location data
            if ($adminAttendance && $adminAttendance['check_in']) {
                error_log("[ATTENDANCE_DEBUG] Admin attendance: Location={$adminAttendance['location_title']}, Type={$adminAttendance['location_type']}, Radius={$adminAttendance['location_radius']}");
            }
            
            // Times are already in IST, no conversion needed
            
        } catch (Exception $e) {
            error_log('Attendance error: ' . $e->getMessage());
        }
        
        // Handle AJAX requests
        if (isset($_GET['ajax']) && $_GET['ajax'] == '1') {
            $this->handleAjaxResponse($employeeAttendance);
            return;
        }
        
        $viewName = ($_SESSION['role'] === 'owner') ? 'attendance/owner_index' : 'attendance/admin_index';
        $this->view($viewName, [
            'employees' => $employeeAttendance, 
            'admin_attendance' => $adminAttendance,
            'active_page' => 'attendance',
            'filter_date' => $filterDate,
            'user_role' => $_SESSION['role']
        ]);
    }
    
    private function handleAjaxResponse($employees) {
        header('Content-Type: text/html');
        echo "<table class='table'><tbody>";
        
        if (empty($employees)) {
            echo "<tr><td colspan='7' class='text-center text-muted py-4'>No employees found.</td></tr>";
        } else {
            foreach ($employees as $employee) {
                echo "<tr>";
                echo "<td>";
                echo "<div style='display: flex; align-items: center; gap: 0.5rem;'>";
                $bgColor = $employee['role'] === 'admin' ? '#8b5cf6' : ($employee['status'] === 'Present' ? '#22c55e' : '#ef4444');
                $icon = $employee['role'] === 'admin' ? '👔' : strtoupper(substr($employee['name'], 0, 2));
                echo "<div style='width: 32px; height: 32px; border-radius: 50%; background: $bgColor; display: flex; align-items: center; justify-content: center; color: white; font-size: 0.75rem; font-weight: bold;'>$icon</div>";
                echo "<div><div style='font-weight: 500;'>" . htmlspecialchars($employee['name']) . "</div>";
                echo "<div style='font-size: 0.75rem; color: #6b7280;'>" . htmlspecialchars($employee['email']) . "</div></div></div></td>";
                echo "<td>" . htmlspecialchars($employee['department']) . "</td>";
                
                $statusBadge = $employee['status'] === 'Present' ? 'success' : 'danger';
                $statusIcon = $employee['status'] === 'Present' ? '✅' : '❌';
                if ($employee['status'] === 'On Leave') {
                    echo "<td><span class='badge badge--warning'>🏖️ On Leave</span></td>";
                } else {
                    echo "<td><span class='badge badge--$statusBadge'>$statusIcon {$employee['status']}</span></td>";
                }
                
                $checkInTime = $employee['check_in'] ? TimezoneHelper::displayTime($employee['check_in']) : null;
                echo "<td>" . ($checkInTime ? "<span style='color: #059669; font-weight: 500;'>$checkInTime</span>" : '<span style="color: #6b7280;">-</span>') . "</td>";
                
                $checkOutTime = $employee['check_out'] ? TimezoneHelper::displayTime($employee['check_out']) : null;
                if ($checkOutTime) {
                    echo "<td><span style='color: #dc2626; font-weight: 500;'>$checkOutTime</span></td>";
                } elseif ($employee['check_in']) {
                    echo "<td><span style='color: #f59e0b; font-weight: 500;'>Working...</span></td>";
                } else {
                    echo "<td><span style='color: #6b7280;'>-</span></td>";
                }
                
                echo "<td>" . ($employee['working_hours'] !== '0h 0m' ? "<span style='color: #1f2937; font-weight: 500;'>" . htmlspecialchars($employee['working_hours']) . "</span>" : "<span style='color: #6b7280;'>0h 0m</span>") . "</td>";
                echo "<td><div style='display: flex; gap: 0.25rem;'>";
                echo "<button class='btn btn--sm btn--secondary' onclick='viewEmployeeDetails({$employee['id']})' title='View Details'><span>👁️</span></button>";
                if ($employee['status'] === 'Absent') {
                    echo "<button class='btn btn--sm btn--warning' onclick='markManualAttendance({$employee['id']})' title='Manual Entry'><span>✏️</span></button>";
                }
                echo "</div></td></tr>";
            }
        }
        echo "</tbody></table>";
        exit;
    }
    
    public function status() {
        $this->requireAuth();
        
        header('Content-Type: application/json');
        
        try {
            require_once __DIR__ . '/../config/database.php';
            $db = Database::connect();
            
            $currentDate = TimezoneHelper::getCurrentDate();
            
            $stmt = $db->prepare("SELECT * FROM attendance WHERE user_id = ? AND DATE(check_in) = ?");
            $stmt->execute([$_SESSION['user_id'], $currentDate]);
            $todayAttendance = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $onLeave = false;
            try {
                $stmt = $db->prepare("SELECT id FROM leaves WHERE user_id = ? AND status = 'approved' AND CURDATE() BETWEEN start_date AND end_date");
                $stmt->execute([$_SESSION['user_id']]);
                $onLeave = $stmt->fetch() ? true : false;
            } catch (Exception $e) {
                $onLeave = false;
            }
            
            echo json_encode([
                'success' => true,
                'attendance' => $todayAttendance,
                'on_leave' => $onLeave,
                'can_clock_in' => !$todayAttendance && !$onLeave,
                'can_clock_out' => $todayAttendance && !$todayAttendance['check_out']
            ]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }
    
    public function manual() {
        $this->requireAuth();
        
        if (!in_array($_SESSION['role'], ['admin', 'owner'])) {
            header('HTTP/1.1 403 Forbidden');
            exit('Access denied');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                require_once __DIR__ . '/../config/database.php';
                $db = Database::connect();
                
                $userId = intval($_POST['user_id']);
                $checkIn = $_POST['check_in'] ?? null;
                $checkOut = $_POST['check_out'] ?? null;
                $date = $_POST['date'] ?? date('Y-m-d');
                
                $stmt = $db->prepare("SELECT id FROM attendance WHERE user_id = ? AND DATE(check_in) = ?");
                $stmt->execute([$userId, $date]);
                $existing = $stmt->fetch();
                
                if ($existing) {
                    $stmt = $db->prepare("UPDATE attendance SET check_in = ?, check_out = ?, updated_at = NOW() WHERE id = ?");
                    $stmt->execute([
                        $date . ' ' . $checkIn,
                        $checkOut ? $date . ' ' . $checkOut : null,
                        $existing['id']
                    ]);
                } else {
                    $stmt = $db->prepare("INSERT INTO attendance (user_id, check_in, check_out, status, location_name, created_at) VALUES (?, ?, ?, 'present', 'Manual Entry', NOW())");
                    $stmt->execute([
                        $userId,
                        $date . ' ' . $checkIn,
                        $checkOut ? $date . ' ' . $checkOut : null
                    ]);
                }
                
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'Manual attendance recorded']);
                exit;
                
            } catch (Exception $e) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
                exit;
            }
        }
    }
    
    public function clock() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $this->requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleClockAction();
        } else {
            $this->showClockPage();
        }
    }
    
    private function handleClockAction() {
        try {
            require_once __DIR__ . '/../config/database.php';
            $db = Database::connect();
            
            DatabaseHelper::safeExec($db, "SET time_zone = '+00:00'", "Set variable");
            $this->ensureAttendanceTable($db);
            
            $type = $_POST['type'] ?? '';
            $userId = $_SESSION['user_id'];
            
            header('Content-Type: application/json');
            
            if ($type === 'in') {
                $this->handleClockIn($db, $userId);
            } elseif ($type === 'out') {
                $this->handleClockOut($db, $userId);
            } else {
                echo json_encode(['success' => false, 'error' => 'Invalid action']);
            }
            exit;
            
        } catch (Exception $e) {
            error_log('Attendance clock error: ' . $e->getMessage());
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Server error: ' . $e->getMessage()]);
            exit;
        }
    }
    
    private function handleClockIn($db, $userId) {
        // Check if already clocked in today
        $currentDate = TimezoneHelper::getCurrentDate();
        $stmt = $db->prepare("SELECT id FROM attendance WHERE user_id = ? AND DATE(check_in) = ? AND (check_out IS NULL OR check_out = '')");
        $stmt->execute([$userId, $currentDate]);
        
        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'error' => 'Already clocked in today']);
            return;
        }
        
        $userLat = floatval($_POST['latitude'] ?? 0);
        $userLng = floatval($_POST['longitude'] ?? 0);
        $projectId = intval($_POST['project_id'] ?? 0);
        
        // Validate GPS coordinates
        if ($userLat == 0 || $userLng == 0) {
            echo json_encode(['success' => false, 'error' => 'GPS location is required for attendance. Please enable location access and try again.']);
            return;
        }
        
        // Check location and get details
        $locationValidation = $this->validateUserLocation($db, $userLat, $userLng, $projectId);
        if (!$locationValidation['allowed']) {
            echo json_encode(['success' => false, 'error' => $locationValidation['error']]);
            return;
        }
        
        $locationInfo = $locationValidation['location_info'];
        
        $currentTime = TimezoneHelper::nowIst();
        
        $stmt = $db->prepare("INSERT INTO attendance (user_id, project_id, check_in, location_name, location_type, location_title, location_radius, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $result = $stmt->execute([$userId, $locationInfo['project_id'], $currentTime, $locationInfo['title'], $locationInfo['type'], $locationInfo['title'], $locationInfo['radius'], $currentTime]);
        
        if ($result) {
            // Create service history entry if project-based
            if ($locationInfo['project_id']) {
                $stmt = $db->prepare("INSERT INTO service_history (user_id, project_id, attendance_id, service_date, start_time, location_lat, location_lng) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$userId, $locationInfo['project_id'], $db->lastInsertId(), $currentDate, date('H:i:s'), $userLat, $userLng]);
            }
            
            echo json_encode(['success' => true, 'message' => 'Clocked in successfully from ' . $locationInfo['title']]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to clock in']);
        }
    }
    
    private function handleClockOut($db, $userId) {
        $currentTime = TimezoneHelper::nowIst();
        $currentDate = TimezoneHelper::getCurrentDate();
        
        $stmt = $db->prepare("SELECT * FROM attendance WHERE user_id = ? AND DATE(check_in) = ? AND check_out IS NULL");
        $stmt->execute([$userId, $currentDate]);
        $attendance = $stmt->fetch();
        
        if (!$attendance) {
            echo json_encode(['success' => false, 'error' => 'No clock in record found for today']);
            return;
        }
        
        $userLat = floatval($_POST['latitude'] ?? 0);
        $userLng = floatval($_POST['longitude'] ?? 0);
        
        // Validate GPS coordinates
        if ($userLat == 0 || $userLng == 0) {
            echo json_encode(['success' => false, 'error' => 'GPS location is required for attendance. Please enable location access and try again.']);
            return;
        }
        
        // Check location for checkout
        $locationValidation = $this->validateUserLocation($db, $userLat, $userLng, $attendance['project_id']);
        if (!$locationValidation['allowed']) {
            echo json_encode(['success' => false, 'error' => $locationValidation['error']]);
            return;
        }
        
        $stmt = $db->prepare("UPDATE attendance SET check_out = ? WHERE id = ?");
        $result = $stmt->execute([$currentTime, $attendance['id']]);
        
        if ($result) {
            // Update service history
            $hoursWorked = (strtotime($currentTime) - strtotime($attendance['check_in'])) / 3600;
            $stmt = $db->prepare("UPDATE service_history SET end_time = ?, hours_worked = ?, status = 'completed' WHERE attendance_id = ?");
            $stmt->execute([date('H:i:s'), round($hoursWorked, 2), $attendance['id']]);
            
            echo json_encode(['success' => true, 'message' => 'Clocked out successfully']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to clock out']);
        }
    }
    
    private function showClockPage() {
        $todayAttendance = null;
        $onLeave = false;
        
        try {
            require_once __DIR__ . '/../config/database.php';
            $db = Database::connect();
            $this->ensureAttendanceTable($db);
            
            $currentDate = TimezoneHelper::getCurrentDate();
            
            $stmt = $db->prepare("SELECT * FROM attendance WHERE user_id = ? AND DATE(check_in) = ?");
            $stmt->execute([$_SESSION['user_id'], $currentDate]);
            $todayAttendance = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Check if user is on leave
            try {
                $stmt = $db->prepare("SELECT id FROM leaves WHERE user_id = ? AND status = 'approved' AND CURDATE() BETWEEN start_date AND end_date");
                $stmt->execute([$_SESSION['user_id']]);
                $onLeave = $stmt->fetch() ? true : false;
            } catch (Exception $e) {
                $onLeave = false;
            }
            
        } catch (Exception $e) {
            error_log('Today attendance fetch error: ' . $e->getMessage());
        }
        
        // Prepare attendance status for JavaScript
        $attendanceStatus = [
            'has_clocked_in' => $todayAttendance ? true : false,
            'has_clocked_out' => $todayAttendance && $todayAttendance['check_out'] ? true : false,
            'on_leave' => $onLeave,
            'is_completed' => $todayAttendance && $todayAttendance['check_out'] ? true : false
        ];
        
        $this->view('attendance/clock', [
            'today_attendance' => $todayAttendance, 
            'on_leave' => $onLeave, 
            'attendance_status' => $attendanceStatus,
            'active_page' => 'attendance'
        ]);
    }
    
    private function ensureAttendanceTable($db) {
        try {
            DatabaseHelper::safeExec($db, "CREATE TABLE IF NOT EXISTS attendance (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                project_id INT NULL,
                check_in DATETIME NOT NULL,
                check_out DATETIME NULL,
                location_name VARCHAR(255) DEFAULT 'Office',
                location_type VARCHAR(50) NULL,
                location_title VARCHAR(255) NULL,
                location_radius INT NULL,
                status VARCHAR(20) DEFAULT 'present',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_user_id (user_id),
                INDEX idx_check_in_date (check_in)
            )", "Create table");
            
            DatabaseHelper::safeExec($db, "UPDATE attendance SET check_out = NULL WHERE check_out = '' OR check_out = '0000-00-00 00:00:00'", "Update data");
            
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
    
    private function validateUserLocation($db, $userLat, $userLng, $projectId = null) {
        // Log the validation attempt
        error_log("[LOCATION_DEBUG] Validating location: User({$userLat}, {$userLng}), ProjectID: {$projectId}");
        
        $allowedLocations = [];
        $userLocation = "User Location: {$userLat}, {$userLng}";
        
        // Check if within any project radius first
        $stmt = $db->prepare("SELECT id, name, latitude, longitude, checkin_radius, location_title FROM projects WHERE latitude IS NOT NULL AND longitude IS NOT NULL AND status = 'active'");
        $stmt->execute();
        $projects = $stmt->fetchAll();
        
        foreach ($projects as $project) {
            if ($project['latitude'] != 0 && $project['longitude'] != 0) {
                $distance = $this->calculateDistance($userLat, $userLng, $project['latitude'], $project['longitude']);
                $allowedLocations[] = [
                    'name' => $project['location_title'] ?: $project['name'] . ' Site',
                    'distance' => $distance,
                    'radius' => $project['checkin_radius'],
                    'coords' => "({$project['latitude']}, {$project['longitude']})"
                ];
                
                error_log("[LOCATION_DEBUG] Project {$project['name']}: Distance={$distance}m, Allowed={$project['checkin_radius']}m");
                
                if ($distance <= $project['checkin_radius']) {
                    return [
                        'allowed' => true,
                        'location_info' => [
                            'type' => 'project',
                            'title' => $project['location_title'] ?: $project['name'] . ' Site',
                            'radius' => $project['checkin_radius'],
                            'project_id' => $project['id']
                        ]
                    ];
                }
            }
        }
        
        // Check if within office/settings radius
        $this->ensureSettingsTable($db);
        $stmt = $db->prepare("SELECT base_location_lat, base_location_lng, attendance_radius, location_title FROM settings LIMIT 1");
        $stmt->execute();
        $settings = $stmt->fetch();
        
        if ($settings && $settings['base_location_lat'] != 0 && $settings['base_location_lng'] != 0) {
            $distance = $this->calculateDistance($userLat, $userLng, $settings['base_location_lat'], $settings['base_location_lng']);
            $allowedLocations[] = [
                'name' => $settings['location_title'] ?: 'Main Office',
                'distance' => $distance,
                'radius' => $settings['attendance_radius'],
                'coords' => "({$settings['base_location_lat']}, {$settings['base_location_lng']})"
            ];
            
            error_log("[LOCATION_DEBUG] Office: Distance={$distance}m, Allowed={$settings['attendance_radius']}m");
            
            if ($distance <= $settings['attendance_radius']) {
                return [
                    'allowed' => true,
                    'location_info' => [
                        'type' => 'office',
                        'title' => $settings['location_title'] ?: 'Main Office',
                        'radius' => $settings['attendance_radius'],
                        'project_id' => null
                    ]
                ];
            }
        }
        
        // Generate detailed error message
        $errorMessage = "Please move within the allowed area to continue.\n\n";
        $errorMessage .= "{$userLocation}\n\n";
        $errorMessage .= "Allowed Locations:\n";
        
        if (empty($allowedLocations)) {
            $errorMessage .= "No attendance locations have been configured by your administrator.";
        } else {
            foreach ($allowedLocations as $location) {
                $errorMessage .= "• {$location['name']} {$location['coords']} - ";
                $errorMessage .= "You are {$location['distance']}m away (max {$location['radius']}m allowed)\n";
            }
        }
        
        error_log("[LOCATION_DEBUG] Validation failed: {$errorMessage}");
        
        return [
            'allowed' => false,
            'error' => $errorMessage
        ];
    }
    
    private function ensureSettingsTable($db) {
        try {
            DatabaseHelper::safeExec($db, "CREATE TABLE IF NOT EXISTS settings (
                id INT AUTO_INCREMENT PRIMARY KEY,
                company_name VARCHAR(255) DEFAULT 'ERGON Company',
                base_location_lat DECIMAL(10,8) DEFAULT 0,
                base_location_lng DECIMAL(11,8) DEFAULT 0,
                attendance_radius INT DEFAULT 5,
                location_title VARCHAR(255) DEFAULT 'Main Office',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )", "Create table");
            
            // Add location_title column if it doesn't exist
            try {
                DatabaseHelper::safeExec($db, "ALTER TABLE settings ADD COLUMN location_title VARCHAR(255) DEFAULT 'Main Office'", "Alter table");
            } catch (Exception $e) {
                // Column might already exist, ignore error
            }
            
            // Insert default settings if none exist
            $stmt = $db->query("SELECT COUNT(*) as count FROM settings");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($result['count'] == 0) {
                DatabaseHelper::safeExec($db, "INSERT INTO settings (company_name, base_location_lat, base_location_lng, attendance_radius, location_title) VALUES ('ERGON Company', 0, 0, 5, 'Main Office')", "Insert data");
            }
            
            // Update existing records to have location_title if null
            DatabaseHelper::safeExec($db, "UPDATE settings SET location_title = 'Main Office' WHERE location_title IS NULL OR location_title = ''", "Update data");
            
        } catch (Exception $e) {
            error_log('ensureSettingsTable error: ' . $e->getMessage());
        }
    }
    
    private function calculateDistance($lat1, $lng1, $lat2, $lng2) {
        $earthRadius = 6371000; // Earth radius in meters
        
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        
        $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng/2) * sin($dLng/2);
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        
        return $earthRadius * $c; // Distance in meters
    }
    
    private function calculateUserStats($attendance) {
        $totalMinutes = 0;
        $presentDays = 0;
        
        foreach ($attendance as $record) {
            if ($record['check_in'] && $record['check_out']) {
                $minutes = (strtotime($record['check_out']) - strtotime($record['check_in'])) / 60;
                $totalMinutes += $minutes;
                $presentDays++;
            } elseif ($record['check_in']) {
                $presentDays++;
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
    
    public function delete() {
        $this->requireAuth();
        
        if (!in_array($_SESSION['role'], ['admin', 'owner'])) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Access denied']);
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                require_once __DIR__ . '/../config/database.php';
                $db = Database::connect();
                
                $attendanceId = intval($_POST['id'] ?? 0);
                
                if ($attendanceId <= 0) {
                    throw new Exception('Invalid attendance ID');
                }
                
                $stmt = $db->prepare("DELETE FROM attendance WHERE id = ?");
                $result = $stmt->execute([$attendanceId]);
                
                if ($result && $stmt->rowCount() > 0) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => true, 'message' => 'Attendance record deleted successfully']);
                } else {
                    throw new Exception('Attendance record not found or could not be deleted');
                }
                
            } catch (Exception $e) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            }
            exit;
        }
        
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Invalid request method']);
        exit;
    }
    
    public function serviceHistory() {
        $this->requireAuth();
        $this->view('attendance/service_history', ['active_page' => 'attendance']);
    }
}
?>
