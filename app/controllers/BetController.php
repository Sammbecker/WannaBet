<?php
require_once __DIR__ . '/../models/Bet.php';
require_once __DIR__ . '/../models/Notification.php';
require_once __DIR__ . '/../models/Friendship.php';
require_once __DIR__ . '/../models/PaymentProcessor.php';
require_once __DIR__ . '/../models/TrustScore.php';

class BetController {
    private $betModel;
    private $notificationModel;
    private $friendshipModel;
    private $paymentProcessor;
    private $trustScoreModel;
    
    public function __construct() {
        $this->betModel = new Bet();
        $this->notificationModel = new Notification();
        $this->friendshipModel = new Friendship();
        $this->paymentProcessor = new PaymentProcessor();
        $this->trustScoreModel = new TrustScore();
    }
    
    /**
     * Create a new bet
     */
    public function createBet() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return ['success' => false, 'errors' => ['Invalid request method']];
        }

        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            return ['success' => false, 'errors' => ['User not authenticated']];
        }

        // Validate input
        $errors = [];
        $requiredFields = ['opponent_id', 'description', 'stake_type', 'deadline'];
        foreach ($requiredFields as $field) {
            if (empty($_POST[$field])) {
                $errors[] = ucfirst($field) . ' is required';
            }
        }

        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        $stakeType = $_POST['stake_type'];
        $stakeAmount = null;
        $stakeDescription = null;
        $paymentPreference = $_POST['payment_preference'] ?? 'pay_now'; // Default to pay_now

        if ($stakeType === 'money') {
            if (empty($_POST['stake_amount']) || !is_numeric($_POST['stake_amount'])) {
                return ['success' => false, 'errors' => ['Valid stake amount is required for monetary bets']];
            }
            $stakeAmount = floatval($_POST['stake_amount']);
        } else {
            if (empty($_POST['stake_description'])) {
                return ['success' => false, 'errors' => ['Stake description is required for non-monetary bets']];
            }
            $stakeDescription = $_POST['stake_description'];
        }

        // For money bets, handle payment first
        if ($stakeType === 'money') {
            // Create payment intent first
            $paymentResult = $this->paymentProcessor->createPaymentIntent(null, $stakeAmount, $userId);
            if (!$paymentResult['success']) {
                return ['success' => false, 'errors' => ['Payment setup failed: ' . $paymentResult['error']]];
            }

            // Store payment reference for later
            $paymentReference = $paymentResult['reference'];
            $paymentUrl = $paymentResult['authorization_url'];

            // For pay_later option, proceed with bet creation
            if ($paymentPreference === 'pay_later') {
                $paymentUrl = null;
            }
        }

        // Create bet
        $betData = [
            'user_id' => $userId,
            'opponent_id' => $_POST['opponent_id'],
            'description' => $_POST['description'],
            'stake_type' => $stakeType,
            'stake_amount' => $stakeAmount,
            'stake_description' => $stakeDescription,
            'deadline' => $_POST['deadline'],
            'payment_required' => ($stakeType === 'money'),
            'payment_status' => ($stakeType === 'money' ? 'pending' : null)
        ];

        // Only create bet if it's not a money bet or if it's pay_later
        if ($stakeType !== 'money' || $paymentPreference === 'pay_later') {
            $betId = $this->betModel->createBet($betData);
            if (!$betId) {
                return ['success' => false, 'errors' => ['Failed to create bet']];
            }

            // Create notification
            $notificationData = [
                'bet_id' => $betId,
                'user_id' => $_POST['opponent_id'],
                'type' => 'bet_invitation',
                'message' => 'You have been invited to a new bet!'
            ];

            $notificationId = $this->notificationModel->createNotification($notificationData);
            if (!$notificationId) {
                // Rollback bet creation
                $this->betModel->deleteBet($betId);
                return ['success' => false, 'errors' => ['Failed to create notification']];
            }
        }

        $response = [
            'success' => true,
            'message' => 'Bet created successfully'
        ];

        if ($paymentUrl) {
            $response['payment_url'] = $paymentUrl;
            $response['redirect'] = true;
            $response['payment_reference'] = $paymentReference;
        }

        return $response;
    }
    
    /**
     * Get a bet by ID
     */
    public function getBet($betId) {
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            return ['success' => false, 'errors' => ['You must be logged in to view bets']];
        }
        
        $bet = $this->betModel->getBetById($betId);
        
        if ($bet) {
            return ['success' => true, 'bet' => $bet];
        } else {
            return ['success' => false, 'errors' => ['Bet not found']];
        }
    }
    
    /**
     * Get all bets for the current user
     */
    public function getUserBets() {
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            return ['success' => false, 'errors' => ['You must be logged in to view bets']];
        }
        
        $userId = $_SESSION['user_id'];
        
        // Get bets created by the user
        $createdBets = $this->betModel->getBetsByUser($userId);
        
        // Get bets where the user is participating
        $participatingBets = $this->betModel->getBetsForUser($userId);
        
        return [
            'success' => true,
            'created_bets' => $createdBets,
            'participating_bets' => $participatingBets
        ];
    }
    
    /**
     * Get friends for creating a bet
     */
    public function getFriendsForBet() {
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            return ['success' => false, 'errors' => ['You must be logged in to view friends']];
        }
        
        $userId = $_SESSION['user_id'];
        
        // Get friends
        $friends = $this->friendshipModel->getFriends($userId);
        
        return ['success' => true, 'friends' => $friends];
    }
    
    /**
     * Handle bet response (accept/reject)
     */
    public function respondToBet() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return ['success' => false, 'errors' => ['Invalid request method']];
        }
        
        $notificationId = $_POST['notification_id'] ?? null;
        $response = $_POST['response'] ?? null;
        $userId = $_SESSION['user_id'] ?? null;
        
        if (!$notificationId || !$response || !in_array($response, ['accepted', 'rejected'])) {
            return ['success' => false, 'errors' => ['Invalid parameters']];
        }
        
        // Get notification details
        $notification = $this->notificationModel->getNotificationById($notificationId);
        if (!$notification) {
            return ['success' => false, 'errors' => ['Notification not found']];
        }
        
        // Get bet details
        $bet = $this->betModel->getBetById($notification['bet_id']);
        if (!$bet) {
            return ['success' => false, 'errors' => ['Bet not found']];
        }

        // For money bets, check if payment is required
        if ($bet['stake_type'] === 'money' && $response === 'accepted') {
            // Initialize payment with current user's ID
            $paymentResult = $this->paymentProcessor->createPaymentIntent($bet['bet_id'], $bet['stake_amount'], $userId);
            if (!$paymentResult['success']) {
                return ['success' => false, 'errors' => ['Payment setup failed: ' . $paymentResult['error']]];
            }

            // Store the payment reference in session for verification after redirect
            $_SESSION['pending_payment'] = [
                'reference' => $paymentResult['reference'],
                'notification_id' => $notificationId,
                'bet_id' => $bet['bet_id']
            ];

            // Return payment URL and redirect flag
            return [
                'success' => true,
                'message' => 'Please complete the payment to accept the bet',
                'payment_url' => $paymentResult['authorization_url'],
                'requires_payment' => true,
                'redirect' => true
            ];
        }

        // Update notification status
        $result = $this->notificationModel->updateStatus($notificationId, $response);
        
        if ($result) {
            // Update the bet status if accepted
            if ($response === 'accepted') {
                $this->betModel->updateBetStatus($notification['bet_id'], 'active');
            }
            
            return [
                'success' => true, 
                'message' => 'Response submitted successfully',
                'redirect' => true,
                'redirect_url' => 'my_bets.php'
            ];
        } else {
            return ['success' => false, 'errors' => ['Failed to submit response']];
        }
    }
    
    /**
     * Process bet completion and handle payouts
     */
    public function completeBet() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return ['success' => false, 'errors' => ['Invalid request method']];
        }

        $betId = $_POST['bet_id'] ?? null;
        $winnerId = $_POST['winner_id'] ?? null;

        if (!$betId || !$winnerId) {
            return ['success' => false, 'errors' => ['Missing required parameters']];
        }

        // Get bet details
        $bet = $this->betModel->getBetById($betId);
        if (!$bet) {
            return ['success' => false, 'errors' => ['Bet not found']];
        }

        // Process payout for monetary bets
        if ($bet['stake_type'] === 'money' && $bet['payment_required']) {
            $payoutResult = $this->paymentProcessor->processPayout($betId, $winnerId, $bet['stake_amount']);
            if (!$payoutResult['success']) {
                return ['success' => false, 'errors' => ['Payout failed: ' . $payoutResult['error']]];
            }
        }

        // Update bet status
        $updateResult = $this->betModel->updateBet($betId, [
            'status' => 'completed',
            'winner_id' => $winnerId,
            'payment_status' => ($bet['stake_type'] === 'money') ? 'completed' : null
        ]);

        if (!$updateResult) {
            return ['success' => false, 'errors' => ['Failed to update bet status']];
        }

        // Update trust scores for both users
        $this->trustScoreModel->updateScoreAfterBet($betId);

        return [
            'success' => true,
            'message' => 'Bet completed successfully'
        ];
    }
    
    /**
     * Get all notifications for the current user
     */
    public function getUserNotifications() {
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            return ['success' => false, 'errors' => ['You must be logged in to view notifications']];
        }
        
        $userId = $_SESSION['user_id'];
        
        // Get notifications for the user
        $notifications = $this->notificationModel->getNotificationsForUser($userId);
        
        return ['success' => true, 'notifications' => $notifications];
    }
    
    /**
     * Get pending notifications for the current user
     */
    public function getPendingNotifications() {
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            return ['success' => false, 'errors' => ['You must be logged in to view notifications']];
        }
        
        $userId = $_SESSION['user_id'];
        
        // Get pending notifications for the user
        $notifications = $this->notificationModel->getPendingNotificationsForUser($userId);
        
        return ['success' => true, 'notifications' => $notifications];
    }

    /**
     * Get user's trust score and stats
     */
    public function getUserTrustScore($userId = null) {
        if (!$userId) {
            $userId = $_SESSION['user_id'] ?? null;
        }

        if (!$userId) {
            return ['success' => false, 'errors' => ['User not authenticated']];
        }

        $trustScore = $this->trustScoreModel->getTrustScore($userId);

        return [
            'success' => true,
            'trust_score' => $trustScore
        ];
    }
}
?> 