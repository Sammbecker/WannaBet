<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../controllers/UserController.php';
require_once __DIR__ . '/../utils/functions.php';

// Handle CORS if needed
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With');

// Instantiate the user controller
$userController = new UserController();

// Get the request method
$method = $_SERVER['REQUEST_METHOD'];

// Get the endpoint from the URL
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode('/', $uri);
$endpoint = $uri[count($uri) - 1] ?? '';

// Route the request to the appropriate controller method
switch ($method) {
    case 'GET':
        if ($endpoint === 'profile') {
            // Get the current user's profile
            $result = $userController->updateProfile();
            jsonResponse($result);
        } elseif ($endpoint === 'all_users') {
            // Get all users (for opponent selection)
            if (!isLoggedIn()) {
                jsonResponse(['success' => false, 'errors' => ['You must be logged in to view users']], 401);
            }
            
            $users = $userController->getAllUsers();
            
            // Filter out the current user
            $filteredUsers = array_filter($users, function($user) {
                return $user['user_id'] != $_SESSION['user_id'];
            });
            
            jsonResponse(['success' => true, 'users' => array_values($filteredUsers)]);
        } else {
            // Invalid endpoint
            jsonResponse(['success' => false, 'errors' => ['Invalid endpoint']], 404);
        }
        break;
        
    case 'POST':
        if ($endpoint === 'register') {
            // Register a new user
            $result = $userController->register();
            jsonResponse($result);
        } elseif ($endpoint === 'login') {
            // Login a user
            $result = $userController->login();
            jsonResponse($result);
        } elseif ($endpoint === 'logout') {
            // Logout a user
            $userController->logout();
            jsonResponse(['success' => true, 'message' => 'Logged out successfully']);
        } elseif ($endpoint === 'update_profile') {
            // Update the current user's profile
            $result = $userController->updateProfile();
            jsonResponse($result);
        } else {
            // Invalid endpoint
            jsonResponse(['success' => false, 'errors' => ['Invalid endpoint']], 404);
        }
        break;
        
    default:
        // Method not allowed
        jsonResponse(['success' => false, 'errors' => ['Method not allowed']], 405);
        break;
}
?> 