<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/peach_payments.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class PaymentProcessor {
    private $entityId;
    private $signature;
    private $apiUrl;
    private $db;
    private $client;

    public function __construct() {
        $this->entityId = $_ENV['PEACH_ENTITY_ID'];
        $this->signature = $_ENV['PEACH_SIGNATURE'];
        $this->apiUrl = $_ENV['PEACH_API_URL'];
        $this->db = getDB();
        $this->client = new Client();
    }

    /**
     * Make API request to Peach Payments
     */
    private function makePeachRequest($data) {
        try {
            // For testing, log the request data
            if ($_ENV['PEACH_ENVIRONMENT'] === 'test') {
                error_log("Making Peach Payments request: " . json_encode($data));
            }
            
            // Set up proper URL validation for Peach Payments
            if (isset($data['shopperResultUrl']) && !preg_match('/^https?:\/\/(www\.)?[a-zA-Z0-9][-a-zA-Z0-9]{0,62}(\.[a-zA-Z0-9][-a-zA-Z0-9]{0,62})+(:\d+)?(\/\w+)*$/', $data['shopperResultUrl'])) {
                error_log("Warning: shopperResultUrl doesn't match Peach Payments URL pattern: " . $data['shopperResultUrl']);
            }
            
            if (isset($data['cancelUrl']) && !preg_match('/^https?:\/\/(www\.)?[a-zA-Z0-9][-a-zA-Z0-9]{0,62}(\.[a-zA-Z0-9][-a-zA-Z0-9]{0,62})+(:\d+)?(\/\w+)*$/', $data['cancelUrl'])) {
                error_log("Warning: cancelUrl doesn't match Peach Payments URL pattern: " . $data['cancelUrl']);
            }
            
            if (isset($data['notificationUrl']) && !preg_match('/^https?:\/\/(www\.)?[a-zA-Z0-9][-a-zA-Z0-9]{0,62}(\.[a-zA-Z0-9][-a-zA-Z0-9]{0,62})+(:\d+)?(\/\w+)*$/', $data['notificationUrl'])) {
                error_log("Warning: notificationUrl doesn't match Peach Payments URL pattern: " . $data['notificationUrl']);
            }
            
            // Use form data instead of JSON as shown in the Peach Payments example
            $response = $this->client->request('POST', $this->apiUrl, [
                'form_params' => $data
            ]);
            
            $responseBody = (string) $response->getBody();
            error_log("Peach Payments response: " . $responseBody);
            
            return [
                'success' => true,
                'data' => json_decode($responseBody, true)
            ];
        } catch (RequestException $e) {
            error_log("Peach Payments API Error: " . $e->getMessage());
            
            // Log more detailed information for debugging
            if ($e->hasResponse()) {
                $errorBody = (string) $e->getResponse()->getBody();
                error_log("Peach Payments Error Response: " . $errorBody);
                
                $errorData = json_decode($errorBody, true);
                
                // Handle validation errors specifically
                if (isset($errorData['validation_errors'])) {
                    error_log("Validation errors: " . json_encode($errorData['validation_errors']));
                    
                    // Create a more user-friendly error message
                    $errorMessages = [];
                    foreach ($errorData['validation_errors'] as $field => $errors) {
                        $errorMessages[] = $field . ': ' . implode(', ', $errors);
                    }
                    
                    return [
                        'success' => false,
                        'error' => 'Validation failed: ' . implode('; ', $errorMessages),
                        'details' => $errorData
                    ];
                }
                
                return [
                    'success' => false,
                    'error' => $errorData['message'] ?? $e->getMessage(),
                    'details' => $errorData
                ];
            }
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Initialize a payment transaction for a bet
     */
    public function createPaymentIntent($amount, $betId = null, $userId = null) {
        try {
            error_log("Creating payment intent: amount=$amount, betId=$betId, userId=$userId");
            
            // Generate merchant transaction ID
            $merchantTransactionId = $this->generateTransactionReference();
            
            // Prepare API request data
            $data = [
                'amount' => $amount,
                'currency' => 'USD', // Change as needed
                'merchantTransactionId' => $merchantTransactionId,
                'purpose' => 'Bet payment',
                'customer' => [
                    'id' => $userId ?? 'guest',
                    'email' => $this->getUserEmail($userId) ?? 'guest@example.com'
                ],
                'redirectUrls' => [
                    'success' => $_ENV['APP_URL'] . '/app/views/payment_callback.php?success=true&reference=' . $merchantTransactionId,
                    'failure' => $_ENV['APP_URL'] . '/app/views/payment_callback.php?success=false&reference=' . $merchantTransactionId,
                    'cancel' => $_ENV['APP_URL'] . '/app/views/payment_callback.php?status=cancelled&reference=' . $merchantTransactionId
                ]
            ];

            // In test mode, simulate a successful Peach Payments response
            if ($_ENV['PEACH_ENVIRONMENT'] === 'test') {
                error_log("TEST MODE: Simulating successful Peach Payments response");
                
                // Store payment intent in database if bet exists and is numeric
                if ($betId && is_numeric($betId)) {
                    try {
                        $sql = "INSERT INTO PaymentIntents (bet_id, payment_reference, amount, status) 
                                VALUES (?, ?, ?, ?)";
                        $stmt = $this->db->prepare($sql);
                        $stmt->execute([
                            $betId,
                            $merchantTransactionId,
                            $amount,
                            'pending'
                        ]);
                    } catch (Exception $dbError) {
                        // Log but don't fail - this is just for testing
                        error_log("Test mode DB error: " . $dbError->getMessage());
                    }
                }
                
                // Simulate the return URL for payment callback in test mode - use the proper path
                $testCheckoutUrl = '/app/views/payment_callback.php?success=true&reference=' . 
                    urlencode($merchantTransactionId) . 
                    '&bet_id=' . urlencode($betId ?? 'new');
                
                return [
                    'success' => true,
                    'checkout_url' => $testCheckoutUrl,
                    'authorization_url' => $testCheckoutUrl, // Include both for consistency
                    'reference' => $merchantTransactionId,
                    'test_mode' => true
                ];
            }
            
            // For production, make the actual API request
            $response = $this->makePeachRequest($data);
                
            if (!$response['success']) {
                throw new Exception($response['message'] ?? 'Payment API request failed');
            }
            
            // Store payment intent in database if bet exists
            if ($betId && is_numeric($betId)) {
                try {
                    $sql = "INSERT INTO PaymentIntents (bet_id, payment_reference, amount, status) 
                            VALUES (?, ?, ?, ?)";
                    $stmt = $this->db->prepare($sql);
                    $stmt->execute([
                        $betId,
                        $merchantTransactionId,
                        $amount,
                        'pending'
                    ]);
                } catch (Exception $dbError) {
                    // Log but don't fail - this is just for testing
                    error_log("Test mode DB error: " . $dbError->getMessage());
                }
            }

            return [
                'success' => true,
                'checkout_url' => $response['data']['checkoutUrl'] ?? $response['data']['redirect']['url'] ?? null,
                'authorization_url' => $response['data']['checkoutUrl'] ?? $response['data']['redirect']['url'] ?? null,
                'reference' => $merchantTransactionId
            ];
        } catch (Exception $e) {
            error_log("Payment initialization error: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Process a payout for a bet winner
     */
    public function processPayout($betId, $winnerId, $amount) {
        try {
            // Get winner's details
            $sql = "SELECT email, username, user_id FROM Users WHERE user_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$winnerId]);
            $winner = $stmt->fetch();

            if (!$winner) {
                throw new Exception('Winner not found');
            }

            // For Peach Payments, we would typically use their payout API
            // Since this is a test implementation, we'll simulate the payout process
            
            // Record the payout in our database
            $reference = 'PAYOUT_' . $betId . '_' . time();
            
            $sql = "INSERT INTO Payouts (bet_id, user_id, amount, reference, status) 
                    VALUES (?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $betId,
                $winnerId,
                $amount,
                $reference,
                'completed'  // In production, this would start as 'pending'
            ]);
            
            // Update payment status
            $sql = "UPDATE PaymentIntents SET status = 'paid_out', 
                    payout_reference = ? WHERE bet_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$reference, $betId]);

            // In a real implementation, we would make an API call to Peach Payments
            // to process the actual bank transfer
            
            // Simulate a successful response
            return [
                'success' => true,
                'payout_reference' => $reference
            ];
        } catch (Exception $e) {
            error_log("Payout error: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Verify payment and update bet status if successful
     */
    public function verifyPayment($reference) {
        try {
            error_log("Starting payment verification for reference: " . $reference);
            
            // Check if we're in test mode
            if ($_ENV['PEACH_ENVIRONMENT'] === 'test') {
                error_log("NOTICE: Using test mode for payment verification");
                
                // For testing, simulate a successful payment verification
                if (isset($_SESSION['pending_payment']) && $_SESSION['pending_payment']['reference'] === $reference) {
                    $pendingPayment = $_SESSION['pending_payment'];
                    $betId = $pendingPayment['bet_id'] ?? null;
                    
                    error_log("TEST MODE: Simulating successful payment for bet_id: " . $betId);
                    
                    // Update status based on action type
                    if ($pendingPayment['action'] === 'accept_bet') {
                        $this->updateBetStatusAfterPayment($betId);
                    } else if ($pendingPayment['action'] === 'create_bet') {
                        // For newly created bets, mark payment as successful
                        error_log("TEST MODE: Updating payment status for newly created bet: " . $betId);
                        
                        // Update payment status in database
                        $this->updatePaymentStatus($reference, 'completed', $betId);
                    }
                    
                    // Clear the pending payment from session
                    unset($_SESSION['pending_payment']);
                    
                    return [
                        'success' => true,
                        'status' => 'completed',
                        'bet_id' => $betId,
                        'test_mode' => true
                    ];
                }
                
                // If we don't have session data, still return success for testing
                return [
                    'success' => true,
                    'status' => 'completed',
                    'test_mode' => true,
                    'note' => 'This is a simulated success response for testing'
                ];
            }
            
            // Make the API request to verify payment
            error_log("Making API request to verify payment reference: " . $reference);
            
            // For Peach Payments, we would typically verify by checking the payment status
            // Since Peach uses webhooks/notifications, we'll check our database first
            $sql = "SELECT * FROM PaymentIntents WHERE payment_reference = ? LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$reference]);
            $paymentIntent = $stmt->fetch();
            
            if ($paymentIntent && $paymentIntent['status'] === 'completed') {
                // Payment already verified and completed
                error_log("Payment already verified and completed in database");
                return [
                    'success' => true,
                    'status' => 'completed',
                    'bet_id' => $paymentIntent['bet_id']
                ];
            }
            
            // If the payment is not marked as completed in our database,
            // we assume it's a callback notification or a new verification request
            
            // In production, we would make an API request to check status
            $betId = null;
            $userId = null;
            
            // Check if we have session data
            if (isset($_SESSION['pending_payment']) && $_SESSION['pending_payment']['reference'] === $reference) {
                $pendingPayment = $_SESSION['pending_payment'];
                $betId = $pendingPayment['bet_id'];
                $userId = $pendingPayment['user_id'];
                
                error_log("Found pending payment in session: " . json_encode($pendingPayment));
                
                // Handle different payment actions
                if ($pendingPayment['action'] === 'accept_bet') {
                    // Update bet status for accepting an existing bet
                    $this->updateBetStatusAfterPayment($betId);
                } else if ($pendingPayment['action'] === 'create_bet') {
                    // For newly created bets, just update payment status
                    error_log("Updating payment status for newly created bet: " . $betId);
                }
                
                // Update payment status in database
                $this->updatePaymentStatus($reference, 'completed', $betId);
                
                // Clear the pending payment from session
                unset($_SESSION['pending_payment']);
                
                return [
                    'success' => true,
                    'status' => 'completed',
                    'bet_id' => $betId
                ];
            }
            
            // If we don't have session data, try to look up the payment by reference
            $sql = "SELECT * FROM PaymentIntents WHERE payment_reference = ? LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$reference]);
            $paymentIntent = $stmt->fetch();
            
            if ($paymentIntent) {
                // Update status to completed
                $this->updatePaymentStatus($reference, 'completed');
                
                return [
                    'success' => true,
                    'status' => 'completed',
                    'bet_id' => $paymentIntent['bet_id']
                ];
            }
            
            // If we couldn't find any payment with this reference
            throw new Exception('Payment reference not found');
            
        } catch (Exception $e) {
            error_log("Payment verification error: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Update payment status in database
     */
    private function updatePaymentStatus($reference, $status, $betId = null) {
        try {
            // First try to update existing record
            $sql = "UPDATE PaymentIntents SET status = ? WHERE payment_reference = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$status, $reference]);
            
            // If no rows were updated and we have a bet ID, insert a new record
            if ($stmt->rowCount() === 0 && $betId) {
                $sql = "INSERT INTO PaymentIntents (bet_id, payment_reference, status) 
                        VALUES (?, ?, ?)";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$betId, $reference, $status]);
            }
            
            return true;
        } catch (Exception $e) {
            error_log("Error updating payment status: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check and update bet status after payment
     */
    private function updateBetStatusAfterPayment($betId) {
        try {
            // Get current bet details
            $sql = "SELECT b.*, 
                   COUNT(pi.payment_intent_id) as payment_count,
                   SUM(CASE WHEN pi.status = 'completed' THEN 1 ELSE 0 END) as completed_payments
                   FROM Bets b
                   LEFT JOIN PaymentIntents pi ON b.bet_id = pi.bet_id
                   WHERE b.bet_id = ?
                   GROUP BY b.bet_id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$betId]);
            $bet = $stmt->fetch();
            
            if (!$bet) {
                throw new Exception("Bet not found: " . $betId);
            }
            
            // If this is a money bet and all payments are completed, activate the bet
            if ($bet['stake_type'] === 'money' && $bet['completed_payments'] >= 2) {
                // Update bet status to active
                $updateSql = "UPDATE Bets SET status = 'active' WHERE bet_id = ?";
                $updateStmt = $this->db->prepare($updateSql);
                $updateStmt->execute([$betId]);
                
                // Log the update
                error_log("Activated bet {$betId} after payment verification - both participants have paid");
            }
            
            return true;
        } catch (Exception $e) {
            error_log("Error updating bet status after payment: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Setup recipient bank account for payouts
     */
    public function setupBankAccount($userId, $bankDetails) {
        try {
            // Validate bank account
            $response = $this->makePeachRequest([
                'account_number' => $bankDetails['account_number'],
                'bank_code' => $bankDetails['bank_code']
            ]);

            if (!$response['success']) {
                throw new Exception($response['error']);
            }

            // Create transfer recipient
            $recipientData = [
                'type' => 'nuban',
                'name' => $response['data']['account_name'],
                'account_number' => $bankDetails['account_number'],
                'bank_code' => $bankDetails['bank_code'],
                'currency' => $_ENV['PEACH_CURRENCY']
            ];

            $recipient = $this->makePeachRequest($recipientData);

            if (!$recipient['success']) {
                throw new Exception($recipient['error']);
            }

            // Store recipient code
            $sql = "UPDATE Users SET 
                    paystack_recipient_code = ?,
                    bank_account_name = ?,
                    bank_account_number = ?,
                    bank_code = ?
                    WHERE user_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $recipient['data']['recipient_code'],
                $response['data']['account_name'],
                $bankDetails['account_number'],
                $bankDetails['bank_code'],
                $userId
            ]);

            return [
                'success' => true,
                'account_name' => $response['data']['account_name']
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Generate a unique transaction reference
     */
    private function generateTransactionReference() {
        return 'BET_' . time() . '_' . substr(md5(uniqid(mt_rand(), true)), 0, 8);
    }
    
    /**
     * Get user's email by user ID
     */
    private function getUserEmail($userId) {
        if (!$userId) {
            return null;
        }
        
        try {
            $sql = "SELECT email FROM Users WHERE user_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$userId]);
            $user = $stmt->fetch();
            
            return $user ? $user['email'] : null;
        } catch (Exception $e) {
            error_log("Error fetching user email: " . $e->getMessage());
            return null;
        }
    }
} 