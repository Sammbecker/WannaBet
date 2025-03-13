<?php
session_start();
require_once __DIR__ . '/../models/PaymentProcessor.php';

// Set to true for detailed debugging
$debug = true;

// Add detailed error logging
function logDebug($message, $data = null) {
    global $debug;
    if ($debug) {
        $logMsg = "[PAYMENT_VERIFY] $message";
        if ($data !== null) {
            $logMsg .= ": " . json_encode($data);
        }
        error_log($logMsg);
    }
}

logDebug("Starting payment verification");
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

if (!isset($input['reference']) || !isset($input['bet_id'])) {
    http_response_code(400);
    logDebug("Missing required parameters", $input);
    echo json_encode(['success' => false, 'error' => 'Missing required parameters']);
    exit;
}

// Log the verification request
logDebug("Payment verification request", $input);

// If session data is provided in the request, make sure it matches our session
if (isset($input['session_data']) && !empty($input['session_data'])) {
    $sessionData = $input['session_data'];
    logDebug("Session data from request", $sessionData);
    
    if (isset($sessionData['reference']) && $sessionData['reference'] === $input['reference']) {
        // Ensure our session data is up to date
        if (!isset($_SESSION['pending_payment']) || $_SESSION['pending_payment']['reference'] !== $sessionData['reference']) {
            $_SESSION['pending_payment'] = $sessionData;
            logDebug("Updated session with data from request", $_SESSION['pending_payment']);
        } else {
            logDebug("Session already contains matching reference");
        }
    } else {
        logDebug("Reference mismatch: session=" . ($sessionData['reference'] ?? 'none') . ", input=" . $input['reference']);
    }
} else {
    logDebug("No session data provided in request");
}

try {
    // Initialize payment processor
    $paymentProcessor = new PaymentProcessor();
    logDebug("Payment processor initialized");

    // Verify the payment
    $result = $paymentProcessor->verifyPayment($input['reference']);
    logDebug("Verification result", $result);

    if ($result['success']) {
        // Payment verified successfully
        echo json_encode([
            'success' => true,
            'message' => 'Payment verified successfully',
            'data' => $result
        ]);
    } else {
        // Payment verification failed
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => $result['error'] ?? 'Payment verification failed',
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