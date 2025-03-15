<?php
session_start();
require_once __DIR__ . '/../models/PaymentProcessor.php';

// Set to true for detailed debugging
$debug = true;

// Add detailed error logging
function logDebug($message, $data = null) {
    global $debug;
    if ($debug) {
        $logMsg = "[PAYMENT_INIT] $message";
        if ($data !== null) {
            $logMsg .= ": " . json_encode($data);
        }
        error_log($logMsg);
    }
}

logDebug("Starting payment initialization");
logDebug("Session data", $_SESSION);

header('Content-Type: application/json');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    logDebug("Invalid request method: " . $_SERVER['REQUEST_METHOD']);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// Get JSON input
$rawInput = file_get_contents('php://input');
logDebug("Raw input received", $rawInput);
$input = json_decode($rawInput, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    logDebug("JSON parse error: " . json_last_error_msg());
    echo json_encode(['success' => false, 'error' => 'Invalid JSON: ' . json_last_error_msg()]);
    exit;
}

// Check for required fields
if (!isset($input['bet_id']) || !isset($input['amount'])) {
    http_response_code(400);
    logDebug("Missing required parameters", $input);
    echo json_encode(['success' => false, 'error' => 'Missing required parameters: bet_id and amount are required']);
    exit;
}

// Get the bet ID and amount
$betId = $input['bet_id'];
$amount = floatval($input['amount']);

// Validate amount
if ($amount <= 0) {
    http_response_code(400);
    logDebug("Invalid amount: $amount");
    echo json_encode(['success' => false, 'error' => 'Invalid amount']);
    exit;
}

try {
    // Initialize payment processor
    $paymentProcessor = new PaymentProcessor();
    logDebug("Payment processor initialized");

    // Create payment intent
    $userId = $_SESSION['user_id'] ?? null;
    $result = $paymentProcessor->createPaymentIntent($amount, $betId, $userId);
    logDebug("Payment intent result", $result);

    if ($result['success']) {
        // Payment intent created successfully
        echo json_encode([
            'success' => true,
            'message' => 'Payment initialized successfully',
            'checkout_url' => $result['checkout_url'],
            'reference' => $result['reference']
        ]);
    } else {
        // Payment intent creation failed
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => $result['error'] ?? 'Failed to initialize payment',
            'details' => $result
        ]);
    }
} catch (Exception $e) {
    // Catch any exceptions
    logDebug("Exception caught", [
        'message' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Server error: ' . $e->getMessage()
    ]);
} 