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

        $sql = "INSERT INTO Friendships (user_id, friend_id) VALUES (?, ?)";
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
        $sql = "SELECT COUNT(*) FROM Friendships 
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
        $sql = "SELECT COUNT(*) FROM Friendships 
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
        $sql = "SELECT u.* FROM Users u
                JOIN Friendships f ON (u.user_id = f.friend_id OR u.user_id = f.user_id)
                WHERE ((f.user_id = ? OR f.friend_id = ?) 
                AND u.user_id != ? 
                AND f.status = 'accepted')";
                
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId, $userId, $userId]);
        
        return $stmt->fetchAll();
    }

    /**
     * Get pending friend requests sent to user
     */
    public function getPendingRequestsReceived($userId) {
        $sql = "SELECT u.*, f.friendship_id, f.created_at as request_date 
                FROM Users u
                JOIN Friendships f ON u.user_id = f.user_id
                WHERE f.friend_id = ? AND f.status = 'pending'";
                
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        
        return $stmt->fetchAll();
    }

    /**
     * Get pending friend requests sent by user
     */
    public function getPendingRequestsSent($userId) {
        $sql = "SELECT u.*, f.friendship_id, f.created_at as request_date 
                FROM Users u
                JOIN Friendships f ON u.user_id = f.friend_id
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
        $sql = "SELECT COUNT(*) FROM Friendships 
                WHERE friendship_id = ? AND friend_id = ?";
                
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$friendshipId, $userId]);
        
        if ($stmt->fetchColumn() == 0) {
            return false;
        }
        
        // Update the request status
        $sql = "UPDATE Friendships SET status = ? WHERE friendship_id = ?";
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
        $sql = "DELETE FROM Friendships 
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