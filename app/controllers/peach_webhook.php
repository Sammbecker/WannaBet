<?php
require_once __DIR__ . '/../models/PaymentProcessor.php';

// Set to true for detailed debugging
$debug = true;

// Add detailed error logging
function logWebhook($message, $data = null) {
    global $debug;
    $logMsg = "[PEACH_WEBHOOK] $message";
    if ($data !== null) {
        $logMsg .= ": " . json_encode($data);
    }
    error_log($logMsg);
}

logWebhook("Webhook received", [
    'method' => $_SERVER['REQUEST_METHOD'],
    'content_type' => $_SERVER['CONTENT_TYPE'] ?? 'not set',
    'query' => $_GET,
]);

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    logWebhook("Invalid request method: " . $_SERVER['REQUEST_METHOD']);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// Get the raw POST data
$rawInput = file_get_contents('php://input');
logWebhook("Raw webhook payload", $rawInput);

// Parse the payload - handle potential JSON parsing errors
$input = null;
if (!empty($rawInput)) {
    // Try to parse as JSON first
    $input = json_decode($rawInput, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        logWebhook("JSON parsing failed, trying URL-encoded format");
        
        // Try to parse as URL-encoded data
        parse_str($rawInput, $input);
        
        // If still empty, check if there might be nested JSON
        if (empty($input) && strpos($rawInput, '=') !== false) {
            // This might be a key-value pair with JSON as value
            $parts = explode('=', $rawInput, 2);
            if (count($parts) === 2) {
                $jsonPart = urldecode($parts[1]);
                $parsed = json_decode($jsonPart, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $input = $parsed;
                    logWebhook("Successfully parsed nested JSON from URL-encoded data");
                }
            }
        }
    }
}

// If we still couldn't parse the data
if (empty($input)) {
    // Check if we have any POST data
    if (!empty($_POST)) {
        logWebhook("Using POST data directly");
        $input = $_POST;
    } else {
        http_response_code(400);
        logWebhook("Failed to parse webhook data");
        echo json_encode(['success' => false, 'error' => 'Invalid data format']);
        exit;
    }
}

// Extract payment details from the webhook - be flexible with field names
$merchantTransactionId = $input['merchantTransactionId'] ?? 
                          $input['reference'] ?? 
                          $input['id'] ?? 
                          null;
$paymentStatus = $input['status'] ?? 
                 $input['payment_status'] ?? 
                 $input['paymentStatus'] ?? 
                 null;
$paymentResult = $input['result'] ?? 
                 $input['payment_result'] ?? 
                 $input['paymentResult'] ?? 
                 null;

// Log all data for debugging
logWebhook("Extracted payment details", [
    'merchantTransactionId' => $merchantTransactionId,
    'paymentStatus' => $paymentStatus,
    'paymentResult' => $paymentResult,
    'allData' => $input
]);

if (!$merchantTransactionId) {
    http_response_code(400);
    logWebhook("Missing transaction ID");
    echo json_encode(['success' => false, 'error' => 'Missing transaction ID']);
    exit;
}

logWebhook("Processing webhook for transaction", [
    'merchantTransactionId' => $merchantTransactionId,
    'status' => $paymentStatus,
    'result' => $paymentResult
]);

try {
    // Initialize payment processor
    $paymentProcessor = new PaymentProcessor();
    
    // Check if payment is successful
    if ($paymentStatus === 'COMPLETED' || $paymentStatus === 'SUCCESS' || $paymentResult === 'SUCCESS') {
        logWebhook("Payment successful, verifying in our system");
        
        // Verify the payment in our system
        $result = $paymentProcessor->verifyPayment($merchantTransactionId);
        logWebhook("Verification result", $result);
        
        if ($result['success']) {
            // Payment verification successful
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'Payment successfully processed'
            ]);
        } else {
            // Payment verification failed
            http_response_code(500);
            logWebhook("Payment verification failed", $result);
            echo json_encode([
                'success' => false,
                'error' => 'Payment verification failed'
            ]);
        }
    } else {
        // Payment failed or is in another status
        logWebhook("Payment not successful", [
            'status' => $paymentStatus,
            'result' => $paymentResult
        ]);
        
        // Update our database with failed status
        // This would be implemented in a production system
        
        http_response_code(200); // Still return 200 to acknowledge receipt
        echo json_encode([
            'success' => true,
            'message' => 'Webhook received for failed payment'
        ]);
    }
} catch (Exception $e) {
    // Log and return error
    logWebhook("Exception processing webhook", [
        'message' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Server error: ' . $e->getMessage()
    ]);
} 