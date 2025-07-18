<?php
require_once __DIR__ . '/../config/db.php';

class Friendship {
    private $db;

    public function __construct() {
        $this->db = getDB();
    }

    /**
     * Send a friend request
     */
    public function sendFriendRequest($userId, $friendId) {
        // Check if users are already friends or have a pending request
        if ($this->areFriends($userId, $friendId) || $this->hasPendingRequest($userId, $friendId)) {
            return false;
        }
        
        // Prevent sending a request to yourself
        if ($userId == $friendId) {
            return false;
        }

        $sql = "INSERT INTO friendships (user_id, friend_id, status) VALUES (?, ?, 'pending')";
        $stmt = $this->db->prepare($sql);
        
        try {
            return $stmt->execute([$userId, $friendId]);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Check if users are already friends
     */
    public function areFriends($userId, $friendId) {
        $sql = "SELECT COUNT(*) FROM friendships 
                WHERE ((user_id = ? AND friend_id = ?) 
                OR (user_id = ? AND friend_id = ?)) 
                AND status = 'accepted'";
                
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId, $friendId, $friendId, $userId]);
        
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Check if there is a pending friend request
     */
    public function hasPendingRequest($userId, $friendId) {
        $sql = "SELECT COUNT(*) FROM friendships 
                WHERE ((user_id = ? AND friend_id = ?) 
                OR (user_id = ? AND friend_id = ?)) 
                AND status = 'pending'";
                
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId, $friendId, $friendId, $userId]);
        
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Get all friends of a user
     */
    public function getFriends($userId) {
        $sql = "SELECT u.*, u.id as user_id FROM users u
                JOIN friendships f ON (u.id = f.friend_id OR u.id = f.user_id)
                WHERE ((f.user_id = ? OR f.friend_id = ?) 
                AND u.id != ? 
                AND f.status = 'accepted')";
                
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId, $userId, $userId]);
        
        return $stmt->fetchAll();
    }

    /**
     * Get pending friend requests sent to user
     */
    public function getPendingRequestsReceived($userId) {
        $sql = "SELECT u.*, f.id as friendship_id, f.created_at as request_date 
                FROM users u
                JOIN friendships f ON u.id = f.user_id
                WHERE f.friend_id = ? AND f.status = 'pending'";
                
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        
        return $stmt->fetchAll();
    }

    /**
     * Get pending friend requests sent by user
     */
    public function getPendingRequestsSent($userId) {
        $sql = "SELECT u.*, f.id as friendship_id, f.created_at as request_date 
                FROM users u
                JOIN friendships f ON u.id = f.friend_id
                WHERE f.user_id = ? AND f.status = 'pending'";
                
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        
        return $stmt->fetchAll();
    }

    /**
     * Respond to a friend request
     */
    public function respondToRequest($friendshipId, $userId, $status) {
        // Verify that the request is for this user
        $sql = "SELECT COUNT(*) FROM friendships 
                WHERE id = ? AND friend_id = ?";
                
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$friendshipId, $userId]);
        
        if ($stmt->fetchColumn() == 0) {
            return false;
        }
        
        // Update the request status
        $sql = "UPDATE friendships SET status = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        
        try {
            return $stmt->execute([$status, $friendshipId]);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Remove a friend or cancel a request
     */
    public function removeFriend($userId, $friendId) {
        // If friendId is a friendship ID (single ID passed)
        if (is_numeric($friendId) && $friendId > 0 && func_num_args() === 2) {
            // Check if this is a friendship ID
            $sql = "SELECT user_id, friend_id FROM friendships WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$friendId]);
            $friendship = $stmt->fetch();

            if ($friendship) {
                // Make sure the user is either the requester or the recipient
                if ($friendship['user_id'] == $userId || $friendship['friend_id'] == $userId) {
                    $sql = "DELETE FROM friendships WHERE id = ?";
                    $stmt = $this->db->prepare($sql);
                    return $stmt->execute([$friendId]);
                }
                return false;
            }
        }

        // Regular operation - delete friendship between two users
        $sql = "DELETE FROM friendships 
                WHERE (user_id = ? AND friend_id = ?) 
                OR (user_id = ? AND friend_id = ?)";
                
        $stmt = $this->db->prepare($sql);
        
        try {
            return $stmt->execute([$userId, $friendId, $friendId, $userId]);
        } catch (PDOException $e) {
            return false;
        }
    }
}
?> 