<?php
session_start();

// kiko - Main entry point for the application
// Define base path for includes
define('BASE_PATH', dirname(__DIR__));
// Define base URL for links
define('BASE_URL', '/WannaBet/public');

// Debug information
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include necessary files
require_once BASE_PATH . '/app/controllers/LandingController.php';
require_once BASE_PATH . '/app/controllers/AuthController.php';
require_once BASE_PATH . '/app/controllers/HomeController.php';
require_once BASE_PATH . '/app/controllers/UserController.php';

// Get the URL path from REQUEST_URI instead of $_GET['url']
$url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
// Remove the base path from the URL
$url = str_replace('/WannaBet/public/', '', $url);
$url = trim($url, '/');

// Debug the URL
error_log("Requested URL: " . $url);

// Route the request
$authController = new AuthController();
$userController = new UserController();
$landingController = new LandingController();
$homeController = new HomeController();

// Handle POST requests for login and register separately
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    switch ($url) {
        case 'login':
            $result = $userController->login();
            if ($result['success']) {
                header('Location: ' . BASE_URL . '/home');
            } else {
                // Let the login page handle the error
                $authController->login();
            }
            break;
        case 'register':
            $result = $userController->register();
            if ($result['success']) {
                header('Location: ' . BASE_URL . '/login');
            } else {
                // Let the register page handle the error
                $authController->register();
            }
            break;
        case 'my_bets':
        case 'friends':
            // These POST requests are handled by their respective views
            require_once BASE_PATH . "/app/views/{$url}.php";
            break;
        default:
            header('HTTP/1.0 404 Not Found');
            echo '404 Not Found';
            break;
    }
    exit;
}

// Check if the URL contains 'bet/' for bet details pages
if (strpos($url, 'bet/') === 0) {
    $betId = substr($url, 4); // Extract the bet ID
    // Include and call the appropriate controller for bet details
    require_once BASE_PATH . '/app/controllers/BetController.php';
    $betController = new BetController();
    // Pass the bet ID to a view or controller method
    require_once BASE_PATH . '/app/views/bet_details.php';
    exit;
}

// Handle GET requests
switch ($url) {
    case '':
        $landingController->index();
        break;
    case 'login':
        error_log("Handling login route");
        $authController->login();
        break;
    case 'register':
        error_log("Handling register route");
        $authController->register();
        break;
    case 'home':
        $homeController->index();
        break;
    case 'logout':
        $authController->logout();
        break;
    case 'my_bets':
        // Include and call the appropriate controller
        require_once BASE_PATH . '/app/controllers/BetController.php';
        $betController = new BetController();
        require_once BASE_PATH . '/app/views/my_bets.php';
        break;
    case 'friends':
        // Include and render the friends page
        require_once BASE_PATH . '/app/views/friends.php';
        break;
    case 'create_bet':
        // Include and render the create bet page
        require_once BASE_PATH . '/app/controllers/BetController.php';
        $betController = new BetController();
        require_once BASE_PATH . '/app/views/create_bet.php';
        break;
    case 'documentation':
        // Include and render the documentation page
        require_once BASE_PATH . '/app/views/documentation.php';
        break;
    default:
        header('HTTP/1.0 404 Not Found');
        echo '404 Not Found';
        break;
} 