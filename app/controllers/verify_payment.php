<?php
require_once __DIR__ . '/../models/PaymentProcessor.php';

header('Content-Type: application/json');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['reference']) || !isset($input['bet_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing required parameters']);
    exit;
}

// Initialize payment processor
$paymentProcessor = new PaymentProcessor();

// Verify the payment
$result = $paymentProcessor->verifyPayment($input['reference']);

if ($result['success']) {
    // Payment verified successfully
    echo json_encode([
        'success' => true,
        'message' => 'Payment verified successfully'
    ]);
} else {
    // Payment verification failed
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $result['error'] ?? 'Payment verification failed'
    ]);
} 