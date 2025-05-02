<?php
require_once __DIR__ . '/../models/User.php';

class UserController {
    private $userModel;
    
    public function __construct() {
        $this->userModel = new User();
    }
    
    /**
     * Register a new user
     */
    public function register() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'] ?? '';
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            // Validate input
            if (empty($username) || empty($email) || empty($password) || empty($confirmPassword)) {
                return ['success' => false, 'message' => 'All fields are required'];
            }

            if ($password !== $confirmPassword) {
                return ['success' => false, 'message' => 'Passwords do not match'];
            }

            // Register user
            $result = $this->userModel->register($username, $email, $password);
            
            if ($result['success']) {
                // Set success message in session
                $_SESSION['registration_success'] = 'Registration successful! Please log in.';
                return ['success' => true];
            } else {
                return ['success' => false, 'message' => $result['message']];
            }
        }
        return ['success' => false, 'message' => 'Invalid request method'];
    }
    
    /**
     * Login a user
     */
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            // Validate input
            if (empty($email) || empty($password)) {
                return ['success' => false, 'message' => 'Email and password are required'];
            }

            // Attempt login
            $result = $this->userModel->login($email, $password);
            
            if (isset($result['success']) && $result['success'] === true) {
                if (isset($result['user']) && isset($result['user']['id']) && isset($result['user']['username'])) {
                    $_SESSION['user_id'] = $result['user']['id'];
                    $_SESSION['username'] = $result['user']['username'];
                    return ['success' => true];
                } else {
                    return ['success' => false, 'message' => 'Invalid user data received'];
                }
            } else {
                return ['success' => false, 'message' => $result['message'] ?? 'Invalid email or password'];
            }
        }
        return ['success' => false, 'message' => 'Invalid request method'];
    }
    
    /**
     * Logout a user
     */
    public function logout() {
        session_destroy();
        header('Location: /');
        exit;
    }
    
    /**
     * Get user profile
     */
    public function getProfile($userId) {
        return $this->userModel->getUserById($userId);
    }
    
    /**
     * Update user profile
     */
    public function updateProfile() {
        // Process profile update form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = $_SESSION['user_id'] ?? 0;
            
            if (!$userId) {
                return ['success' => false, 'errors' => ['User not logged in']];
            }
            
            $data = [];
            
            // Only update fields that are provided
            if (isset($_POST['email'])) {
                $data['email'] = trim($_POST['email']);
            }
            
            // Update password if provided
            if (!empty($_POST['new_password'])) {
                $currentPassword = $_POST['current_password'] ?? '';
                $newPassword = $_POST['new_password'] ?? '';
                $confirmPassword = $_POST['confirm_password'] ?? '';
                
                // Verify current password
                $user = $this->userModel->getUserById($userId);
                
                if (!password_verify($currentPassword, $user['password'])) {
                    return ['success' => false, 'errors' => ['Current password is incorrect']];
                }
                
                // Validate new password
                if (strlen($newPassword) < 6) {
                    return ['success' => false, 'errors' => ['Password must be at least 6 characters long']];
                }
                
                if ($newPassword !== $confirmPassword) {
                    return ['success' => false, 'errors' => ['Passwords do not match']];
                }
                
                // Update password
                $this->userModel->updatePassword($userId, $newPassword);
            }
            
            // Update other profile data
            if (!empty($data)) {
                $result = $this->userModel->updateProfile($userId, $data);
                
                if ($result) {
                    return ['success' => true, 'message' => 'Profile updated successfully'];
                } else {
                    return ['success' => false, 'errors' => ['Failed to update profile']];
                }
            }
            
            return ['success' => true, 'message' => 'No changes made to profile'];
        }
        
        // Default: return the current profile
        $userId = $_SESSION['user_id'] ?? 0;
        
        if (!$userId) {
            return ['success' => false, 'errors' => ['User not logged in']];
        }
        
        $profile = $this->userModel->getUserById($userId);
        
        return ['success' => true, 'profile' => $profile];
    }
    
    /**
     * Get all users
     */
    public function getAllUsers() {
        return $this->userModel->getAllUsers();
    }
}
?>
