<?php
require_once __DIR__ . '/../core/Controller.php';

class AdvanceController extends Controller {
    
    public function index() {
        $this->requireAuth();
        
        try {
            $user_id = $_SESSION['user_id'];
            $role = $_SESSION['role'] ?? 'user';
            
            require_once __DIR__ . '/../config/database.php';
            $db = Database::connect();
            
            // Ensure table exists with repayment_date column
            $db->exec("CREATE TABLE IF NOT EXISTS advances (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                type VARCHAR(50) DEFAULT 'General Advance',
                amount DECIMAL(10,2) NOT NULL,
                reason TEXT NOT NULL,
                requested_date DATE NULL,
                repayment_date DATE NULL,
                repayment_months INT DEFAULT 1,
                status VARCHAR(20) DEFAULT 'pending',
                approved_by INT NULL,
                approved_at DATETIME NULL,
                payment_proof VARCHAR(255) NULL,
                paid_by INT NULL,
                paid_at DATETIME NULL,
                rejection_reason TEXT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                rejected_by INT NULL,
                rejected_at TIMESTAMP NULL
            )");
            
            // Add repayment_date column if it doesn't exist
            try {
                $db->exec("ALTER TABLE advances ADD COLUMN repayment_date DATE NULL");
            } catch (Exception $e) {
                // Column already exists, ignore error
            }
            // Ensure payment columns exist
            try { $db->exec("ALTER TABLE advances ADD COLUMN payment_proof VARCHAR(255) NULL"); } catch (Exception $e) {}
            try { $db->exec("ALTER TABLE advances ADD COLUMN paid_by INT NULL"); } catch (Exception $e) {}
            try { $db->exec("ALTER TABLE advances ADD COLUMN paid_at DATETIME NULL"); } catch (Exception $e) {}
            try { $db->exec("ALTER TABLE advances ADD COLUMN approved_amount DECIMAL(10,2) NULL"); } catch (Exception $e) {}
            
            if ($role === 'user') {
                $stmt = $db->prepare("SELECT a.*, u.name as user_name, u.role as user_role FROM advances a JOIN users u ON a.user_id = u.id WHERE a.user_id = ? ORDER BY a.created_at DESC");
                $stmt->execute([$user_id]);
            } else {
                $stmt = $db->query("SELECT a.*, u.name as user_name, u.role as user_role FROM advances a JOIN users u ON a.user_id = u.id ORDER BY a.created_at DESC");
            }
            $advances = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $this->view('advances/index', ['advances' => $advances, 'user_role' => $role, 'active_page' => 'advances']);
        } catch (Exception $e) {
            error_log('Advance index error: ' . $e->getMessage());
            $this->view('advances/index', ['advances' => [], 'user_role' => $_SESSION['role'] ?? 'user', 'error' => 'Unable to load advances', 'active_page' => 'advances']);
        }
    }
    
    public function create() {
        $this->requireAuth();
        $this->view('advances/create');
    }
    
    public function store() {
        $this->requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            header('Content-Type: application/json');
            try {
                require_once __DIR__ . '/../config/database.php';
                $db = Database::connect();
                
                $repaymentDate = !empty($_POST['repayment_date']) ? $_POST['repayment_date'] : null;
                $stmt = $db->prepare("INSERT INTO advances (user_id, project_id, type, amount, reason, requested_date, repayment_date, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', NOW())");
                $result = $stmt->execute([
                    $_SESSION['user_id'],
                    $_POST['project_id'] ?: null,
                    trim($_POST['type'] ?? ''),
                    floatval($_POST['amount'] ?? 0),
                    trim($_POST['reason'] ?? ''),
                    date('Y-m-d'),
                    $repaymentDate
                ]);
                
                if ($result) {
                    $advanceId = $db->lastInsertId();
                    try {
                        require_once __DIR__ . '/../helpers/NotificationHelper.php';
                        NotificationHelper::notifyAdvanceRequest($advanceId, $_SESSION['user_id']);
                    } catch (Exception $notifError) {
                        error_log('Notification error (non-critical): ' . $notifError->getMessage());
                    }
                    echo json_encode(['success' => true]);
                } else {
                    echo json_encode(['success' => false, 'error' => 'Failed to create advance']);
                }
                exit;
            } catch (Exception $e) {
                error_log('Advance store error: ' . $e->getMessage());
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
                exit;
            }
        }
        
        echo json_encode(['success' => false, 'error' => 'Invalid request']);
        exit;
    }
    
    public function edit($id) {
        $this->requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                require_once __DIR__ . '/../config/database.php';
                $db = Database::connect();
                
                // Check if user owns this advance or is admin/owner
                $stmt = $db->prepare("SELECT user_id, status FROM advances WHERE id = ?");
                $stmt->execute([$id]);
                $advance = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$advance) {
                    header('Location: /ergon-site/advances?error=Advance not found');
                    exit;
                }
                
                // Only allow editing if user owns it or is admin/owner, and status is pending
                $canEdit = (($advance['user_id'] == $_SESSION['user_id']) || 
                           in_array($_SESSION['role'] ?? '', ['admin', 'owner'])) && 
                           $advance['status'] === 'pending';
                
                if (!$canEdit) {
                    header('Location: /ergon-site/advances?error=Cannot edit this advance');
                    exit;
                }
                
                header('Content-Type: application/json');
                $repaymentDate = !empty($_POST['repayment_date']) ? $_POST['repayment_date'] : null;
                $stmt = $db->prepare("UPDATE advances SET project_id = ?, type = ?, amount = ?, reason = ?, repayment_date = ? WHERE id = ?");
                $result = $stmt->execute([
                    $_POST['project_id'] ?: null,
                    trim($_POST['type'] ?? ''),
                    floatval($_POST['amount'] ?? 0),
                    trim($_POST['reason'] ?? ''),
                    $repaymentDate,
                    $id
                ]);
                
                echo json_encode(['success' => $result]);
                exit;
            } catch (Exception $e) {
                error_log('Advance edit error: ' . $e->getMessage());
                header('Location: /ergon-site/advances/edit/' . $id . '?error=Update failed');
                exit;
            }
        }
        
        // GET request - show edit form
        try {
            require_once __DIR__ . '/../config/database.php';
            $db = Database::connect();
            
            if ($_SESSION['role'] === 'user') {
                $stmt = $db->prepare("SELECT * FROM advances WHERE id = ? AND user_id = ?");
                $stmt->execute([$id, $_SESSION['user_id']]);
            } else {
                $stmt = $db->prepare("SELECT * FROM advances WHERE id = ?");
                $stmt->execute([$id]);
            }
            
            $advance = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$advance) {
                header('Location: /ergon-site/advances?error=Advance not found');
                exit;
            }
            
            if ($advance['status'] !== 'pending') {
                header('Location: /ergon-site/advances?error=Cannot edit processed advance');
                exit;
            }
            
            $this->view('advances/edit', ['advance' => $advance, 'active_page' => 'advances']);
        } catch (Exception $e) {
            error_log('Advance edit load error: ' . $e->getMessage());
            header('Location: /ergon-site/advances?error=Failed to load advance');
            exit;
        }
    }
    
    public function approve($id = null) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $this->requireAuth();
        
        if (!$id) {
            header('Location: /ergon-site/advances?error=Invalid advance ID');
            exit;
        }
        
        // Check authorization - only admin/owner can approve
        $currentUserRole = $_SESSION['role'] ?? 'user';
        if (!in_array($currentUserRole, ['admin', 'owner'])) {
            header('Location: /ergon-site/advances?error=Unauthorized access');
            exit;
        }
        
        try {
            require_once __DIR__ . '/../config/database.php';
            $db = Database::connect();
            
            // Get advance details first
            $stmt = $db->prepare("SELECT user_id FROM advances WHERE id = ? AND status = 'pending'");
            $stmt->execute([$id]);
            $advance = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$advance) {
                header('Location: /ergon-site/advances?error=Advance not found or already processed');
                exit;
            }
            
            // If admin provided an approved amount, use it
            // If admin provided an approved amount, store it in approved_amount (do not overwrite requested amount)
            $approvedAmount = null;
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['approved_amount'])) {
                $approvedAmount = floatval($_POST['approved_amount']);
                if ($approvedAmount > 0) {
                    try { $db->exec("ALTER TABLE advances ADD COLUMN approved_amount DECIMAL(10,2) NULL"); } catch (Exception $e) {}
                    $stmt = $db->prepare("UPDATE advances SET approved_amount = ? WHERE id = ?");
                    $stmt->execute([$approvedAmount, $id]);
                }
            }

            $stmt = $db->prepare("UPDATE advances SET status = 'approved', approved_by = ?, approved_at = NOW() WHERE id = ? AND status = 'pending'");
            $result = $stmt->execute([$_SESSION['user_id'], $id]);
            
            if ($result && $stmt->rowCount() > 0) {
                // Create notification for user
                try {
                    require_once __DIR__ . '/../helpers/NotificationHelper.php';
                    NotificationHelper::notifyAdvanceStatusChange($id, 'approved', $_SESSION['user_id']);
                } catch (Exception $notifError) {
                    error_log('Advance approval notification error: ' . $notifError->getMessage());
                }
                
                header('Location: /ergon-site/advances?success=Advance approved successfully');
            } else {
                header('Location: /ergon-site/advances?error=Advance not found or already processed');
            }
        } catch (Exception $e) {
            error_log('Advance approve error: ' . $e->getMessage());
            header('Location: /ergon-site/advances?error=Failed to approve advance');
        }
        exit;
    }

    public function markPaid($id = null) {
        $this->requireAuth();
        if (!$id) {
            header('Location: /ergon-site/advances?error=Invalid advance ID');
            exit;
        }

        // Only admin/owner can mark paid
        if (!in_array($_SESSION['role'] ?? 'user', ['admin','owner'])) {
            header('Location: /ergon-site/advances?error=Unauthorized');
            exit;
        }

        try {
            require_once __DIR__ . '/../config/database.php';
            require_once __DIR__ . '/../helpers/LedgerHelper.php';
            $db = Database::connect();

            $stmt = $db->prepare("SELECT * FROM advances WHERE id = ? AND status = 'approved'");
            $stmt->execute([$id]);
            $advance = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$advance) {
                header('Location: /ergon-site/advances?error=Advance not found or not approved');
                exit;
            }

            $proof = null;
            // Server-side validation: require file, limit size to 5MB, allow jpeg/png/pdf
            if (!isset($_FILES['proof']) || $_FILES['proof']['error'] !== 0) {
                header('Location: /ergon-site/advances/view/' . $id . '?error=Proof file is required');
                exit;
            }

            $allowedMime = ['image/jpeg', 'image/png', 'application/pdf'];
            $maxSize = 5 * 1024 * 1024; // 5MB
            $file = $_FILES['proof'];
            if ($file['size'] > $maxSize) {
                header('Location: /ergon-site/advances/view/' . $id . '?error=Proof file exceeds 5MB');
                exit;
            }

            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
            if (!in_array($mime, $allowedMime)) {
                header('Location: /ergon-site/advances/view/' . $id . '?error=Invalid proof file type');
                exit;
            }

            $uploadDir = __DIR__ . '/../../storage/proofs/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
            $uploadPath = $uploadDir . $filename;
            if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
                $proof = $filename;
            } else {
                header('Location: /ergon-site/advances/view/' . $id . '?error=Failed to save proof file');
                exit;
            }

            $stmt = $db->prepare("UPDATE advances SET status = 'paid', payment_proof = ?, paid_by = ?, paid_at = NOW() WHERE id = ?");
            $result = $stmt->execute([$proof, $_SESSION['user_id'], $id]);

            if ($result) {
                // Record ledger credit for the advance amount
                // Use approved_amount if present else original amount
                $ledgerAmount = floatval($advance['amount']);
                try {
                    $stmt2 = $db->prepare("SELECT approved_amount FROM advances WHERE id = ? LIMIT 1");
                    $stmt2->execute([$id]);
                    $row2 = $stmt2->fetch(PDO::FETCH_ASSOC);
                    if ($row2 && $row2['approved_amount']) $ledgerAmount = floatval($row2['approved_amount']);
                } catch (Exception $e) {}
                LedgerHelper::recordEntry($advance['user_id'], 'advance', 'advance', $id, $ledgerAmount, 'credit');
                header('Location: /ergon-site/advances?success=Advance marked as paid');
            } else {
                header('Location: /ergon-site/advances?error=Failed to mark paid');
            }
        } catch (Exception $e) {
            error_log('Advance markPaid error: ' . $e->getMessage());
            header('Location: /ergon-site/advances?error=Failed to mark paid');
        }
        exit;
    }
    
    public function reject($id = null) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $this->requireAuth();
        
        if (!$id) {
            header('Location: /ergon-site/advances?error=Invalid advance ID');
            exit;
        }
        
        // Check authorization - only admin/owner can reject
        $currentUserRole = $_SESSION['role'] ?? 'user';
        if (!in_array($currentUserRole, ['admin', 'owner'])) {
            header('Location: /ergon-site/advances?error=Unauthorized access');
            exit;
        }
        
        try {
            require_once __DIR__ . '/../config/database.php';
            $db = Database::connect();
            
            $reason = $_POST['rejection_reason'] ?? 'Rejected by administrator';
            
            // Get advance details first
            $stmt = $db->prepare("SELECT user_id FROM advances WHERE id = ? AND status = 'pending'");
            $stmt->execute([$id]);
            $advance = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$advance) {
                header('Location: /ergon-site/advances?error=Advance not found or already processed');
                exit;
            }
            
            $stmt = $db->prepare("UPDATE advances SET status = 'rejected', rejection_reason = ?, rejected_by = ?, rejected_at = NOW() WHERE id = ? AND status = 'pending'");
            $result = $stmt->execute([$reason, $_SESSION['user_id'], $id]);
            
            if ($result && $stmt->rowCount() > 0) {
                // Create notification for user
                try {
                    require_once __DIR__ . '/../helpers/NotificationHelper.php';
                    NotificationHelper::notifyAdvanceStatusChange($id, 'rejected', $_SESSION['user_id']);
                } catch (Exception $notifError) {
                    error_log('Advance rejection notification error: ' . $notifError->getMessage());
                }
                
                header('Location: /ergon-site/advances?success=Advance rejected successfully');
            } else {
                header('Location: /ergon-site/advances?error=Advance not found or already processed');
            }
        } catch (Exception $e) {
            error_log('Advance reject error: ' . $e->getMessage());
            header('Location: /ergon-site/advances?error=Failed to reject advance');
        }
        exit;
    }
    
    public function viewAdvance($id) {
        $this->requireAuth();
        
        try {
            require_once __DIR__ . '/../config/database.php';
            $db = Database::connect();
            
            if ($_SESSION['role'] === 'user') {
                $stmt = $db->prepare("SELECT a.*, u.name as user_name, u.role as user_role FROM advances a LEFT JOIN users u ON a.user_id = u.id WHERE a.id = ? AND a.user_id = ?");
                $stmt->execute([$id, $_SESSION['user_id']]);
            } else {
                $stmt = $db->prepare("SELECT a.*, u.name as user_name, u.role as user_role FROM advances a LEFT JOIN users u ON a.user_id = u.id WHERE a.id = ?");
                $stmt->execute([$id]);
            }
            
            $advance = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$advance) {
                header('Location: /ergon-site/advances?error=not_found');
                exit;
            }
            
            $this->view('advances/view', ['advance' => $advance, 'active_page' => 'advances']);
        } catch (Exception $e) {
            error_log('Advance view error: ' . $e->getMessage());
            header('Location: /ergon-site/advances?error=1');
            exit;
        }
    }
    
    public function delete($id) {
        $this->requireAuth();
        
        try {
            require_once __DIR__ . '/../config/database.php';
            $db = Database::connect();
            
            $stmt = $db->prepare("DELETE FROM advances WHERE id = ?");
            $result = $stmt->execute([$id]);
            
            echo json_encode(['success' => $result]);
        } catch (Exception $e) {
            echo json_encode(['success' => false]);
        }
        exit;
    }
}
?>
