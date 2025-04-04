<?php
session_start();
require_once __DIR__ . '/../models/PaymentProcessor.php';

// Set content type to JSON
header('Content-Type: application/json');

// Allow CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

// Log access
error_log("API Payment callback accessed. Session: " . json_encode($_SESSION) . ", GET: " . json_encode($_GET));

// Check for required parameters
if (!isset($_GET['merchantTransactionId']) && !isset($_GET['id']) && !isset($_GET['reference'])) {
    error_log("Missing required payment reference parameter");
    echo json_encode([
        'success' => false,
        'error' => 'Missing payment reference'
    ]);
    exit;
}

// Get the payment reference from GET parameters - Peach Payments uses various parameters
$payment_reference = $_GET['merchantTransactionId'] ?? $_GET['id'] ?? $_GET['reference'] ?? null;

// If we still don't have a reference, check session
if (!$payment_reference && isset($_SESSION['pending_payment'])) {
    $payment_reference = $_SESSION['pending_payment']['reference'] ?? null;
}

// Check for payment success parameter
$payment_success = isset($_GET['success']) && $_GET['success'] === 'true';

// Get transaction ID from Peach if available
$peach_transaction_id = $_GET['id'] ?? null;

// Extract any custom parameters from the request
$bet_id = $_GET['customParameters[bet_id]'] ?? $_GET['bet_id'] ?? null;
$user_id = $_GET['customParameters[user_id]'] ?? null;

// Peach Payments webhook signature
$peach_signature = $_GET['signature'] ?? null;

// Log the callback parameters
error_log("API Payment callback parameters: " . json_encode([
    'payment_reference' => $payment_reference,
    'payment_success' => $payment_success,
    'peach_transaction_id' => $peach_transaction_id,
    'bet_id' => $bet_id,
    'user_id' => $user_id,
    'signature' => $peach_signature,
    'all_params' => $_GET
]));

// If we have a reference, verify the payment
if ($payment_reference) {
    try {
        $paymentProcessor = new PaymentProcessor();
        
        // Validate webhook signature if present
        if ($peach_signature) {
            if (!$paymentProcessor->validateWebhookSignature($_GET, $peach_signature)) {
                error_log("Invalid webhook signature received");
                echo json_encode([
                    'success' => false,
                    'error' => 'Invalid signature'
                ]);
                exit;
            }
            error_log("Webhook signature validated successfully");
        }
        
        $result = $paymentProcessor->verifyPayment($payment_reference);
        error_log("Payment verification result: " . json_encode($result));
        
        if ($result['success']) {
            // Payment was successful
            echo json_encode([
                'success' => true,
                'message' => 'Payment processed successfully',
                'bet_id' => $result['bet_id'] ?? null,
                'test_mode' => $result['test_mode'] ?? false,
                'redirect_url' => '/app/views/my_bets.php?payment_success=true&bet_id=' . ($result['bet_id'] ?? '')
            ]);
        } else {
            // Payment failed or couldn't be verified
            echo json_encode([
                'success' => false,
                'error' => $result['error'] ?? 'Payment verification failed',
                'redirect_url' => '/app/views/my_bets.php?payment_error=' . urlencode($result['error'] ?? 'Payment verification failed')
            ]);
        }
    } catch (Exception $e) {
        error_log("Error verifying payment: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'error' => 'An error occurred while verifying your payment'
        ]);
    }
} else {
    // No payment reference found
    echo json_encode([
        'success' => false,
        'error' => 'Payment reference not found'
    ]);
} 