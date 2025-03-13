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
                $sql = "INSERT INTO Notifications (friendship_id, user_id, type, message) 
                        VALUES (?, ?, ?, ?)";
                $params = [
                    $data['friendship_id'],
                    $data['user_id'],
                    $data['type'],
                    $data['message']
                ];
            } else {
                $sql = "INSERT INTO Notifications (bet_id, user_id, type, message) 
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
                    CASE 
                        WHEN n.type = 'bet_invitation' THEN b.description
                        ELSE n.message
                    END as notification_text,
                    b.stake_type,
                    CASE 
                        WHEN b.stake_type = 'money' THEN CONCAT('$', b.stake_amount)
                        WHEN b.stake_type = 'favor' THEN b.stake_description
                        ELSE NULL
                    END as stake_display,
                    u.username as sender_username,
                    b.deadline,
                    b.description,
                    b.stake_amount,
                    b.stake_description
                FROM Notifications n
                LEFT JOIN Bets b ON n.bet_id = b.bet_id
                LEFT JOIN Users u ON b.user_id = u.user_id
                WHERE n.user_id = ? AND n.is_read = 0
                AND n.type = 'bet_invitation'
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
                    CASE 
                        WHEN n.type = 'bet_invitation' THEN b.description
                        ELSE n.message
                    END as notification_text,
                    b.stake_type,
                    CASE 
                        WHEN b.stake_type = 'money' THEN CONCAT('$', b.stake_amount)
                        WHEN b.stake_type = 'favor' THEN b.stake_description
                        ELSE NULL
                    END as stake_display,
                    creator.username as creator_username,
                    b.deadline,
                    b.description,
                    b.stake_amount,
                    b.stake_description
                FROM Notifications n
                LEFT JOIN Bets b ON n.bet_id = b.bet_id
                LEFT JOIN Users creator ON b.user_id = creator.user_id
                WHERE n.user_id = ? AND n.status = 'pending'
                AND n.type = 'bet_invitation'
                ORDER BY n.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        
        return $stmt->fetchAll();
    }

    /**
     * Get a notification by ID
     */
    public function getNotificationById($notificationId) {
        $sql = "SELECT * FROM Notifications WHERE notification_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$notificationId]);
        
        return $stmt->fetch();
    }

    /**
     * Mark a notification as read
     */
    public function markAsRead($notificationId) {
        $sql = "UPDATE Notifications SET is_read = 1 WHERE notification_id = ?";
        $stmt = $this->db->prepare($sql);
        
        try {
            return $stmt->execute([$notificationId]);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Update notification status
     */
    public function updateStatus($notificationId, $status) {
        $sql = "UPDATE Notifications SET status = ? WHERE notification_id = ?";
        $stmt = $this->db->prepare($sql);
        
        try {
            return $stmt->execute([$status, $notificationId]);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Get unread notification count for a user
     */
    public function getUnreadCount($userId) {
        $sql = "SELECT COUNT(*) as count FROM Notifications WHERE user_id = ? AND is_read = 0";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        
        $result = $stmt->fetch();
        return $result['count'];
    }
}
?> 