<?php
require_once __DIR__ . '/../config/database.php';

class Notification {
    private $db;
    
    public function __construct() {
        $this->db = Database::connect();
        $this->ensureTable();
    }
    
    private function ensureTable() {
        $sql = "CREATE TABLE IF NOT EXISTS notifications (
            id INT AUTO_INCREMENT PRIMARY KEY,
            sender_id INT NOT NULL,
            receiver_id INT NOT NULL,
            type ENUM('info', 'success', 'warning', 'error', 'urgent') DEFAULT 'info',
            category ENUM('task', 'approval', 'system', 'reminder', 'announcement') DEFAULT 'system',
            title VARCHAR(255) NOT NULL,
            message TEXT NOT NULL,
            action_url VARCHAR(500) DEFAULT NULL,
            action_text VARCHAR(100) DEFAULT NULL,
            reference_type VARCHAR(50) DEFAULT NULL,
            reference_id INT DEFAULT NULL,
            metadata JSON DEFAULT NULL,
            priority TINYINT(1) DEFAULT 1,
            is_read BOOLEAN DEFAULT FALSE,
            read_at TIMESTAMP NULL DEFAULT NULL,
            expires_at TIMESTAMP NULL DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            
            INDEX idx_receiver_unread (receiver_id, is_read, created_at),
            INDEX idx_receiver_priority (receiver_id, priority, created_at),
            INDEX idx_category_type (category, type),
            INDEX idx_reference (reference_type, reference_id),
            INDEX idx_expires (expires_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        $this->db->exec($sql);
    }
    
    public function create($data) {
        $stmt = $this->db->prepare("INSERT INTO notifications (sender_id, receiver_id, module_name, action_type, message, link, reference_id, priority, is_read, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0, 'delivered')");
        return $stmt->execute([
            $data['sender_id'],
            $data['receiver_id'],
            $data['reference_type'] ?? 'system',
            $data['category'] ?? 'notification',
            ($data['title'] ?? '') . ': ' . ($data['message'] ?? ''),
            $data['action_url'] ?? null,
            $data['reference_id'] ?? null,
            $data['priority'] ?? 1
        ]);
    }
    
    public function getForUser($userId, $limit = 50) {
        $stmt = $this->db->prepare("
            SELECT n.*, COALESCE(u.name, 'System') as sender_name,
                   n.message as title,
                   n.message,
                   n.module_name,
                   n.action_type
            FROM notifications n 
            LEFT JOIN users u ON n.sender_id = u.id 
            WHERE n.receiver_id = ? AND n.status != 'deleted'
            ORDER BY n.is_read ASC, n.created_at DESC 
            LIMIT ?
        ");
        $stmt->execute([$userId, $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getForDropdown($userId, $limit = 10) {
        $stmt = $this->db->prepare("
            SELECT n.*, COALESCE(u.name, 'System') as sender_name,
                   n.message as title,
                   n.message,
                   n.module_name,
                   n.action_type,
                   n.link as action_url,
                   n.reference_id,
                   n.module_name as reference_type
            FROM notifications n 
            LEFT JOIN users u ON n.sender_id = u.id 
            WHERE n.receiver_id = ? AND n.status != 'deleted'
            ORDER BY n.is_read ASC, n.created_at DESC 
            LIMIT ?
        ");
        $stmt->execute([$userId, $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getUnreadCount($userId) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM notifications WHERE receiver_id = ? AND is_read = 0 AND status != 'deleted'");
        $stmt->execute([$userId]);
        return $stmt->fetchColumn();
    }
    
    public function markAsRead($id, $userId) {
        $stmt = $this->db->prepare("UPDATE notifications SET is_read = TRUE, read_at = CURRENT_TIMESTAMP WHERE id = ? AND receiver_id = ?");
        return $stmt->execute([$id, $userId]);
    }
    
    public function markAllAsRead($userId) {
        $stmt = $this->db->prepare("UPDATE notifications SET is_read = TRUE, read_at = CURRENT_TIMESTAMP WHERE receiver_id = ? AND is_read = FALSE");
        return $stmt->execute([$userId]);
    }
    
    public static function notify($senderId, $receiverId, $title, $message, $options = []) {
        $notification = new self();
        return $notification->create([
            'sender_id' => $senderId,
            'receiver_id' => $receiverId,
            'title' => $title,
            'message' => $message,
            'type' => $options['type'] ?? 'info',
            'category' => $options['category'] ?? 'system',
            'action_url' => $options['action_url'] ?? null,
            'reference_type' => $options['reference_type'] ?? null,
            'reference_id' => $options['reference_id'] ?? null,
            'priority' => $options['priority'] ?? 1
        ]);
    }
    
    public static function notifyOwners($senderId, $title, $message, $options = []) {
        $notification = new self();
        $stmt = $notification->db->prepare("SELECT id FROM users WHERE role = 'owner'");
        $stmt->execute();
        $owners = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($owners as $owner) {
            self::notify($senderId, $owner['id'], $title, $message, $options);
        }
    }
    
    public static function notifyAdmins($senderId, $title, $message, $options = []) {
        $notification = new self();
        $stmt = $notification->db->prepare("SELECT id FROM users WHERE role = 'admin'");
        $stmt->execute();
        $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($admins as $admin) {
            self::notify($senderId, $admin['id'], $title, $message, $options);
        }
    }
    
    public function getByCategory($userId, $category, $limit = 20) {
        $stmt = $this->db->prepare("
            SELECT n.*, u.name as sender_name 
            FROM notifications n 
            JOIN users u ON n.sender_id = u.id 
            WHERE n.receiver_id = ? AND n.category = ?
            ORDER BY n.priority DESC, n.created_at DESC 
            LIMIT ?
        ");
        $stmt->execute([$userId, $category, $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function cleanupExpired() {
        // No expires_at column, so no cleanup needed
        return true;
    }
}
?>
