<?php
require_once __DIR__ . '/../core/Controller.php';

class LedgerController extends Controller {
    public function userLedger($id = null) {
        $this->requireAuth();
        if (!$id || !is_numeric($id)) {
            header('Location: /ergon-site/users?error=invalid_user');
            exit;
        }

        try {
            require_once __DIR__ . '/../helpers/LedgerHelper.php';
            require_once __DIR__ . '/../config/database.php';
            $db = Database::connect();
            LedgerHelper::ensureTable();

            $stmt = $db->prepare("
                SELECT 
                    ul.*,
                    COALESCE(e.description, a.reason, 'N/A') as description
                FROM user_ledgers ul
                LEFT JOIN expenses e ON ul.reference_type = 'expense' AND ul.reference_id = e.id
                LEFT JOIN advances a ON ul.reference_type = 'advance' AND ul.reference_id = a.id
                WHERE ul.user_id = ? 
                ORDER BY ul.created_at DESC
            ");
            $stmt->execute([$id]);
            $entries = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $balance = LedgerHelper::getUserBalance($id);

            $this->view('ledgers/user', ['entries' => $entries, 'balance' => $balance, 'user_id' => $id, 'active_page' => 'ledgers']);
        } catch (Exception $e) {
            error_log('Ledger view error: ' . $e->getMessage());
            header('Location: /ergon-site/users?error=ledger_failed');
            exit;
        }
    }

    public function projectLedger() {
        $this->requireAuth();
        
        error_log('Project Ledger - User role: ' . ($_SESSION['role'] ?? 'none'));
        
        if (!in_array($_SESSION['role'] ?? '', ['owner', 'admin'])) {
            error_log('Project Ledger - Access denied for role: ' . ($_SESSION['role'] ?? 'none'));
            header('Location: /ergon-site/dashboard');
            exit;
        }

        try {
            require_once __DIR__ . '/../config/database.php';
            $db = Database::connect();
            error_log('Project Ledger - Database connected');

            $stmt = $db->query("SELECT id, name as project_name FROM projects ORDER BY name");
            $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
            error_log('Project Ledger - Projects fetched: ' . count($projects));

            $data = ['projects' => $projects, 'active_page' => 'ledgers'];

            if (isset($_GET['project_id']) && is_numeric($_GET['project_id'])) {
                $project_id = $_GET['project_id'];

                $stmt = $db->prepare("SELECT name as project_name FROM projects WHERE id = ?");
                $stmt->execute([$project_id]);
                $project = $stmt->fetch(PDO::FETCH_ASSOC);

                $stmt = $db->prepare("
                    SELECT 'expense' as type, e.id, e.user_id, u.name as user_name, e.description, e.amount, e.status, e.created_at
                    FROM expenses e
                    JOIN users u ON e.user_id = u.id
                    WHERE e.project_id = ? AND e.status IN ('approved', 'paid')
                    UNION ALL
                    SELECT 'advance' as type, a.id, a.user_id, u.name as user_name, a.reason as description, a.amount, a.status, a.created_at
                    FROM advances a
                    JOIN users u ON a.user_id = u.id
                    WHERE a.project_id = ? AND a.status IN ('approved', 'paid')
                    ORDER BY created_at DESC
                ");
                $stmt->execute([$project_id, $project_id]);
                $entries = $stmt->fetchAll(PDO::FETCH_ASSOC);

                $total_expenses = array_sum(array_column(array_filter($entries, fn($e) => $e['type'] === 'expense'), 'amount'));
                $total_advances = array_sum(array_column(array_filter($entries, fn($e) => $e['type'] === 'advance'), 'amount'));

                $data['project_id'] = $project_id;
                $data['project_name'] = $project['project_name'] ?? 'Unknown';
                $data['entries'] = $entries;
                $data['total_expenses'] = $total_expenses;
                $data['total_advances'] = $total_advances;
            }

            $this->view('ledgers/project', $data);
        } catch (Exception $e) {
            error_log('Project ledger error: ' . $e->getMessage());
            header('Location: /ergon-site/dashboard?error=ledger_failed');
            exit;
        }
    }
}

?>
