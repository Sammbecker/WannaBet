<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../controllers/BetController.php';
require_once __DIR__ . '/../utils/functions.php';

// Handle CORS if needed
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With');

// Instantiate the bet controller
$betController = new BetController();

// Get the request method
$method = $_SERVER['REQUEST_METHOD'];

// Get the endpoint from the URL
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode('/', $uri);
$endpoint = $uri[count($uri) - 1] ?? '';

// Route the request to the appropriate controller method
switch ($method) {
    case 'GET':
        if ($endpoint === 'user_bets') {
            // Get all bets for the current user
            $result = $betController->getUserBets();
            jsonResponse($result);
        } elseif ($endpoint === 'bet' && isset($_GET['id'])) {
            // Get a bet by ID
            $betId = intval($_GET['id']);
            $result = $betController->getBet($betId);
            jsonResponse($result);
        } elseif ($endpoint === 'notifications') {
            // Get all notifications for the current user
            $result = $betController->getUserNotifications();
            jsonResponse($result);
        } elseif ($endpoint === 'pending_notifications') {
            // Get pending notifications for the current user
            $result = $betController->getPendingNotifications();
            jsonResponse($result);
        } elseif ($endpoint === 'friends_for_bet') {
            // Get friends for creating a bet
            $result = $betController->getFriendsForBet();
            jsonResponse($result);
        } else {
            // Invalid endpoint
            jsonResponse(['success' => false, 'errors' => ['Invalid endpoint']], 404);
        }
        break;
        
    case 'POST':
        if ($endpoint === 'create_bet') {
            // Create a new bet
            $result = $betController->createBet();
            jsonResponse($result);
        } elseif ($endpoint === 'respond_bet') {
            // Respond to a bet invitation
            $result = $betController->respondToBet();
            jsonResponse($result);
        } elseif ($endpoint === 'complete_bet') {
            // Complete a bet
            $result = $betController->completeBet();
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