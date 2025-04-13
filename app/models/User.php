<?php
require_once __DIR__ . '/../config/db.php';

class User {
    private $db;

    public function __construct() {
        $this->db = getDB();
    }

    /**
     * Register a new user
     */
    public function register($username, $email, $password) {
        // Check if username or email already exists
        if ($this->userExists($username, $email)) {
            return ['success' => false, 'message' => 'Username or email already exists'];
        }
        
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        
        $sql = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        
        try {
            $stmt->execute([$username, $email, $hashedPassword]);
            $userId = $this->db->lastInsertId();
            return [
                'success' => true,
                'user' => [
                    'id' => $userId,
                    'username' => $username,
                    'email' => $email
                ]
            ];
        } catch (PDOException $e) {
            error_log("Registration error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Registration failed. Please try again.'];
        }
    }

    /**
     * Check if username or email already exists
     */
    private function userExists($username, $email) {
        $sql = "SELECT COUNT(*) FROM users WHERE username = ? OR email = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$username, $email]);
        
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Login a user using email
     */
    public function login($email, $password) {
        $sql = "SELECT id, username, email, password FROM users WHERE email = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            return [
                'success' => true,
                'user' => [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'email' => $user['email']
                ]
            ];
        }
        
        return ['success' => false, 'message' => 'Invalid email or password'];
    }

    /**
     * Get user by ID
     */
    public function getUserById($userId) {
        $sql = "SELECT id, username, email, created_at FROM users WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        
        return $stmt->fetch();
    }

    /**
     * Get all users
     */
    public function getAllUsers() {
        $sql = "SELECT id, username, email, created_at FROM users";
        $stmt = $this->db->query($sql);
        
        return $stmt->fetchAll();
    }

    /**
     * Update user profile
     */
    public function updateProfile($userId, $data) {
        $updateFields = [];
        $params = [];
        
        foreach ($data as $key => $value) {
            if ($key !== 'id' && $key !== 'password') {
                $updateFields[] = "$key = ?";
                $params[] = $value;
            }
        }
        
        if (empty($updateFields)) {
            return false;
        }
        
        $params[] = $userId;
        $sql = "UPDATE users SET " . implode(', ', $updateFields) . " WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        
        try {
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("Profile update error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update user password
     */
    public function updatePassword($userId, $newPassword) {
        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
        
        $sql = "UPDATE users SET password = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        
        try {
            return $stmt->execute([$hashedPassword, $userId]);
        } catch (PDOException $e) {
            error_log("Password update error: " . $e->getMessage());
            return false;
        }
    }
}
?>
