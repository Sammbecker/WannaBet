<?php
session_start();
require_once __DIR__ . '/../models/PaymentProcessor.php';

// Set header to JSON
header('Content-Type: application/json');

// Get current session data
$session_data = $_SESSION;

// Mask sensitive data for security
if (isset($session_data['user_id'])) {
    // Keep user_id visible but mask other potentially sensitive data
    if (isset($session_data['email'])) {
        $session_data['email'] = substr($session_data['email'], 0, 3) . '***' . strstr($session_data['email'], '@');
    }
}

// Get payment reference from query parameter or session
$reference = $_GET['reference'] ?? ($_SESSION['pending_payment']['reference'] ?? null);

// Check if we should attempt verification
$verify = isset($_GET['verify']) && $_GET['verify'] === 'true';
$verification_result = null;

if ($verify && $reference) {
    try {
        $paymentProcessor = new PaymentProcessor();
        $verification_result = $paymentProcessor->verifyPayment($reference);
    } catch (Exception $e) {
        $verification_result = [
            'success' => false,
            'error' => $e->getMessage(),
            'exception' => get_class($e)
        ];
    }
}

// Get server environment information
$server_info = [
    'php_version' => PHP_VERSION,
    'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'unknown',
    'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? 'unknown',
    'script_filename' => $_SERVER['SCRIPT_FILENAME'] ?? 'unknown',
    'request_uri' => $_SERVER['REQUEST_URI'] ?? 'unknown'
];

// Create response
$response = [
    'time' => date('Y-m-d H:i:s'),
    'session_data' => $session_data,
    'get_params' => $_GET,
    'post_params' => $_POST,
    'server_info' => $server_info
];

if ($verification_result) {
    $response['verification_result'] = $verification_result;
}

// Output JSON
echo json_encode($response, JSON_PRETTY_PRINT); 