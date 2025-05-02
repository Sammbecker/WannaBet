<?php
require_once __DIR__ . '/../models/Friendship.php';
require_once __DIR__ . '/../models/User.php';

class FriendshipController {
    private $friendshipModel;
    private $userModel;
    
    public function __construct() {
        $this->friendshipModel = new Friendship();
        $this->userModel = new User();
    }
    
    /**
     * Send a friend request
     */
    public function sendRequest() {
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            return ['success' => false, 'errors' => ['You must be logged in to send friend requests']];
        }
        
        // Process friend request form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = $_SESSION['user_id'];
            $friendId = intval($_POST['recipient_id'] ?? $_POST['friend_id'] ?? 0);
            
            if ($friendId <= 0) {
                return ['success' => false, 'errors' => ['Invalid friend selection']];
            }
            
            // Check if trying to add yourself
            if ($userId == $friendId) {
                return ['success' => false, 'errors' => ['You cannot send a friend request to yourself']];
            }
            
            // Check if already friends
            if ($this->friendshipModel->areFriends($userId, $friendId)) {
                return ['success' => false, 'errors' => ['You are already friends with this user']];
            }
            
            // Check if request already pending
            if ($this->friendshipModel->hasPendingRequest($userId, $friendId)) {
                return ['success' => false, 'errors' => ['A friend request is already pending with this user']];
            }
            
            // Send the friend request
            $result = $this->friendshipModel->sendFriendRequest($userId, $friendId);
            
            if ($result) {
                return ['success' => true, 'message' => 'Friend request sent successfully'];
            } else {
                return ['success' => false, 'errors' => ['Failed to send friend request']];
            }
        }
        
        // Default: return the form
        return ['success' => false, 'errors' => []];
    }
    
    /**
     * Get users who are not friends
     */
    public function getPotentialFriends() {
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            return ['success' => false, 'errors' => ['You must be logged in to view users']];
        }
        
        $userId = $_SESSION['user_id'];
        
        // Get all users
        $allUsers = $this->userModel->getAllUsers();
        
        // Get current friends
        $friends = $this->friendshipModel->getFriends($userId);
        $friendIds = array_column($friends, 'id');
        
        // Filter out current user and friends
        $potentialFriends = array_filter($allUsers, function($user) use ($userId, $friendIds) {
            return $user['id'] != $userId && !in_array($user['id'], $friendIds);
        });
        
        return ['success' => true, 'users' => array_values($potentialFriends)];
    }
    
    /**
     * Get all friends of the current user
     */
    public function getFriends() {
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            return ['success' => false, 'errors' => ['You must be logged in to view friends']];
        }
        
        $userId = $_SESSION['user_id'];
        
        // Get friends
        $friends = $this->friendshipModel->getFriends($userId);
        
        return ['success' => true, 'friends' => $friends];
    }
    
    /**
     * Get pending friend requests for the current user
     */
    public function getPendingRequests() {
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            return ['success' => false, 'errors' => ['You must be logged in to view friend requests']];
        }
        
        $userId = $_SESSION['user_id'];
        
        // Get pending requests received
        $receivedRequests = $this->friendshipModel->getPendingRequestsReceived($userId);
        
        // Get pending requests sent
        $sentRequests = $this->friendshipModel->getPendingRequestsSent($userId);
        
        return [
            'success' => true, 
            'received_requests' => $receivedRequests,
            'sent_requests' => $sentRequests
        ];
    }
    
    /**
     * Respond to a friend request
     */
    public function respondToRequest() {
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            return ['success' => false, 'errors' => ['You must be logged in to respond to friend requests']];
        }
        
        // Process form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = $_SESSION['user_id'];
            // Accept either request_id or friendship_id from the form
            $friendshipId = intval($_POST['request_id'] ?? $_POST['friendship_id'] ?? 0);
            $response = $_POST['response'] ?? '';
            
            if ($friendshipId <= 0) {
                return ['success' => false, 'errors' => ['Invalid friend request']];
            }
            
            // Map response values to database status values
            if ($response === 'accept') {
                $status = 'accepted';
            } elseif ($response === 'reject' || $response === 'cancel') {
                // Delete the friendship completely on rejection
                $result = $this->friendshipModel->removeFriend($userId, $friendshipId);
                if ($result) {
                    return ['success' => true, 'message' => 'Friend request ' . ($response === 'cancel' ? 'cancelled' : 'rejected') . ' successfully'];
                } else {
                    return ['success' => false, 'errors' => ['Failed to ' . ($response === 'cancel' ? 'cancel' : 'reject') . ' friend request']];
                }
            } else {
                return ['success' => false, 'errors' => ['Invalid response']];
            }
            
            // Update the request status
            $result = $this->friendshipModel->respondToRequest($friendshipId, $userId, $status);
            
            if ($result) {
                return ['success' => true, 'message' => 'Friend request accepted successfully'];
            } else {
                return ['success' => false, 'errors' => ['Failed to respond to friend request']];
            }
        }
        
        // Default: return an error
        return ['success' => false, 'errors' => ['Invalid request']];
    }
    
    /**
     * Remove a friend or cancel a request
     */
    public function removeFriend() {
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            return ['success' => false, 'errors' => ['You must be logged in to remove friends']];
        }
        
        // Process form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = $_SESSION['user_id'];
            $friendId = intval($_POST['friend_id'] ?? 0);
            
            if ($friendId <= 0) {
                return ['success' => false, 'errors' => ['Invalid friend selection']];
            }
            
            // Remove the friend
            $result = $this->friendshipModel->removeFriend($userId, $friendId);
            
            if ($result) {
                return ['success' => true, 'message' => 'Friend removed successfully'];
            } else {
                return ['success' => false, 'errors' => ['Failed to remove friend']];
            }
        }
        
        // Default: return an error
        return ['success' => false, 'errors' => ['Invalid request']];
    }
}
?> 