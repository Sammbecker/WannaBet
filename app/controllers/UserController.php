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
        // Process registration form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            
            $errors = [];
            
            // Validate input
            if (empty($username)) {
                $errors[] = "Username is required";
            }
            
            if (empty($email)) {
                $errors[] = "Email is required";
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = "Invalid email format";
            }
            
            if (empty($password)) {
                $errors[] = "Password is required";
            } elseif (strlen($password) < 6) {
                $errors[] = "Password must be at least 6 characters long";
            }
            
            if ($password !== $confirmPassword) {
                $errors[] = "Passwords do not match";
            }
            
            // If no errors, try to register the user
            if (empty($errors)) {
                $userId = $this->userModel->register($username, $email, $password);
                
                if ($userId) {
                    // Set session data
                    $_SESSION['user_id'] = $userId;
                    $_SESSION['username'] = $username;
                    
                    // Redirect to home page
                    header('Location: home.php');
                    exit();
                } else {
                    $errors[] = "Username or email already exists";
                }
            }
            
            // If errors, return them
            return ['success' => false, 'errors' => $errors];
        }
        
        // Default: return the registration form
        return ['success' => false, 'errors' => []];
    }
    
    /**
     * Login a user
     */
    public function login() {
        // Process login form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            
            $errors = [];
            
            // Validate input
            if (empty($email)) {
                $errors[] = "Email is required";
            }
            
            if (empty($password)) {
                $errors[] = "Password is required";
            }
            
            // If no errors, try to login
            if (empty($errors)) {
                $user = $this->userModel->login($email, $password);
                
                if ($user) {
                    // Set session data
                    $_SESSION['user_id'] = $user['user_id'];
                    $_SESSION['username'] = $user['username'];
                    
                    // Redirect to home page
                    header('Location: home.php');
                    exit();
                } else {
                    $errors[] = "Invalid email or password";
                }
            }
            
            // If errors, return them
            return ['success' => false, 'errors' => $errors];
        }
        
        // Default: return the login form
        return ['success' => false, 'errors' => []];
    }
    
    /**
     * Logout a user
     */
    public function logout() {
        // Unset all session variables
        $_SESSION = [];
        
        // Destroy the session
        session_destroy();
        
        // Redirect to login page
        header('Location: login.php');
        exit();
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
