<?php
require_once __DIR__ . '/../config/db.php';

class Notification {
    private $db;

    public function __construct() {
        $this->db = getDB();
    }

    /**
     * Create a new notification
     */
    public function createNotification($data) {
        try {
            // Determine which fields to include based on notification type
            if ($data['type'] === 'friend_request' || $data['type'] === 'friend_response') {
                $sql = "INSERT INTO notifications (friendship_id, user_id, type, message) 
                        VALUES (?, ?, ?, ?)";
                $params = [
                    $data['friendship_id'],
                    $data['user_id'],
                    $data['type'],
                    $data['message']
                ];
            } else {
                $sql = "INSERT INTO notifications (bet_id, user_id, type, message) 
                        VALUES (?, ?, ?, ?)";
                $params = [
                    $data['bet_id'],
                    $data['user_id'],
                    $data['type'],
                    $data['message']
                ];
            }

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log("Error creating notification: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all notifications for a user
     */
    public function getNotificationsForUser($userId) {
        $sql = "SELECT n.*, 
                b.title as bet_title,
                u.username as sender_username
                FROM notifications n
                LEFT JOIN bets b ON n.bet_id = b.id
                LEFT JOIN users u ON n.user_id = u.id
                WHERE n.user_id = ?
                ORDER BY n.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    /**
     * Get pending notifications for a user
     */
    public function getPendingNotificationsForUser($userId) {
        $sql = "SELECT n.*, 
                b.title as bet_title,
                u.username as sender_username
                FROM notifications n
                LEFT JOIN bets b ON n.bet_id = b.id
                LEFT JOIN users u ON n.user_id = u.id
                WHERE n.user_id = ? AND n.is_read = FALSE
                ORDER BY n.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    /**
     * Get notification by ID
     */
    public function getNotificationById($notificationId) {
        $sql = "SELECT n.*, 
                b.title as bet_title,
                u.username as sender_username
                FROM notifications n
                LEFT JOIN bets b ON n.bet_id = b.id
                LEFT JOIN users u ON n.user_id = u.id
                WHERE n.id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$notificationId]);
        return $stmt->fetch();
    }

    /**
     * Mark notification as read
     */
    public function markAsRead($notificationId) {
        $sql = "UPDATE notifications SET is_read = TRUE WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$notificationId]);
    }

    /**
     * Update notification status
     */
    public function updateStatus($notificationId, $status) {
        $sql = "UPDATE notifications SET status = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$status, $notificationId]);
    }

    /**
     * Get count of unread notifications
     */
    public function getUnreadCount($userId) {
        $sql = "SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = FALSE";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        $result = $stmt->fetch();
        return $result['count'];
    }
}
?> 