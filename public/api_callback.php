<?php
// Prevent PHP from outputting errors as HTML
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Start session and include required files
session_start();
require_once __DIR__ . '/../app/models/PaymentProcessor.php';

// Always set JSON content type
header('Content-Type: application/json');

// Log request for debugging
error_log("API callback received: " . json_encode($_GET) . " Session: " . json_encode($_SESSION));

try {
    // Get parameters
    $reference = $_GET['reference'] ?? $_GET['merchantTransactionId'] ?? null;
    $betId = $_GET['bet_id'] ?? null;
    $success = isset($_GET['success']) && $_GET['success'] === 'true';
    
    // Use session data if available
    if (!$reference && isset($_SESSION['pending_payment']['reference'])) {
        $reference = $_SESSION['pending_payment']['reference'];
    }
    
    if (!$betId && isset($_SESSION['pending_payment']['bet_id'])) {
        $betId = $_SESSION['pending_payment']['bet_id'];
    }
    
    // Check if we have a reference
    if (!$reference) {
        echo json_encode([
            'success' => false,
            'error' => 'Missing payment reference',
            'redirect_url' => '/my_bets.php?error=missing_reference'
        ]);
        exit;
    }
    
    // Process the payment
    $paymentProcessor = new PaymentProcessor();
    $result = $paymentProcessor->verifyPayment($reference);
    
    // Add redirect URL if not present
    if (!isset($result['redirect_url'])) {
        if ($result['success']) {
            $result['redirect_url'] = '/my_bets.php?payment_success=true&bet_id=' . ($betId ?? '');
        } else {
            $result['redirect_url'] = '/my_bets.php?payment_error=' . urlencode($result['error'] ?? 'Payment verification failed');
        }
    }
    
    // Return result as JSON
    echo json_encode($result);
    
} catch (Exception $e) {
    // Log the error
    error_log("API callback error: " . $e->getMessage());
    
    // Return error as JSON
    echo json_encode([
        'success' => false,
        'error' => 'Server error: ' . $e->getMessage(),
        'redirect_url' => '/my_bets.php?error=server_error'
    ]);
} 