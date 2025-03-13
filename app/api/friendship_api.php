<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../controllers/FriendshipController.php';
require_once __DIR__ . '/../utils/functions.php';

// Handle CORS if needed
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With');

// Instantiate the friendship controller
$friendshipController = new FriendshipController();

// Get the request method
$method = $_SERVER['REQUEST_METHOD'];

// Get the endpoint from the URL
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode('/', $uri);
$endpoint = $uri[count($uri) - 1] ?? '';

// Route the request to the appropriate controller method
switch ($method) {
    case 'GET':
        if ($endpoint === 'friends') {
            // Get all friends
            $result = $friendshipController->getFriends();
            jsonResponse($result);
        } elseif ($endpoint === 'potential_friends') {
            // Get users who are not friends
            $result = $friendshipController->getPotentialFriends();
            jsonResponse($result);
        } elseif ($endpoint === 'friend_requests') {
            // Get pending friend requests
            $result = $friendshipController->getPendingRequests();
            jsonResponse($result);
        } else {
            // Invalid endpoint
            jsonResponse(['success' => false, 'errors' => ['Invalid endpoint']], 404);
        }
        break;
        
    case 'POST':
        if ($endpoint === 'send_request') {
            // Send a friend request
            $result = $friendshipController->sendRequest();
            jsonResponse($result);
        } elseif ($endpoint === 'respond_request') {
            // Respond to a friend request
            $result = $friendshipController->respondToRequest();
            jsonResponse($result);
        } elseif ($endpoint === 'remove_friend') {
            // Remove a friend
            $result = $friendshipController->removeFriend();
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