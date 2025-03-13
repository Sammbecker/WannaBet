<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/paystack.php';

class PaymentProcessor {
    private $secretKey;
    private $db;

    public function __construct() {
        $this->secretKey = $_ENV['PAYSTACK_SECRET_KEY'];
        $this->db = getDB();
    }

    /**
     * Make API request to Paystack
     */
    private function makePaystackRequest($endpoint, $method = 'POST', $data = null) {
        $url = "https://api.paystack.co/" . $endpoint;
        
        $headers = [
            "Authorization: Bearer " . $this->secretKey,
            "Cache-Control: no-cache",
            "Content-Type: application/json"
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        if ($method === 'POST' && $data) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new Exception("cURL Error: " . $error);
        }

        return json_decode($response, true);
    }

    /**
     * Initialize a payment transaction for a bet
     */
    public function createPaymentIntent($betId = null, $amount, $userId = null) {
        try {
            // Get user's email - either the bet participant or creator
            $sql = "SELECT u.email, u.username 
                    FROM Users u 
                    WHERE u.user_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$userId ?? $_SESSION['user_id']]);
            $user = $stmt->fetch();

            if (!$user) {
                throw new Exception('User not found');
            }

            if (!$user['email']) {
                throw new Exception('User email not found');
            }

            // Initialize transaction
            $data = [
                'amount' => $amount * 100, // Convert to kobo/cents
                'email' => $user['email'],
                'currency' => $_ENV['PAYSTACK_CURRENCY'],
                'reference' => 'bet_' . ($betId ?? 'new') . '_' . time(),
                'metadata' => [
                    'bet_id' => $betId,
                    'type' => 'bet_payment',
                    'user_id' => $userId ?? $_SESSION['user_id']
                ]
            ];

            $response = $this->makePaystackRequest('transaction/initialize', 'POST', $data);

            if (!$response['status']) {
                throw new Exception($response['message']);
            }

            // Store payment intent in database only if bet exists
            if ($betId) {
                $sql = "INSERT INTO PaymentIntents (bet_id, stripe_payment_intent_id, amount, status) 
                        VALUES (?, ?, ?, ?)";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([
                    $betId,
                    $response['data']['reference'],
                    $amount,
                    'pending'
                ]);
            }

            return [
                'success' => true,
                'authorization_url' => $response['data']['authorization_url'],
                'access_code' => $response['data']['access_code'],
                'reference' => $response['data']['reference']
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
            $sql = "SELECT email, paystack_recipient_code FROM Users WHERE user_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$winnerId]);
            $winner = $stmt->fetch();

            if (!$winner['paystack_recipient_code']) {
                throw new Exception('User has not set up their bank account for payouts');
            }

            // Initiate transfer
            $data = [
                'source' => 'balance',
                'amount' => $amount * 100,
                'recipient' => $winner['paystack_recipient_code'],
                'reason' => 'Bet Winnings - Bet #' . $betId,
                'reference' => 'win_' . $betId . '_' . time()
            ];

            $response = $this->makePaystackRequest('transfer', 'POST', $data);

            if (!$response['status']) {
                throw new Exception($response['message']);
            }

            // Update payment status
            $sql = "UPDATE PaymentIntents SET status = 'completed', 
                    transfer_id = ? WHERE bet_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$response['data']['transfer_code'], $betId]);

            return [
                'success' => true,
                'transfer_code' => $response['data']['transfer_code']
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Verify payment and create bet if successful
     */
    public function verifyPayment($reference) {
        try {
            $response = $this->makePaystackRequest('transaction/verify/' . $reference, 'GET');

            if (!$response['status']) {
                throw new Exception($response['message']);
            }

            if ($response['data']['status'] === 'success') {
                // Get metadata from payment
                $metadata = $response['data']['metadata'];
                $betId = $metadata['bet_id'] ?? null;
                $userId = $metadata['user_id'] ?? null;

                // If this is a new bet (no bet_id), create it now
                if ($betId === 'new' && $userId) {
                    // Create the bet using stored session data
                    $betController = new BetController();
                    $betResult = $betController->createBet();
                    
                    if (!$betResult['success']) {
                        throw new Exception('Failed to create bet after payment');
                    }
                    
                    $betId = $betResult['bet_id'];
                }

                // Update payment status
                $sql = "UPDATE PaymentIntents SET status = 'completed' 
                        WHERE stripe_payment_intent_id = ?";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$reference]);

                return [
                    'success' => true,
                    'status' => 'completed',
                    'bet_id' => $betId
                ];
            }

            return [
                'success' => false,
                'status' => $response['data']['status']
            ];
        } catch (Exception $e) {
            error_log("Payment verification error: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Setup recipient bank account for payouts
     */
    public function setupBankAccount($userId, $bankDetails) {
        try {
            // Validate bank account
            $response = $this->makePaystackRequest('bank/resolve', 'POST', [
                'account_number' => $bankDetails['account_number'],
                'bank_code' => $bankDetails['bank_code']
            ]);

            if (!$response['status']) {
                throw new Exception($response['message']);
            }

            // Create transfer recipient
            $recipientData = [
                'type' => 'nuban',
                'name' => $response['data']['account_name'],
                'account_number' => $bankDetails['account_number'],
                'bank_code' => $bankDetails['bank_code'],
                'currency' => $_ENV['PAYSTACK_CURRENCY']
            ];

            $recipient = $this->makePaystackRequest('transferrecipient', 'POST', $recipientData);

            if (!$recipient['status']) {
                throw new Exception($recipient['message']);
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
} 