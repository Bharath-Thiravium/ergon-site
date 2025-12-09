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

            $stmt = $db->prepare("SELECT * FROM user_ledgers WHERE user_id = ? ORDER BY created_at DESC");
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
}

?>
